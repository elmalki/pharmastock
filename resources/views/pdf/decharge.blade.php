@include('pdf.style')
@extends('pdf.header')
@section('title')
    <title>Stock</title>
@endsection
@section('content')
    <div class="w-full text-center ">
        <h1 class="text-lg" style="text-decoration: underline">Décharge</h1>
    </div>
    <div class="w-full text-center ">
        <h1 class="text-lg" style="text-decoration: underline">N° {{$destockage->n_destockage}}</h1>
    </div>
    <div class="w-full">
        <div  style="font-size: 20px; margin-top: 10px">
            Le {{\Carbon\Carbon::parse($destockage->created_at)->format('d/m/Y')}}; Je sousigne, Mr: <strong>{{$destockage->fonctionnaire}}</strong>
        </div>
        <div  style="font-size: 20px; margin-top: 10px">
            Service:....................................................
        </div>
        <div  style="font-size: 20px; margin-top: 10px">
            Avoir reçu du magasinier de la commune de Massa la fourniture ci-après:
        </div>
    </div>
<table class="table table-condensed table-bordered margin" style="margin-top: 40px">
    <thead>
    <tr class="active">
        <th style="broder:1px solid black">Désignation</th>
        <th style="broder:1px solid black">Quantité</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($destockage->produits as $produit)
        <tr>
            <td style="broder:1px solid black"> {{$produit->label}}</td>
            <td style="broder:1px solid black"> {{$produit->pivot->qte}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
    <div class="w-full text-right" style=" margin-top: 30px">
        <h2 class="text-lg" style="text-decoration: underline">Signature</h2>
    </div>
@include('pdf.footer')
@endsection
