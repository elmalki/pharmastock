<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommandeRequest extends FormRequest
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
            'n_bon' => 'required|string|max:255',
            'n_facture' => 'nullable|string|max:255',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'date' => 'required|date',
            'paiement' => 'required|string',
            'dateEcheance' => 'nullable|date',
            'situation' => 'nullable|string',
            'produits' => 'required|array|min:1',
            'produits.*.id' => 'required|exists:produits,id',
            'produits.*.entree.qte' => 'required|integer|min:1',
            'produits.*.entree.prix_achat' => 'required|numeric|min:0',
            'produits.*.entree.prix_vente' => 'required|numeric|min:0',
            'produits.*.entree.n_lot' => 'nullable|string|max:255',
            'produits.*.entree.expirationDate' => 'nullable|date',
            'produits.*.entree.tva' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function attributes(): array
    {
        return [
            'n_bon' => 'numéro de bon',
            'n_facture' => 'numéro de facture',
            'fournisseur_id' => 'fournisseur',
            'date' => 'date',
            'paiement' => 'mode de paiement',
            'dateEcheance' => 'date d\'échéance',
            'situation' => 'situation',
            'produits' => 'produits',
            'produits.*.id' => 'produit',
            'produits.*.entree.qte' => 'quantité',
            'produits.*.entree.prix_achat' => 'prix d\'achat',
            'produits.*.entree.prix_vente' => 'prix de vente',
            'produits.*.entree.n_lot' => 'numéro de lot',
            'produits.*.entree.expirationDate' => 'date de péremption',
            'produits.*.entree.tva' => 'TVA',
        ];
    }

}
