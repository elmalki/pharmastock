<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeProduit;
use App\Models\Produit;
use App\Models\Vente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private const MONTH_NAMES = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    private const DAY_NAMES = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');

        // Product stats
        $total = Produit::count();
        $en_rupture = Produit::whereNotNull('limit_command')
            ->where('limit_command', '>', 0)
            ->whereRaw('(SELECT COALESCE(SUM(qte), 0) FROM commande_produit WHERE commande_produit.produit_id = produits.id) < produits.limit_command')
            ->count();
        $perimes = CommandeProduit::whereNotNull('expirationDate')
            ->where('expirationDate', '<', now())
            ->where('qte', '>', 0)
            ->count();

        // Date ranges
        [$start, $end] = $this->getDateRange($period);
        [$prevStart, $prevEnd] = $this->getPreviousDateRange($period);

        // All dashboard data
        $stats = $this->computeStats($start, $end, $prevStart, $prevEnd);
        $topProducts = $this->getTopProducts($start, $end);
        $creancesClients = $this->getCreancesClients();
        $dettesFournisseurs = $this->getDettesFournisseurs();
        $alertes = $this->getAlertes();
        $activites = $this->getActivites();
        $salesChart = $this->getSalesChartData();
        $categoryChart = $this->getCategoryChartData($start, $end);
        $cashFlowChart = $this->getCashFlowChartData();

        return Inertia::render('Dashboard', compact(
            'total', 'en_rupture', 'perimes', 'period',
            'stats', 'topProducts', 'creancesClients', 'dettesFournisseurs',
            'alertes', 'activites', 'salesChart', 'categoryChart', 'cashFlowChart'
        ));
    }

    // ─── Stats Cards ─────────────────────────────────────────────

    private function computeStats($start, $end, $prevStart, $prevEnd): array
    {
        // Ventes today vs yesterday
        $ventesToday = Vente::whereDate('date', today())->count();
        $ventesYesterday = Vente::whereDate('date', today()->subDay())->count();
        $ventesGrowth = $ventesYesterday > 0
            ? round(($ventesToday - $ventesYesterday) / $ventesYesterday * 100, 1)
            : ($ventesToday > 0 ? 100 : 0);

        // CA = subtotal - remise_amount
        $caExpr = 'COALESCE(SUM(COALESCE(subtotal, 0) - COALESCE(remise_amount, 0)), 0)';
        $ca = (float) Vente::whereBetween('date', [$start, $end])->selectRaw("$caExpr as v")->value('v');
        $caPrev = (float) Vente::whereBetween('date', [$prevStart, $prevEnd])->selectRaw("$caExpr as v")->value('v');
        $caMonth = (float) Vente::whereYear('date', now()->year)->whereMonth('date', now()->month)->selectRaw("$caExpr as v")->value('v');
        $caGrowth = $caPrev > 0 ? round(($ca - $caPrev) / $caPrev * 100, 1) : ($ca > 0 ? 100 : 0);

        // Bénéfice from vente_produit (more reliable than benefice column)
        $benefExpr = 'COALESCE(SUM((vente_produit.prix - vente_produit.prix_achat) * vente_produit.qte), 0)';
        $benefice = (float) DB::table('vente_produit')
            ->join('ventes', 'vente_produit.vente_id', '=', 'ventes.id')
            ->whereBetween('ventes.date', [$start, $end])
            ->whereNull('ventes.deleted_at')
            ->selectRaw("$benefExpr as v")->value('v');
        $beneficePrev = (float) DB::table('vente_produit')
            ->join('ventes', 'vente_produit.vente_id', '=', 'ventes.id')
            ->whereBetween('ventes.date', [$prevStart, $prevEnd])
            ->whereNull('ventes.deleted_at')
            ->selectRaw("$benefExpr as v")->value('v');
        $marge = $ca > 0 ? round($benefice / $ca * 100, 1) : 0;
        $beneficeGrowth = $beneficePrev > 0
            ? round(($benefice - $beneficePrev) / $beneficePrev * 100, 1)
            : ($benefice > 0 ? 100 : 0);

        // Créances (unpaid sales)
        $creances = (float) Vente::where('reste', '>', 0)->sum('reste');
        $facturesEnAttente = Vente::where('reste', '>', 0)->count();

        // Solde = total encaissé - total décaissé
        $totalEncaisse = (float) Vente::sum('montantPaye');
        $totalDecaisse = (float) Commande::sum('montantPaye');

        return [
            'ventesToday' => $ventesToday,
            'ventesGrowth' => $ventesGrowth,
            'ventesIncrease' => $ventesToday - $ventesYesterday,
            'ca' => $ca,
            'caMonth' => $caMonth,
            'caGrowth' => $caGrowth,
            'benefice' => $benefice,
            'marge' => $marge,
            'beneficeGrowth' => $beneficeGrowth,
            'creances' => $creances,
            'facturesEnAttente' => $facturesEnAttente,
            'solde' => $totalEncaisse - $totalDecaisse,
        ];
    }

    // ─── Top Products ────────────────────────────────────────────

    private function getTopProducts($start, $end)
    {
        return DB::table('vente_produit')
            ->join('produits', 'vente_produit.produit_id', '=', 'produits.id')
            ->join('ventes', 'vente_produit.vente_id', '=', 'ventes.id')
            ->whereBetween('ventes.date', [$start, $end])
            ->whereNull('ventes.deleted_at')
            ->select(
                'produits.id',
                'produits.label as nom',
                DB::raw('SUM(vente_produit.qte) as quantite'),
                DB::raw('SUM(vente_produit.prix * vente_produit.qte) as montant')
            )
            ->groupBy('produits.id', 'produits.label')
            ->orderByDesc('montant')
            ->limit(5)
            ->get();
    }

    // ─── Créances Clients ────────────────────────────────────────

    private function getCreancesClients()
    {
        return DB::table('ventes')
            ->join('clients', 'ventes.client_id', '=', 'clients.id')
            ->where('ventes.reste', '>', 0)
            ->whereNull('ventes.deleted_at')
            ->select(
                'clients.id',
                'clients.nom',
                DB::raw('SUM(ventes.reste) as montant'),
                DB::raw('DATEDIFF(CURDATE(), MIN(ventes.dateEcheance)) as jours_diff')
            )
            ->groupBy('clients.id', 'clients.nom')
            ->orderByDesc('montant')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $diff = (int) ($row->jours_diff ?? 0);
                $row->montant = (float) $row->montant;
                $row->joursRetard = $diff > 0 ? $diff : 0;
                $row->joursRestants = $diff <= 0 ? abs($diff) : 0;
                unset($row->jours_diff);
                return $row;
            });
    }

    // ─── Dettes Fournisseurs ─────────────────────────────────────

    private function getDettesFournisseurs()
    {
        return DB::table('commandes')
            ->join('fournisseurs', 'commandes.fournisseur_id', '=', 'fournisseurs.id')
            ->join(
                DB::raw('(SELECT commande_id, SUM(prix_achat * qte_achete) as total_ht FROM commande_produit GROUP BY commande_id) as totals'),
                'totals.commande_id', '=', 'commandes.id'
            )
            ->whereRaw('totals.total_ht > commandes.montantPaye')
            ->whereNull('commandes.deleted_at')
            ->select(
                'fournisseurs.id',
                'fournisseurs.societe as nom',
                DB::raw('SUM(totals.total_ht - commandes.montantPaye) as montant'),
                DB::raw('DATEDIFF(MIN(commandes.dateEcheance), CURDATE()) as joursRestants')
            )
            ->groupBy('fournisseurs.id', 'fournisseurs.societe')
            ->orderByDesc('montant')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $row->montant = (float) $row->montant;
                $row->joursRestants = (int) ($row->joursRestants ?? 0);
                return $row;
            });
    }

    // ─── Alertes Stock ───────────────────────────────────────────

    private function getAlertes(): array
    {
        $alertes = [];

        // Rupture de stock (qte = 0 and has limit)
        $rupture = Produit::whereNotNull('limit_command')
            ->where('limit_command', '>', 0)
            ->whereRaw('(SELECT COALESCE(SUM(qte), 0) FROM commande_produit WHERE commande_produit.produit_id = produits.id) = 0')
            ->limit(5)->pluck('label');
        foreach ($rupture as $i => $label) {
            $alertes[] = ['id' => "r-$i", 'type' => 'rupture', 'titre' => 'Rupture de stock', 'description' => $label];
        }

        // Stock critique (0 < qte < limit)
        $critique = Produit::whereNotNull('limit_command')
            ->where('limit_command', '>', 0)
            ->whereRaw('(SELECT COALESCE(SUM(qte), 0) FROM commande_produit WHERE commande_produit.produit_id = produits.id) > 0')
            ->whereRaw('(SELECT COALESCE(SUM(qte), 0) FROM commande_produit WHERE commande_produit.produit_id = produits.id) < produits.limit_command')
            ->limit(5)->get();
        foreach ($critique as $i => $p) {
            $alertes[] = ['id' => "c-$i", 'type' => 'critique', 'titre' => 'Stock critique', 'description' => $p->label . ' - Reste: ' . $p->qte . ' unités'];
        }

        // Lots périmés
        $perimesCount = CommandeProduit::expired()->where('qte', '>', 0)->count();
        $perimesValue = (float) CommandeProduit::expired()->where('qte', '>', 0)
            ->selectRaw('COALESCE(SUM(qte * prix_achat), 0) as v')->value('v');
        if ($perimesCount > 0) {
            $alertes[] = [
                'id' => 'p-1', 'type' => 'perime', 'titre' => 'Produits périmés',
                'description' => $perimesCount . ' lots expirés - Valeur: ' . number_format($perimesValue, 0, ',', ' ') . ' MAD',
            ];
        }

        // Lots expirant bientôt (< 30 jours)
        $expireSoonCount = CommandeProduit::whereNotNull('expirationDate')
            ->where('expirationDate', '>=', now())
            ->where('expirationDate', '<=', now()->addDays(30))
            ->where('qte', '>', 0)->count();
        $expireSoonValue = (float) CommandeProduit::whereNotNull('expirationDate')
            ->where('expirationDate', '>=', now())
            ->where('expirationDate', '<=', now()->addDays(30))
            ->where('qte', '>', 0)
            ->selectRaw('COALESCE(SUM(qte * prix_achat), 0) as v')->value('v');
        if ($expireSoonCount > 0) {
            $alertes[] = [
                'id' => 'e-1', 'type' => 'expire', 'titre' => 'Expire bientôt (< 30j)',
                'description' => $expireSoonCount . ' lots - Valeur: ' . number_format($expireSoonValue, 0, ',', ' ') . ' MAD',
            ];
        }

        return $alertes;
    }

    // ─── Activités Récentes ──────────────────────────────────────

    private function getActivites(): array
    {
        $activites = collect();

        // Recent ventes
        $recentVentes = Vente::with('client')->orderByDesc('created_at')->limit(4)->get();
        foreach ($recentVentes as $v) {
            $total = ($v->subtotal ?? 0) - ($v->remise_amount ?? 0);
            $activites->push([
                'type' => 'vente',
                'titre' => 'Nouvelle vente',
                'description' => 'Facture #' . $v->n_facture . ' - ' . number_format($total, 2, ',', ' ') . ' MAD',
                'date' => $v->created_at->diffForHumans(),
                'sort' => $v->created_at,
            ]);
        }

        // Recent commandes
        $recentCommandes = Commande::with('fournisseur')->orderByDesc('created_at')->limit(4)->get();
        foreach ($recentCommandes as $c) {
            $activites->push([
                'type' => 'reception',
                'titre' => 'Réception commande',
                'description' => ($c->fournisseur->societe ?? 'Fournisseur') . ' - Bon ' . $c->n_bon,
                'date' => $c->created_at->diffForHumans(),
                'sort' => $c->created_at,
            ]);
        }

        return $activites->sortByDesc('sort')->take(6)->values()->map(function ($a) {
            unset($a['sort']);
            return $a;
        })->toArray();
    }

    // ─── Charts: Sales Evolution ─────────────────────────────────

    private function getSalesChartData(): array
    {
        $caExpr = 'COALESCE(SUM(COALESCE(ventes.subtotal, 0) - COALESCE(ventes.remise_amount, 0)), 0)';

        // — Daily: last 7 days —
        $dailyRaw = Vente::where('date', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw("DATE(ventes.date) as d, $caExpr as ca")
            ->groupByRaw('DATE(ventes.date)')
            ->get()->keyBy('d');

        $dailyBenefRaw = DB::table('vente_produit as vp')
            ->join('ventes', 'ventes.id', '=', 'vp.vente_id')
            ->whereNull('ventes.deleted_at')
            ->where('ventes.date', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(ventes.date) as d, COALESCE(SUM((vp.prix - vp.prix_achat) * vp.qte), 0) as benefice')
            ->groupByRaw('DATE(ventes.date)')
            ->get()->keyBy('d');

        $daily = ['categories' => [], 'ca' => [], 'benefice' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $daily['categories'][] = self::DAY_NAMES[$date->dayOfWeek];
            $daily['ca'][] = (float) ($dailyRaw[$key]->ca ?? 0);
            $daily['benefice'][] = (float) ($dailyBenefRaw[$key]->benefice ?? 0);
        }

        // — Weekly: last 8 weeks —
        $weeklyRaw = Vente::where('date', '>=', now()->subWeeks(7)->startOfWeek())
            ->selectRaw("YEARWEEK(ventes.date, 1) as yw, $caExpr as ca")
            ->groupByRaw('YEARWEEK(ventes.date, 1)')
            ->get()->keyBy('yw');

        $weeklyBenefRaw = DB::table('vente_produit as vp')
            ->join('ventes', 'ventes.id', '=', 'vp.vente_id')
            ->whereNull('ventes.deleted_at')
            ->where('ventes.date', '>=', now()->subWeeks(7)->startOfWeek())
            ->selectRaw('YEARWEEK(ventes.date, 1) as yw, COALESCE(SUM((vp.prix - vp.prix_achat) * vp.qte), 0) as benefice')
            ->groupByRaw('YEARWEEK(ventes.date, 1)')
            ->get()->keyBy('yw');

        $weekly = ['categories' => [], 'ca' => [], 'benefice' => []];
        for ($i = 7; $i >= 0; $i--) {
            $week = now()->subWeeks($i);
            $ywMysql = $week->year . str_pad($week->isoWeek(), 2, '0', STR_PAD_LEFT);
            $weekly['categories'][] = 'S' . $week->isoWeek();
            $weekly['ca'][] = (float) ($weeklyRaw[$ywMysql]->ca ?? 0);
            $weekly['benefice'][] = (float) ($weeklyBenefRaw[$ywMysql]->benefice ?? 0);
        }

        // — Monthly: last 12 months —
        $monthlyRaw = Vente::where('date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(ventes.date, '%Y-%m') as ym, $caExpr as ca")
            ->groupByRaw("DATE_FORMAT(ventes.date, '%Y-%m')")
            ->get()->keyBy('ym');

        $monthlyBenefRaw = DB::table('vente_produit as vp')
            ->join('ventes', 'ventes.id', '=', 'vp.vente_id')
            ->whereNull('ventes.deleted_at')
            ->where('ventes.date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(ventes.date, '%Y-%m') as ym, COALESCE(SUM((vp.prix - vp.prix_achat) * vp.qte), 0) as benefice")
            ->groupByRaw("DATE_FORMAT(ventes.date, '%Y-%m')")
            ->get()->keyBy('ym');

        $monthly = ['categories' => [], 'ca' => [], 'benefice' => []];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $monthly['categories'][] = self::MONTH_NAMES[$month->month - 1];
            $monthly['ca'][] = (float) ($monthlyRaw[$key]->ca ?? 0);
            $monthly['benefice'][] = (float) ($monthlyBenefRaw[$key]->benefice ?? 0);
        }

        return compact('daily', 'weekly', 'monthly');
    }

    // ─── Charts: Sales by Category ───────────────────────────────

    private function getCategoryChartData($start, $end)
    {
        $colors = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#feca57', '#ff6b6b', '#48dbfb', '#ff9ff3'];

        return DB::table('vente_produit')
            ->join('ventes', 'vente_produit.vente_id', '=', 'ventes.id')
            ->join('produits', 'vente_produit.produit_id', '=', 'produits.id')
            ->leftJoin('categories', 'produits.categorie_id', '=', 'categories.id')
            ->whereBetween('ventes.date', [$start, $end])
            ->whereNull('ventes.deleted_at')
            ->select(
                DB::raw("COALESCE(categories.label, 'Sans catégorie') as name"),
                DB::raw('COALESCE(SUM(vente_produit.prix * vente_produit.qte), 0) as y')
            )
            ->groupBy('categories.label')
            ->orderByDesc('y')
            ->get()
            ->values()
            ->map(function ($row, $i) use ($colors) {
                $row->y = (float) $row->y;
                $row->color = $colors[$i % count($colors)];
                return $row;
            });
    }

    // ─── Charts: Cash Flow ───────────────────────────────────────

    private function getCashFlowChartData(): array
    {
        // Encaissements (ventes.montantPaye par mois)
        $encRaw = Vente::where('date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, COALESCE(SUM(montantPaye), 0) as total")
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->get()->keyBy('ym');

        // Décaissements (commandes.montantPaye par mois)
        $decRaw = Commande::where('date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as ym, COALESCE(SUM(montantPaye), 0) as total")
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->get()->keyBy('ym');

        $categories = [];
        $encaissements = [];
        $decaissements = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $categories[] = self::MONTH_NAMES[$month->month - 1];
            $encaissements[] = (float) ($encRaw[$key]->total ?? 0);
            $decaissements[] = (float) ($decRaw[$key]->total ?? 0);
        }

        return compact('categories', 'encaissements', 'decaissements');
    }

    // ─── Date Range Helpers ──────────────────────────────────────

    private function getDateRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function getPreviousDateRange(string $period): array
    {
        return match ($period) {
            'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        };
    }

    // ─── Notifications (existing) ────────────────────────────────

    public function notifications()
    {
        return Inertia::render('Dashboard/Notifications', ['items' => Auth::user()->notifications]);
    }

    public function notificationDelete($notification_id)
    {
        Auth::user()->notifications()->where('id', $notification_id)->delete();
        return redirect()->route('notifications.index')->banner('Notification supprimée.');
    }

    public function deleteAllNotifications()
    {
        Auth::user()->notifications()->delete();
        return redirect()->route('notifications.index');
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }
}
