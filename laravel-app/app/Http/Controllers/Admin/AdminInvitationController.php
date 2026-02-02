<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminInvitation;
use App\Models\User;
use App\Http\Requests\InviteAdminRequest;
use App\Notifications\AdminInvitationNotification;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminInvitationController extends Controller
{
    use LogsActivity;

    /**
     * Store a newly created invitation.
     * 
     * Rate limited to 5 requests per minute per user/IP to prevent abuse.
     * This protects against invitation spam and potential security threats.
     */
    public function store(InviteAdminRequest $request): RedirectResponse
    {
        $this->authorize('invite', User::class);

        $validated = $request->validated();

        // #region agent log
        $logPath = 'c:\Users\User\Desktop\CB015938_Yelani Samarathunga\.cursor\debug.log';
        $mailConfig = config('mail.mailers.smtp');
        $rawUsername = env('MAIL_USERNAME');
        $rawPassword = env('MAIL_PASSWORD');
        $passwordFromConfig = $mailConfig['password'] ?? null;
        $logData = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'A',
            'location' => 'AdminInvitationController.php:32',
            'message' => 'Mail config before transaction',
            'data' => [
                'host' => $mailConfig['host'] ?? null,
                'port' => $mailConfig['port'] ?? null,
                'username_from_config' => $mailConfig['username'] ?? null,
                'username_from_env' => $rawUsername,
                'username_length' => strlen($mailConfig['username'] ?? ''),
                'password_length' => strlen($passwordFromConfig ?? ''),
                'password_has_spaces' => str_contains($passwordFromConfig ?? '', ' '),
                'password_starts_with_space' => !empty($passwordFromConfig) && $passwordFromConfig[0] === ' ',
                'password_ends_with_space' => !empty($passwordFromConfig) && substr($passwordFromConfig, -1) === ' ',
                'password_has_newlines' => str_contains($passwordFromConfig ?? '', "\n"),
                'password_first_2_chars' => !empty($passwordFromConfig) ? substr($passwordFromConfig, 0, 2) : null,
                'password_last_2_chars' => !empty($passwordFromConfig) ? substr($passwordFromConfig, -2) : null,
                'password_matches_env' => $passwordFromConfig === $rawPassword,
                'encryption' => $mailConfig['encryption'] ?? null,
                'mailer' => config('mail.default'),
                'raw_password_length' => strlen($rawPassword ?? ''),
                'raw_password_first_2' => !empty($rawPassword) ? substr($rawPassword, 0, 2) : null,
                'raw_password_last_2' => !empty($rawPassword) ? substr($rawPassword, -2) : null,
                'config_cached' => app()->configurationIsCached(),
            ],
            'timestamp' => time() * 1000,
        ];
        file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
        // #endregion

        // Use transaction: if email fails, rollback invitation creation
        DB::beginTransaction();
        try {
            // Create invitation
            $invitation = AdminInvitation::createInvitation([
                'email' => $validated['email'],
                'role' => $validated['role'],
                'invited_by' => Auth::id(),
            ]);

            // #region agent log
            $logData = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'D',
                'location' => 'AdminInvitationController.php:70',
                'message' => 'Invitation created in transaction',
                'data' => ['invitation_id' => $invitation->id, 'email' => $validated['email']],
                'timestamp' => time() * 1000,
            ];
            file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
            // #endregion

            // Send notification
            \Illuminate\Support\Facades\Notification::route('mail', $validated['email'])
                ->notify(new AdminInvitationNotification($invitation));
            
            // #region agent log
            $logData = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'B',
                'location' => 'AdminInvitationController.php:80',
                'message' => 'Email sent successfully',
                'data' => ['email' => $validated['email']],
                'timestamp' => time() * 1000,
            ];
            file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
            // #endregion

            // Commit transaction only if email sent successfully
            DB::commit();
        } catch (\Exception $e) {
            // #region agent log
            $logData = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'C',
                'location' => 'AdminInvitationController.php:95',
                'message' => 'Email send failed, rolling back',
                'data' => [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_class' => get_class($e),
                ],
                'timestamp' => time() * 1000,
            ];
            file_put_contents($logPath, json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
            
            // Rollback transaction - invitation will not be saved
            DB::rollBack();
            
            // Return error instead of throwing (better UX)
            return redirect()->route('admin.admins.invite')
                ->withInput()
                ->with('error', 'Failed to send invitation email: ' . $e->getMessage());
        }

        // Log invitation
        $this->logActivity(
            'admin.invitation.sent',
            null,
            'Admin Management',
            'info',
            "Admin invitation sent to {$validated['email']} with role {$validated['role']}",
            [
                'invitation_id' => $invitation->id,
                'email' => $validated['email'],
                'role' => $validated['role'],
                'invited_by' => Auth::id(),
            ]
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Invitation sent successfully.');
    }

    /**
     * Show the invitation acceptance form.
     */
    public function accept(string $token): View|RedirectResponse
    {
        $invitation = AdminInvitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Invalid invitation link.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has already been used.');
        }

        return view('admin.invitations.accept', compact('invitation'));
    }

    /**
     * Process the invitation acceptance.
     */
    public function processAcceptance(Request $request, string $token): RedirectResponse
    {
        $invitation = AdminInvitation::where('token', $token)->first();

        if (!$invitation || $invitation->isExpired() || $invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired invitation.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $invitation->email,
            'password' => Hash::make($validated['password']),
            'role' => $invitation->role,
            'is_active' => true,
            'invited_by' => $invitation->invited_by,
            'invited_at' => $invitation->created_at,
            'email_verified_at' => now(), // Auto-verify since they came from invitation
        ]);

        // Mark invitation as accepted
        $invitation->markAsAccepted();

        // Log acceptance
        $this->logActivity(
            'admin.invitation.accepted',
            $user,
            'Admin Management',
            'info',
            "Admin invitation accepted by {$user->email}",
            [
                'invitation_id' => $invitation->id,
                'user_id' => $user->id,
                'role' => $user->role,
            ]
        );

        // Auto-login the user
        Auth::login($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome! Your admin account has been created successfully.');
    }

    /**
     * Resend an invitation.
     */
    public function resend(AdminInvitation $invitation): RedirectResponse
    {
        $this->authorize('invite', User::class);

        if ($invitation->isAccepted()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'This invitation has already been accepted.');
        }

        // Update expiration
        $invitation->update([
            'expires_at' => now()->addHours(24),
        ]);

        // Resend notification
        \Illuminate\Support\Facades\Notification::route('mail', $invitation->email)
            ->notify(new AdminInvitationNotification($invitation));

        // Log resend
        $this->logActivity(
            'admin.invitation.resent',
            null,
            'Admin Management',
            'info',
            "Admin invitation resent to {$invitation->email}",
            [
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
            ]
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Invitation resent successfully.');
    }

    /**
     * Cancel an invitation.
     */
    public function destroy(AdminInvitation $invitation): RedirectResponse
    {
        $this->authorize('invite', User::class);

        if ($invitation->isAccepted()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Cannot cancel an accepted invitation.');
        }

        $email = $invitation->email;

        // Log cancellation
        $this->logActivity(
            'admin.invitation.cancelled',
            null,
            'Admin Management',
            'warning',
            "Admin invitation cancelled for {$email}",
            [
                'invitation_id' => $invitation->id,
                'email' => $email,
            ]
        );

        $invitation->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Invitation cancelled successfully.');
    }
}
