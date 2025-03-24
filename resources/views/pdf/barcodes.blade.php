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
        <th style="broder:1px solid black">Code à barre</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($produits as $produit)
        <tr>
            <td style="broder:1px solid black"> {{$produit->label}}</td>
            <td style="broder:1px solid black"><img src="data:image/png;base64,'{{$produit->image}}" alt=""></td>
        </tr>
    @endforeach
    </tbody>
</table>
@include('pdf.footer')
@endsection
