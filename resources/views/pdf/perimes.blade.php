@include('pdf.style')
@extends('pdf.header')
@section('title')
    <title>Stock-Périmé</title>
@endsection
@section('content')
<table class="table table-condensed table-bordered margin">
    <thead>
    <tr class="active">
        <th style="broder:1px solid black">N° Lot</th>
        <th style="broder:1px solid black">Libellé</th>
        <th style="broder:1px solid black">Catégorie</th>
        <th style="broder:1px solid black">Qte périmé</th>
    </tr>
    @foreach ($items as $item)
        <tr>
            <td style="broder:1px solid black"> {{$item->n_lot}}</td>
            <td style="broder:1px solid black"> {{$item->produit->label}}</td>
            <td style="broder:1px solid black"> {{$item->produit->categorie->label}}</td>
            <td style="broder:1px solid black"> {{$item->produit->qtePerime}}</td>
        </tr>
    @endforeach
    </thead>
    <tbody>

    </tbody>
</table>
@include('pdf.footer')
@endsection
