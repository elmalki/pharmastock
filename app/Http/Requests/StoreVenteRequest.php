<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenteRequest extends FormRequest
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
            'date' => 'required|date',
            'paiement' => 'required|string',
            'client_id' => 'nullable|exists:clients,id',
            'n_facture' => 'nullable|string|max:255',
            'dateEcheance' => 'nullable|date',
            'montantPaye' => 'required|numeric|min:0',
            'remise' => 'nullable|numeric|min:0|max:100',
            'produits' => 'required|array|min:1',
            'produits.*.id' => 'required|exists:produits,id',
            'produits.*.lots' => 'required|array|min:1',
            'produits.*.lots.*.n_lot' => 'required|string',
            'produits.*.lots.*.commande_id' => 'required|integer',
            'produits.*.lots.*.sortie' => 'required|integer|min:0',
            'produits.*.lots.*.prix_vente' => 'required|numeric|min:0',
        ];
    }
}
