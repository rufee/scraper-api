<?php

namespace App\Http\Requests;

use App\Rules\CssSelector;
use Illuminate\Foundation\Http\FormRequest;

class StoreScrapeRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'urls'           => 'required|array',
            'urls.*'         => 'string|url',
            'selectors'      => 'required|array',
            'selectors.*'    => [new CssSelector()],
        ];
    }
}
