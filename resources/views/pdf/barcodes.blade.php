@include('pdf.style')
@extends('pdf.header')
@section('title')
    <title>Codes-barres</title>
@endsection
@section('content')
    @php($dns = new \Milon\Barcode\DNS1D())
    @php($dns->setStorPath(storage_path('framework/barcodes/')))
    <table class="table table-condensed table-bordered margin">
        <thead>
        <tr class="active">
            <th style="border:1px solid black; width:50%">Désignation</th>
            <th style="border:1px solid black; width:50%">Code à barres</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($produits as $produit)
            <tr>
                <td style="border:1px solid black; padding:6px">
                    <strong>{{ $produit->label }}</strong>
                    @if($produit->dosage)
                        <br><small>{{ $produit->dosage }}</small>
                    @endif
                    <br><small style="font-family: monospace">{{ $produit->barcode }}</small>
                </td>
                <td style="border:1px solid black; text-align:center; padding:6px">
                    @if($produit->barcode)
                        <img src="data:image/png;base64,{{ $dns->getBarcodePNG($produit->barcode, 'C128', 2, 60) }}" alt="{{ $produit->barcode }}">
                    @else
                        <em>Aucun code</em>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@include('pdf.footer')
@endsection
