<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $order = $this->route('order');
        $currentStatus = $order->status ?? null;

        return [
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']),
                function ($attribute, $value, $fail) use ($currentStatus) {
                    // Validate status transition
                    if (!$this->isValidTransition($currentStatus, $value)) {
                        $fail("Invalid status transition from '{$currentStatus}' to '{$value}'.");
                    }
                },
            ],
        ];
    }

    /**
     * Validate status transition.
     * 
     * Valid transitions:
     * - pending → confirmed (reduce stock)
     * - confirmed → shipped
     * - shipped → delivered
     * - Any status → cancelled (restore stock if needed)
     * 
     * Status updates are idempotent to prevent duplicate stock reductions.
     */
    private function isValidTransition(?string $from, string $to): bool
    {
        // Idempotent: same status is always valid
        if ($from === $to) {
            return true;
        }

        // Cancellation is always allowed
        if ($to === 'cancelled') {
            return true;
        }

        // Define valid transitions
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => ['cancelled'], // Can only cancel delivered orders
        ];

        return isset($validTransitions[$from]) && in_array($to, $validTransitions[$from]);
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status value.',
        ];
    }
}
