<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['sometimes', 'string', 'max:255'],
            'app_url' => ['sometimes', 'url', 'max:255'],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'language' => ['sometimes', 'string', 'max:10'],
            
            // Security settings
            'require_2fa' => ['sometimes', 'boolean'],
            'password_requirements' => ['sometimes', 'boolean'],
            'session_timeout' => ['sometimes', 'integer', 'min:5', 'max:480'],
            'max_login_attempts' => ['sometimes', 'integer', 'min:3', 'max:10'],
            
            // Notification settings
            'email_notifications' => ['sometimes', 'boolean'],
            'notification_email' => ['sometimes', 'email', 'max:255'],
            'notification_types' => ['sometimes', 'array'],
            'notification_types.*' => ['string'],
        ];
    }
}
