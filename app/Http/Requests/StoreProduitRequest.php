<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduitRequest extends FormRequest
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
            'label' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'dci' => 'nullable|string|max:255',
            'forme' => 'nullable|string|max:100',
            'dosage' => 'nullable|string|max:100',
            'laboratoire' => 'nullable|string|max:255',
            'unite' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'perissable' => 'boolean',
            'ordonnance_requise' => 'boolean',
            'prix_public' => 'nullable|numeric|min:0',
            'generated' => 'boolean',
            'limit_command' => 'nullable|integer|min:0',
            'categorie_id' => 'nullable|exists:categories,id',
        ];
    }
}
