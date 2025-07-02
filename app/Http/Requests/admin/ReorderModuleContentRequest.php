<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderModuleContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorize if the user is an admin or has appropriate permissions
        return true;
    }

    public function rules(): array
    {
        return [
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:module_contents,id',
            'contents.*.order' => 'required|integer|min:0'
        ];
    }
}