@include('pdf.style')
@extends('pdf.header')
@section('title')
    <title>Stock</title>
@endsection
@section('content')
<table class="table table-condensed table-bordered margin">
    <thead>
    <tr class="active">
        <th style="broder:1px solid black">Désignation</th>
        <th style="broder:1px solid black">Quantité périmée</th>
        <th style="broder:1px solid black">Quantité en stock</th>
        <th style="broder:1px solid black">Prix d'achat</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($produits as $produit)
        <tr>
            <td style="broder:1px solid black"> {{$produit->label}}</td>
            <td style="broder:1px solid black"> {{$produit->qtePerime}}</td>
            <td style="broder:1px solid black"> {{$produit->qte}}</td>
            <td style="broder:1px solid black"> {{$produit->prixachat}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@include('pdf.footer')
@endsection
