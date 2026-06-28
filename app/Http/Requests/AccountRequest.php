<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if($this->isMethod('POST')){
            return [
                'name' => 'required',
                'type' => 'required|in:cash,bank,ewallet',
                'initial_balance' => 'required|numeric',
                'is_active' => 'required|boolean',
            ];
        }
        elseif($this->isMethod('PATCH') || $this->isMethod('PUT')){
            return [
                'name' => 'nullable',
                'type'=> 'in:cash,bank,ewallet',
                'initial_balance' => 'numeric',
                'is_active' => 'boolean'
            ];
        }
        else{
            return [];
        }
    }
}
