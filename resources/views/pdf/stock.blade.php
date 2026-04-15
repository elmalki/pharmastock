<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>État du Stock</title>
    <style>
        @page { margin: 12mm 12mm 14mm 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #333;
        }

        /* ── Header ── */
        .header { width: 100%; margin-bottom: 12px; }
        .header td { vertical-align: top; }
        .pharmacy-name { font-size: 16px; font-weight: bold; color: #1a5276; line-height: 1.3; }
        .pharmacy-info { font-size: 9px; color: #555; margin-top: 4px; line-height: 1.6; }
        .pharmacy-info-icon { color: #1a5276; font-weight: bold; }
        .logo-cell { text-align: right; vertical-align: top; }
        .logo-cell img { height: 60px; }

        /* ── Title bar ── */
        .title-bar {
            background: #1a5276; color: #fff; padding: 10px 15px;
            margin-bottom: 14px;
        }
        .title-bar td { vertical-align: middle; }
        .title-bar-left { font-size: 17px; font-weight: bold; letter-spacing: 2px; }
        .title-bar-right { text-align: right; font-size: 10px; }
        .title-bar-sub { font-size: 12px; font-weight: bold; }

        /* ── Stats strip ── */
        .stats { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 12px; }
        .stats td {
            border: 1px solid #e0e0e0; border-radius: 4px;
            padding: 8px 10px; background: #fafbfc; width: 25%;
        }
        .stat-label {
            font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px;
            color: #888; font-weight: bold;
        }
        .stat-value { font-size: 15px; font-weight: bold; margin-top: 2px; }
        .stat-rupture { color: #c0392b; }
        .stat-critique { color: #d68910; }
        .stat-ok { color: #1a5276; }
        .stat-value-green { color: #27ae60; }

        /* ── Products table ── */
        table.products { width: 100%; border-collapse: collapse; }
        table.products thead th {
            background: #1a5276; color: #fff; font-size: 8px; text-transform: uppercase;
            letter-spacing: 0.6px; padding: 7px 6px; font-weight: 600; text-align: left;
        }
        table.products thead th.r { text-align: right; }
        table.products thead th.c { text-align: center; }
        table.products tbody td {
            padding: 6px; border-bottom: 1px solid #eee;
            font-size: 9px; vertical-align: middle;
        }
        table.products tbody td.r { text-align: right; }
        table.products tbody td.c { text-align: center; }
        table.products tbody tr:nth-child(even) td { background: #f8f9fa; }
        .product-name { font-weight: 600; color: #222; }
        .product-sub { font-size: 8px; color: #888; }
        .barcode { font-family: 'Courier New', monospace; font-size: 8px; color: #666; }

        /* ── Status badges ── */
        .badge {
            display: inline-block; padding: 2px 6px; border-radius: 10px;
            font-size: 8px; font-weight: bold; text-transform: uppercase;
            letter-spacing: 0.4px; white-space: nowrap;
        }
        .badge-rupture { background: #fdecea; color: #c0392b; border: 1px solid #f5c6c4; }
        .badge-critique { background: #fef4e4; color: #b9770e; border: 1px solid #f7dcb3; }
        .badge-ok { background: #e8f6ef; color: #1e8449; border: 1px solid #bfe0cd; }

        .qte-critique { color: #c0392b; font-weight: 600; }
        .qte-warning { color: #d68910; font-weight: 600; }
        .perime { color: #c0392b; font-weight: 600; }

        /* ── Footer ── */
        .footer {
            position: fixed; bottom: -8mm; left: 0; right: 0;
            text-align: center; font-size: 8px; color: #aaa;
            border-top: 1px solid #ddd; padding-top: 4px;
        }
    </style>
</head>
<body style=" margin:12px">
    <!-- Header: Pharmacy info (left) + Logo (right) -->
    <table class="header" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="pharmacy-name">{{ env('PHARMACY_NAME', 'Pharmacie') }}</div>
                <div class="pharmacy-info">
                    @if(env('PHARMACY_DOCTOR'))
                        <span class="pharmacy-info-icon">&#9655;</span> {{ env('PHARMACY_DOCTOR') }}<br>
                    @endif
                    @if(env('PHARMACY_PHONE'))
                        <span class="pharmacy-info-icon">&#9742;</span> {{ env('PHARMACY_PHONE') }}<br>
                    @endif
                    @if(env('PHARMACY_ADDRESS'))
                        <span class="pharmacy-info-icon">&#9656;</span> {{ env('PHARMACY_ADDRESS') }}
                    @endif
                </div>
            </td>
            <td class="logo-cell">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo">
            </td>
        </tr>
    </table>

    <!-- Title bar -->
    <table class="title-bar" cellpadding="0" cellspacing="0" style="width:100%;">
        <tr>
            <td class="title-bar-left">ÉTAT DU STOCK</td>
            <td class="title-bar-right">
                <div class="title-bar-sub">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div>Édité le {{ now()->format('d/m/Y à H:i') }}</div>
            </td>
        </tr>
    </table>

    <!-- Stats strip -->
    <table class="stats" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="stat-label">Total produits</div>
                <div class="stat-value stat-ok">{{ $stats['total'] }}</div>
            </td>
            <td>
                <div class="stat-label">En rupture</div>
                <div class="stat-value stat-rupture">{{ $stats['rupture'] }}</div>
            </td>
            <td>
                <div class="stat-label">Stock critique</div>
                <div class="stat-value stat-critique">{{ $stats['critique'] }}</div>
            </td>
            <td>
                <div class="stat-label">Valeur du stock</div>
                <div class="stat-value stat-value-green">{{ number_format($stats['valeur'], 2, ',', ' ') }} MAD</div>
            </td>
        </tr>
    </table>

    <!-- Products table -->
    <table class="products" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="c" style="width: 4%;">#</th>
                <th style="width: 11%;">Code-barres</th>
                <th style="width: 28%;">Désignation</th>
                <th style="width: 12%;">Catégorie</th>
                <th class="c" style="width: 8%;">Stock</th>
                <th class="c" style="width: 8%;">Périmé</th>
                <th class="c" style="width: 11%;">Statut</th>
                <th class="r" style="width: 9%;">Prix public</th>
                <th class="r" style="width: 9%;">Valeur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produits as $i => $produit)
                @php
                    $qte = (int) ($produit->qte ?? 0);
                    $limit = (int) ($produit->limit_command ?? 0);
                    $perime = (int) ($produit->qtePerime ?? 0);
                    $prix = (float) ($produit->prix_public ?? 0);
                    $valeur = $qte * $prix;

                    if ($qte === 0) {
                        $statut = ['label' => 'Rupture', 'class' => 'badge-rupture'];
                        $qteClass = 'qte-critique';
                    } elseif ($limit > 0 && $qte <= $limit) {
                        $statut = ['label' => 'Critique', 'class' => 'badge-critique'];
                        $qteClass = 'qte-warning';
                    } else {
                        $statut = ['label' => 'Disponible', 'class' => 'badge-ok'];
                        $qteClass = '';
                    }
                @endphp
                <tr>
                    <td class="c" style="color: #999;">{{ $i + 1 }}</td>
                    <td class="barcode">{{ $produit->barcode ?: '—' }}</td>
                    <td>
                        <span class="product-name">{{ $produit->label }}</span>
                        @if($produit->dci || $produit->dosage)
                            <br><span class="product-sub">
                                {{ $produit->dci }}@if($produit->dci && $produit->dosage) · @endif{{ $produit->dosage }}
                            </span>
                        @endif
                    </td>
                    <td>{{ $produit->categorie->label ?? '—' }}</td>
                    <td class="c {{ $qteClass }}">{{ $qte }}</td>
                    <td class="c {{ $perime > 0 ? 'perime' : '' }}">
                        {{ $perime > 0 ? $perime : '—' }}
                    </td>
                    <td class="c">
                        <span class="badge {{ $statut['class'] }}">{{ $statut['label'] }}</span>
                    </td>
                    <td class="r">{{ number_format($prix, 2, ',', ' ') }}</td>
                    <td class="r" style="font-weight: 600;">{{ number_format($valeur, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ env('PHARMACY_NAME', 'Pharmacie') }} — État du stock généré le {{ now()->format('d/m/Y à H:i') }}
    </div>

</body>
</html>
