<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        $customerId = $this->route('customer');

        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId)
            ],
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'city' => 'sometimes|nullable|string|max:255',
            'state' => 'sometimes|nullable|string|max:255',
            'zip_code' => 'sometimes|nullable|string|max:20',
            'country' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|in:active,inactive,prospect',
            'source' => 'sometimes|nullable|string|max:255',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'status.in' => 'Status must be active, inactive, or prospect.',
            'assigned_to.exists' => 'Selected user does not exist.',
        ];
    }
}
