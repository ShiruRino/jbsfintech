<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends BaseRequest
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
                'name'=> 'required',
                'type'=> 'required|in:income,expense',
                'icon'=> 'sometimes',
                'is_active'=> 'sometimes|boolean',
            ];
            
        }
        if($this->isMethod('PATCH') || $this->isMethod('PUT')){
            return [
                'name'=> 'sometimes',
                'type'=> 'sometimes|in:income,expense',
                'icon'=> 'sometimes',
                'is_active'=> 'sometimes|boolean',
            ];
        }
        return [];
    }
}
