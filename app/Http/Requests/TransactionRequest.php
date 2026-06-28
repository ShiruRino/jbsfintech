<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class TransactionRequest extends BaseRequest
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
              'account_id' => 'required|exists:accounts,id',  
              'category_id' => 'required|exists:categories,id',  
              'type' => 'required|in:income,expense',  
              'amount' => 'required|numeric|min:1',  
              'transaction_date' => 'sometimes|date',  
              'note' => 'sometimes',  
              'attachment_path' => 'sometimes|file|mimes:jpg,jpeg,png,webp,svg|max:2048',  
            ];
        }
        if($this->isMethod('PATCH') || $this->isMethod('PUT')){
            return [
              'account_id' => 'sometimes|exists:accounts,id',  
              'category_id' => 'sometimes|exists:categories,id',  
              'type' => 'sometimes|in:income,expense',  
              'amount' => 'sometimes|numeric|min:1',  
              'transaction_date' => 'sometimes|date',  
              'note' => 'sometimes',  
              'attachment_path' => 'sometimes|file|mimes:jpg,jpeg,png,webp,svg|max:2048',  
            ];
        }
        return [];
    }
}
