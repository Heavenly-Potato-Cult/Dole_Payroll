<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfficeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Role check is handled in the controller
    }

    public function rules(): array
    {
        return [
            'office_order_no'   => ['required', 'string', 'max:50', 'unique:office_orders,office_order_no'],
            'employee_id'       => ['required', 'integer', 'exists:employees,id'],
            'purpose'           => ['required', 'string', 'max:500'],
            'destination'       => ['required', 'string', 'max:255'],
            'travel_type'       => ['required', 'in:local,regional,national'],
            'travel_date_start' => ['required', 'date'],
            'travel_date_end'   => ['required', 'date', 'after_or_equal:travel_date_start'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'office_order_no.unique'          => 'This Office Order number already exists.',
            'travel_date_end.after_or_equal'  => 'End date must be on or after the start date.',
            'travel_type.in'                  => 'Travel type must be local, regional, or national.',
        ];
    }
}