<?php

namespace App\Http\Controllers;

use App\Models\CaisseMouvement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CaisseController extends Controller
{
    public function index(Request $request)
    {
        $solde = CaisseMouvement::soldeActuel();

        // Period stats (today, this month)
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $todayStats = CaisseMouvement::selectRaw("
            COALESCE(SUM(CASE WHEN type='entree' THEN montant ELSE 0 END), 0) as entrees,
            COALESCE(SUM(CASE WHEN type='sortie' THEN montant ELSE 0 END), 0) as sorties
        ")->where('created_at', '>=', $today)->first();

        $monthStats = CaisseMouvement::selectRaw("
            COALESCE(SUM(CASE WHEN type='entree' THEN montant ELSE 0 END), 0) as entrees,
            COALESCE(SUM(CASE WHEN type='sortie' THEN montant ELSE 0 END), 0) as sorties
        ")->where('created_at', '>=', $monthStart)->first();

        $kpis = [
            'solde'           => $solde,
            'entrees_jour'    => (float) $todayStats->entrees,
            'sorties_jour'    => (float) $todayStats->sorties,
            'entrees_mois'    => (float) $monthStats->entrees,
            'sorties_mois'    => (float) $monthStats->sorties,
        ];

        // Filters
        $type = $request->input('type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $sortField = in_array($request->input('field'), ['created_at', 'type', 'montant', 'motif'])
            ? $request->input('field') : 'created_at';
        $sortOrder = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $perPage = max(5, min(100, (int) $request->input('per_page', 15)));

        $query = CaisseMouvement::with(['user:id,name', 'vente:id,n_facture']);
        if ($type && in_array($type, ['entree', 'sortie'])) {
            $query->where('type', $type);
        }
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        $mouvements = $query->orderBy($sortField, $sortOrder)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Caisse/Index', [
            'kpis'       => $kpis,
            'mouvements' => $mouvements,
            'filters'    => compact('type', 'dateFrom', 'dateTo'),
            'sort'       => ['field' => $sortField, 'order' => $sortOrder, 'per_page' => $perPage],
        ]);
    }

    public function vider(Request $request)
    {
        $solde = CaisseMouvement::soldeActuel();

        $validated = $request->validate([
            'montant' => ['required', 'numeric', 'min:0.01', "max:$solde"],
            'motif'   => ['required', 'string', 'max:255'],
        ], [
            'montant.max' => "Montant supérieur au solde disponible ({$solde} MAD)",
            'montant.min' => 'Le montant doit être positif',
        ]);

        CaisseMouvement::create([
            'type'    => 'sortie',
            'montant' => $validated['montant'],
            'motif'   => $validated['motif'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('caisse.index')->banner('Caisse vidée de ' . number_format($validated['montant'], 2, ',', ' ') . ' MAD');
    }
}
