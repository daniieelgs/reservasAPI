<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservaFreeBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'data_inici' => 'required|date_format:Y-m-d',
            'hora_inici' => 'required|date_format:H:00:00',
            'data_final' => 'required|date_format:Y-m-d',
            'hora_final' => 'required|date_format:H:00:00'
        ];
    }
}
