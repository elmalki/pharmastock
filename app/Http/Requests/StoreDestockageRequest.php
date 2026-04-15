<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDestockageRequest extends FormRequest
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
            'n_destockage' => 'required|string|max:255',
            'motifs' => 'required|string|max:500',
            'produits' => 'required|array|min:1',
            'produits.*.lots' => 'required|array|min:1',
            'produits.*.lots.*.produit_id' => 'required|exists:produits,id',
            'produits.*.lots.*.commande_id' => 'required|integer',
            'produits.*.lots.*.sortie' => 'required|integer|min:0',
            'produits.*.lots.*.qte' => 'required|integer|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'n_destockage' => 'numéro de destockage',
            'motifs' => 'motifs',
            'produits' => 'produits',
            'produits.*.lots' => 'lots',
            'produits.*.lots.*.produit_id' => 'produit',
            'produits.*.lots.*.commande_id' => 'commande',
            'produits.*.lots.*.sortie' => 'quantité à destocker',
            'produits.*.lots.*.qte' => 'quantité disponible',
        ];
    }
}
