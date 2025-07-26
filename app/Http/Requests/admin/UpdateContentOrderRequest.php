<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Authorization is handled by the admin middleware on the route
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $moduleId = $this->route('module')->id;

        return [
            'contents' => ['required', 'array'],
            'contents.*.id' => [
                'required',
                'integer',
                Rule::exists('module_contents', 'id')->where(function ($query) use ($moduleId) {
                    $query->where('module_id', $moduleId);
                }),
            ],
            'contents.*.order' => ['required', 'integer', 'min:1'],
        ];
    }
}
