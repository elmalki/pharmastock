@include('pdf.style')
@extends('pdf.header')
@section('title')
    <title>Destockages {{ $year }}</title>
@endsection
@section('content')
    @php($grandTotal = $destockages->sum(fn($d) => $d->produits->sum(fn($p) => $p->pivot->qte ?? 0)))
    @php($totalProduits = $destockages->sum(fn($d) => $d->produits->count()))

    <style>
        .doc-head {
            padding: 18px 22px;
            background: linear-gradient(90deg, #ea580c 0%, #f97316 60%, #fb923c 100%);
            color: #fff;
            border-radius: 8px;
            margin-bottom: 14px;
        }
        .doc-head h1 { margin: 0; font-size: 22px; letter-spacing: 0.5px; }
        .doc-head .sub { font-size: 11px; opacity: 0.9; margin-top: 4px; }
        .doc-head .meta { font-size: 10px; margin-top: 8px; opacity: 0.85; }

        .stat-row { width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 8px 0; }
        .stat-row td {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
            width: 33.33%;
        }
        .stat-row .lbl { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #9a3412; font-weight: bold; }
        .stat-row .val { font-size: 20px; font-weight: bold; color: #7c2d12; margin-top: 2px; }

        table.pretty {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        table.pretty thead th {
            background: #1f2937 !important;
            color: #fff !important;
            padding: 8px 6px;
            font-weight: bold;
            font-size: 10px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            border: 1px solid #111827 !important;
        }
        table.pretty tbody td {
            padding: 7px 6px;
            border: 1px solid #e5e7eb !important;
            vertical-align: top;
        }
        table.pretty tbody tr:nth-child(even) td { background: #fafafa; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            background: #fff7ed;
            color: #9a3412;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #fed7aa;
        }
        .pill-qty {
            display: inline-block;
            min-width: 22px;
            padding: 1px 6px;
            border-radius: 10px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 10px;
            font-weight: bold;
            margin-left: 3px;
        }
        .prod-line { padding: 2px 0; border-bottom: 1px dotted #e5e7eb; }
        .prod-line:last-child { border-bottom: 0; }

        table.pretty tfoot td {
            background: #1f2937 !important;
            color: #fff !important;
            padding: 8px;
            font-weight: bold;
            border: 1px solid #111827 !important;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            font-style: italic;
        }
        .num { font-variant-numeric: tabular-nums; }
    </style>

    <div class="doc-head">
        <h1>Liste des destockages</h1>
        <div class="sub">Année {{ $year }}</div>
        <div class="meta">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

    <table class="stat-row">
        <tr>
            <td>
                <div class="lbl">Destockages</div>
                <div class="val">{{ $destockages->count() }}</div>
            </td>
            <td>
                <div class="lbl">Produits concernés</div>
                <div class="val">{{ $totalProduits }}</div>
            </td>
            <td>
                <div class="lbl">Total unités</div>
                <div class="val">{{ $grandTotal }}</div>
            </td>
        </tr>
    </table>

    <table class="pretty">
        <thead>
        <tr>
            <th style="width:8%">N°</th>
            <th style="width:10%">Date</th>
            <th style="width:22%">Motifs</th>
            <th style="width:14%">Utilisateur</th>
            <th style="width:36%">Produits</th>
            <th style="width:10%">Unités</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($destockages as $d)
            @php($total = $d->produits->sum(fn($p) => $p->pivot->qte ?? 0))
            <tr>
                <td class="text-center"><span class="badge">{{ $d->n_destockage }}</span></td>
                <td class="text-center num">{{ $d->created_at?->format('d/m/Y') }}</td>
                <td class="text-left">{{ $d->motifs }}</td>
                <td class="text-left">{{ $d->user?->name }}</td>
                <td class="text-left">
                    @foreach ($d->produits as $p)
                        <div class="prod-line">
                            {{ $p->label }}<span class="pill-qty">{{ $p->pivot->qte ?? 0 }}</span>
                        </div>
                    @endforeach
                </td>
                <td class="text-center num" style="font-weight:bold; font-size:13px; color:#7c2d12">{{ $total }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">Aucun destockage enregistré pour l'année {{ $year }}.</td></tr>
        @endforelse
        </tbody>
        @if ($destockages->isNotEmpty())
            <tfoot>
            <tr>
                <td colspan="5" class="text-right">Total général des unités destockées</td>
                <td class="text-center num">{{ $grandTotal }}</td>
            </tr>
            </tfoot>
        @endif
    </table>
@include('pdf.footer')
@endsection
