<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'items' => 'nullable|array',
            'items.*.title' => 'required|string|max:255',
            'items.*.url' => 'nullable|string',
            'items.*.target' => 'nullable|in:_self,_blank,_parent,_top',
            'items.*.type' => 'nullable|in:custom,post,category,page',
            'items.*.order' => 'nullable|integer',
        ];
    }
}
