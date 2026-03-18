<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Facture {{ $vente->n_facture }}</title>
    <style>
        @page { margin: 15mm 15mm 15mm 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333;
            border: 2px solid #1a5276; padding: 15px;
        }

        /* ── Header ── */
        .header { width: 100%; margin-bottom: 15px; }
        .header td { vertical-align: top; }
        .pharmacy-name { font-size: 16px; font-weight: bold; color: #1a5276; line-height: 1.3; }
        .pharmacy-info { font-size: 9px; color: #555; margin-top: 4px; line-height: 1.6; }
        .pharmacy-info-icon { color: #1a5276; font-weight: bold; }
        .logo-cell { text-align: right; vertical-align: top; }
        .logo-cell img { height: 70px; }

        /* ── Facture title bar ── */
        .title-bar {
            background: #1a5276; color: #fff; padding: 10px 15px;
            margin-bottom: 18px;
        }
        .title-bar td { vertical-align: middle; }
        .title-bar-left { font-size: 20px; font-weight: bold; letter-spacing: 3px; }
        .title-bar-right { text-align: right; font-size: 11px; }
        .title-bar-num { font-size: 14px; font-weight: bold; }

        /* ── Info blocks ── */
        .info-blocks { width: 100%; margin-bottom: 18px; }
        .info-blocks td { vertical-align: top; width: 50%; }
        .info-block-bordered {
            border: 1px solid #ddd; border-radius: 4px; padding: 10px 12px;
            background-color: #fafbfc;
        }
        .info-title {
            font-size: 8px; text-transform: uppercase; letter-spacing: 1px;
            color: #1a5276; font-weight: bold; margin-bottom: 6px;
            padding-bottom: 4px; border-bottom: 1px solid #e0e0e0;
        }
        .info-row { margin-bottom: 2px; font-size: 10px; }
        .info-key { color: #888; }
        .info-val { color: #222; font-weight: 600; }
        .client-name { font-size: 13px; font-weight: bold; color: #222; margin-bottom: 4px; }

        /* ── Products table ── */
        table.products { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        table.products thead th {
            background: #1a5276; color: #fff; font-size: 8px; text-transform: uppercase;
            letter-spacing: 0.8px; padding: 8px 10px; font-weight: 600; text-align: left;
        }
        table.products thead th.r { text-align: right; }
        table.products thead th.c { text-align: center; }
        table.products tbody td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 10px; }
        table.products tbody td.r { text-align: right; }
        table.products tbody td.c { text-align: center; }
        table.products tbody tr:nth-child(even) td { background: #f8f9fa; }
        .product-name { font-weight: 600; color: #222; }
        .product-sub { font-size: 8px; color: #999; }

        /* ── Totals ── */
        .totals-area { width: 100%; margin-top: 0; }
        .totals-area td { vertical-align: top; }
        .totals-left { width: 55%; padding-top: 8px; padding-right: 10px; }
        .totals-right { width: 45%; }

        table.totals { width: 100%; border-collapse: collapse; }
        table.totals td { padding: 5px 10px; font-size: 10px; }
        table.totals td.label { color: #888; }
        table.totals td.value { text-align: right; font-weight: 600; color: #333; }
        table.totals td.discount { color: #27ae60; }
        table.totals tr.grand-total td {
            background: #1a5276; color: #fff; font-size: 13px; font-weight: bold; padding: 10px;
        }
        table.totals tr.grand-total td.label { color: #cde; }

        /* ── Montant en lettres ── */
        .amount-words {
            margin-top: 12px; padding: 8px 12px;
            border: 1px solid #ccc; border-radius: 4px;
            background: #f7f9fb; font-size: 10px;
        }
        .amount-words-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; color: #1a5276; font-weight: bold; }
        .amount-words-text { margin-top: 3px; font-weight: 600; color: #222; font-style: italic; }

        /* ── Payment ── */
        .payment-section { width: 100%; margin-top: 12px; border-collapse: collapse; }
        .payment-section td {
            border: 1px solid #ddd; padding: 8px 12px; vertical-align: top;
            background: #fafbfc;
        }
        .pay-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.8px; color: #999; font-weight: bold; }
        .pay-val { font-size: 12px; font-weight: bold; color: #222; margin-top: 2px; }
        .pay-green { color: #27ae60; }
        .pay-orange { color: #e67e22; }

        /* ── Footer ── */
        .footer {
            margin-top: 20px; padding-top: 8px;
            border-top: 1px solid #ddd; text-align: center;
            font-size: 8px; color: #bbb;
        }
    </style>
</head>
<body>

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

    <!-- Facture title bar -->
    <table class="title-bar" cellpadding="0" cellspacing="0" style="width:100%;">
        <tr>
            <td class="title-bar-left">FACTURE</td>
            <td class="title-bar-right">
                <div class="title-bar-num">N° {{ $vente->n_facture }}</div>
                <div>{{ \Carbon\Carbon::parse($vente->date)->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Client + Details -->
    <table class="info-blocks" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="info-block-bordered" style="margin-right: 10px;">
                    <div class="info-title">Informations</div>
                    <div class="info-row"><span class="info-key">Date:</span> <span class="info-val">{{ \Carbon\Carbon::parse($vente->date)->format('d/m/Y') }}</span></div>
                    <div class="info-row"><span class="info-key">Paiement:</span> <span class="info-val">{{ $vente->paiement?->value ?? $vente->paiement }}</span></div>
                    @if($vente->dateEcheance)
                        <div class="info-row"><span class="info-key">Échéance:</span> <span class="info-val">{{ \Carbon\Carbon::parse($vente->dateEcheance)->format('d/m/Y') }}</span></div>
                    @endif
                </div>
            </td>
            <td>
                <div class="info-block-bordered">
                    <div class="info-title">Facturé à</div>
                    <div class="client-name">{{ $vente->client->nom ?? 'Client comptoir' }}</div>
                    @if($vente->client?->tel)
                        <div class="info-row"><span class="info-key">Tél:</span> {{ $vente->client->tel }}</div>
                    @endif
                    @if($vente->client?->email)
                        <div class="info-row"><span class="info-key">Email:</span> {{ $vente->client->email }}</div>
                    @endif
                    @if($vente->client?->adresse)
                        <div class="info-row"><span class="info-key">Adresse:</span> {{ $vente->client->adresse }}</div>
                    @endif
                </div>
            </td>

        </tr>
    </table>

    <!-- Products -->
    <table class="products" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 7%;">#</th>
                <th>Désignation</th>
                <th class="c" style="width: 8%;">Qté</th>
                <th class="r" style="width: 14%;">P.U.HT</th>
                <th class="c" style="width: 8%;">TVA</th>
                <th class="c" style="width: 8%;">TTC</th>
                <th class="r" style="width: 16%;">Montant TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vente->produits as $i => $produit)
                <tr>
                    <td class="c" style="color: #999;">{{ $i + 1 }}</td>
                    <td>
                        <span class="product-name">{{ $produit->label }}</span>
                        @if($produit->dci)
                            <br><span class="product-sub">{{ $produit->dci }}</span>
                        @endif
                    </td>
                    <td class="c">{{ $produit->pivot->qte }}</td>
                    <td class="r">{{ number_format($produit->pivot->prix*(1+$produit->pivot->tva*0.01), 2, ',', ' ') }}</td>
                    <td class="c">{{ $produit->pivot->tva > 0 ? $produit->pivot->tva . '%' : '0%' }}</td>
                    <td class="c">{{ number_format($produit->pivot->prix, 2, ',', ' ') }}</td>
                    <td class="r" style="font-weight: 600;">{{ number_format($produit->pivot->prix * $produit->pivot->qte, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals + Amount in words -->
    @php
        $tvaGroups = [];
        foreach ($vente->produits as $p) {
            $tva = (float) $p->pivot->tva;
            if ($tva > 0) {
                $tvaGroups[$tva] = ($tvaGroups[$tva] ?? 0) + $p->pivot->qte  * ($p->pivot->prix * $tva /($tva + 100));
            }
        }
    @endphp

    <table class="totals-area" cellpadding="0" cellspacing="0">
        <tr>
            <td class="totals-left">
                <div class="amount-words">
                    <div class="amount-words-label">Arrêtée la présente facture à la somme de</div>
                    <div class="amount-words-text">{{ \App\Helpers\NumberToWords::convert($vente->total) }}</div>
                </div>
            </td>
            <td class="totals-right">
                <table class="totals" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Sous-total TTC</td>
                        <td class="value">{{ number_format($vente->subtotal, 2, ',', ' ') }}</td>
                    </tr>
                    @if((float)$vente->remise > 0)
                        <tr>
                            <td class="label">Remise ({{ $vente->remise }}%)</td>
                            <td class="value discount">- {{ number_format($vente->remise_amount, 2, ',', ' ') }}</td>
                        </tr>
                    @endif
                    @foreach($tvaGroups as $rate => $amount)
                        <tr>
                            <td class="label">TVA {{ $rate }}%</td>
                            <td class="value">{{ number_format($amount, 2, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                    <tr class="grand-total">
                        <td class="label">Total TTC</td>
                        <td class="value" style="text-align: right;">{{ number_format($vente->total, 2, ',', ' ') }} MAD</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Payment summary -->
    <table class="payment-section" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 34%;">
                <div class="pay-label">Mode de paiement</div>
                <div class="pay-val">{{ $vente->paiement?->value ?? $vente->paiement }}</div>
            </td>
            <td style="width: 33%;">
                <div class="pay-label">Montant payé</div>
                <div class="pay-val pay-green">{{ number_format($vente->montantPaye, 2, ',', ' ') }} MAD</div>
            </td>
            <td style="width: 33%;">
                <div class="pay-label">Reste à payer</div>
                <div class="pay-val {{ (float)$vente->reste > 0 ? 'pay-orange' : '' }}">{{ number_format($vente->reste, 2, ',', ' ') }} MAD</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ env('NAME_PHARMACY', 'Pharmacie') }} — Facture générée le {{ now()->format('d/m/Y à H:i') }}
    </div>

</body>
</html>
