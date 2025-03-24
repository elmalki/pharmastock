<table class="table table-condensed table-bordered margin">
    <thead>
    <tr class="active">
        <th style="broder:1px solid black">N° Lot</th>
        <th style="broder:1px solid black">Produit</th>
        <th style="broder:1px solid black">Catégorie</th>
        <th style="broder:1px solid black">Qte périmées</th>
        <th style="broder:1px solid black">Date péremption</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($items as $item)
        <tr>
            <td style="broder:1px solid black"> {{$item->n_lot}}</td>
            <td style="broder:1px solid black"> {{$item->produit->label}}</td>
            <td style="broder:1px solid black"> {{$item->produit->categorie?->label}}</td>
            <td style="broder:1px solid black"> {{$item->produit->qtePerime}}</td>
            <td style="broder:1px solid black"> {{$item->expirationDate}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
