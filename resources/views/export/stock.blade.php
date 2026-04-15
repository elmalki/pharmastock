<table>
    {{-- Row 1: Pharmacy name --}}
    <tr>
        <td colspan="11">{{ env('PHARMACY_NAME', 'Pharmacie') }}</td>
    </tr>
    {{-- Row 2: Contact --}}
    <tr>
        <td colspan="11">
            @php
                $parts = array_filter([
                    env('PHARMACY_DOCTOR'),
                    env('PHARMACY_PHONE'),
                    env('PHARMACY_ADDRESS'),
                ]);
            @endphp
            {{ implode('  —  ', $parts) }}
        </td>
    </tr>
    {{-- Row 3: Title --}}
    <tr>
        <td colspan="11">ÉTAT DU STOCK</td>
    </tr>
    {{-- Row 4: Generation date --}}
    <tr>
        <td colspan="11">Édité le {{ now()->format('d/m/Y à H:i') }}</td>
    </tr>
    {{-- Row 5: empty --}}
    <tr><td colspan="11"></td></tr>

    {{-- Row 6: stats labels --}}
    <tr>
        <td colspan="2">Total produits</td>
        <td colspan="2">En rupture</td>
        <td colspan="2">Stock critique</td>
        <td colspan="5">Valeur du stock</td>
    </tr>
    {{-- Row 7: stats values --}}
    <tr>
        <td colspan="2">{{ $stats['total'] }}</td>
        <td colspan="2">{{ $stats['rupture'] }}</td>
        <td colspan="2">{{ $stats['critique'] }}</td>
        <td colspan="5">{{ number_format($stats['valeur'], 2, ',', ' ') }} MAD</td>
    </tr>
    {{-- Row 8: empty --}}
    <tr><td colspan="11"></td></tr>

    {{-- Row 9: table headers --}}
    <tr>
        <th>#</th>
        <th>Code-barres</th>
        <th>Désignation</th>
        <th>DCI</th>
        <th>Dosage</th>
        <th>Catégorie</th>
        <th>Stock</th>
        <th>Périmé</th>
        <th>Statut</th>
        <th>Prix public</th>
        <th>Valeur</th>
    </tr>

    {{-- Data rows --}}
    @foreach($produits as $i => $produit)
        @php
            $qte = (int) ($produit->qte ?? 0);
            $limit = (int) ($produit->limit_command ?? 0);
            $perime = (int) ($produit->qtePerime ?? 0);
            $prix = (float) ($produit->prix_public ?? 0);
            $valeur = $qte * $prix;

            if ($qte === 0) {
                $statutLabel = 'Rupture';
            } elseif ($limit > 0 && $qte <= $limit) {
                $statutLabel = 'Critique';
            } else {
                $statutLabel = 'Disponible';
            }
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $produit->barcode ?: '' }}</td>
            <td>{{ $produit->label }}</td>
            <td>{{ $produit->dci ?: '' }}</td>
            <td>{{ $produit->dosage ?: '' }}</td>
            <td>{{ $produit->categorie->label ?? '' }}</td>
            <td>{{ $qte }}</td>
            <td>{{ $perime }}</td>
            <td>{{ $statutLabel }}</td>
            <td>{{ number_format($prix, 2, '.', '') }}</td>
            <td>{{ number_format($valeur, 2, '.', '') }}</td>
        </tr>
    @endforeach

    {{-- Total row --}}
    <tr>
        <td colspan="10">TOTAL VALEUR STOCK</td>
        <td>{{ number_format($stats['valeur'], 2, '.', '') }}</td>
    </tr>
</table>
