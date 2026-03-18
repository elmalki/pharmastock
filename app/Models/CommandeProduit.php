<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

class CommandeProduit extends Pivot
{
    protected $table = 'commande_produit';
    protected $fillable = [
        'produit_id',
        'commande_id',
        'n_lot',
        'tva',
        'qte',
        'qte_achete',
        'prix_achat',
        'prix_vente',
        'expirationDate',
    ];

    /**
     * Attributs castés
     */
    protected $casts = [
        'prix_achat' => 'decimal:2',
        'prix_vente' => 'decimal:2',
        'tva' => 'decimal:2',
        'qte' => 'integer',
        'qte_achete' => 'integer',
        'expirationDate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }
    /**
     * ========================================
     * MÉTHODES PERSONNALISÉES INCREMENT/DECREMENT
     * ========================================
     */

    /**
     * Incrémenter la quantité du lot
     *
     * @param int $value Valeur à ajouter (par défaut 1)
     * @param bool $allowNegative Autoriser les quantités négatives (par défaut false)
     * @param bool $logHistory Enregistrer l'historique du mouvement (par défaut true)
     * @return bool Succès de l'opération
     * @throws \Exception Si la quantité devient négative et allowNegative = false
     */
    public function incrementQuantite(int $value = 1, bool $allowNegative = false, bool $logHistory = true): bool
    {
        // Validation
        if ($value < 0) {
            throw new \InvalidArgumentException("La valeur d'incrémentation doit être positive. Utilisez decrementQuantite() pour diminuer.");
        }

        // Transaction pour garantir l'intégrité
        DB::beginTransaction();

        try {
            // Verrouiller la ligne pour éviter les race conditions
            $lot = self::lockForUpdate()->findOrFail($this->id);

            // Ancienne quantité (pour l'historique)
            $oldQte = $lot->qte;

            // Nouvelle quantité
            $newQte = $oldQte + $value;

            // Vérifier si on autorise les quantités négatives
            if (!$allowNegative && $newQte < 0) {
                throw new \Exception("Opération refusée : la quantité ne peut pas être négative (actuelle: {$oldQte}, tentative d'ajout: {$value})");
            }

            // Mettre à jour la quantité
            $lot->qte = $newQte;
            $lot->save();

            // Enregistrer l'historique du mouvement
            if ($logHistory) {
                $this->logMouvement('retour', $value, $oldQte, $newQte, 'Réapprovisionnement/Retour');
            }

            DB::commit();

            // Rafraîchir le modèle actuel
            $this->refresh();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Décrémenter la quantité du lot
     *
     * @param int $value Valeur à retirer (par défaut 1)
     * @param bool $allowNegative Autoriser les quantités négatives (par défaut false)
     * @param bool $checkStock Vérifier le stock avant de décrémenter (par défaut true)
     * @param bool $logHistory Enregistrer l'historique du mouvement (par défaut true)
     * @return bool Succès de l'opération
     * @throws \Exception Si stock insuffisant
     */
    public function decrementQuantite(int $value = 1, bool $allowNegative = false, bool $checkStock = true, bool $logHistory = true): bool
    {
        // Validation
        if ($value < 0) {
            throw new \InvalidArgumentException("La valeur de décrémentation doit être positive. Utilisez incrementQuantite() pour augmenter.");
        }

        // Transaction pour garantir l'intégrité
        DB::beginTransaction();

        try {
            // Verrouiller la ligne pour éviter les race conditions
            $lot = self::lockForUpdate()->findOrFail($this->id);

            // Ancienne quantité (pour l'historique)
            $oldQte = $lot->qte;

            // Vérifier le stock disponible
            if ($checkStock && $value > $oldQte) {
                throw new \Exception("Stock insuffisant pour le lot {$lot->n_lot}. Disponible: {$oldQte}, Demandé: {$value}");
            }

            // Nouvelle quantité
            $newQte = $oldQte - $value;

            // Vérifier si on autorise les quantités négatives
            if (!$allowNegative && $newQte < 0) {
                throw new \Exception("Opération refusée : la quantité ne peut pas être négative (actuelle: {$oldQte}, tentative de retrait: {$value})");
            }

            // Mettre à jour la quantité
            $lot->qte = $newQte;
            $lot->save();

            // Enregistrer l'historique du mouvement
            if ($logHistory) {
                $this->logMouvement('vente', $value, $oldQte, $newQte, 'Vente/Sortie');
            }

            DB::commit();

            // Rafraîchir le modèle actuel
            $this->refresh();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Méthode raccourcie pour incrémenter (compatible avec Eloquent standard)
     *
     * @param string $column
     * @param int $amount
     * @param array $extra
     * @return bool
     */
    public function increment($column = 'qte', $amount = 1, array $extra = [])
    {
        // Si c'est la colonne qte, utiliser notre méthode personnalisée
        if ($column === 'qte') {
            return $this->incrementQuantite($amount);
        }

        // Sinon, utiliser la méthode Eloquent standard
        return parent::increment($column, $amount);
    }

    /**
     * Méthode raccourcie pour décrémenter (compatible avec Eloquent standard)
     *
     * @param string $column
     * @param int $amount
     * @param array $extra
     * @return bool
     */
    public function decrement($column = 'qte', $amount = 1, array $extra = [])
    {
        // Si c'est la colonne qte, utiliser notre méthode personnalisée
        if ($column === 'qte') {
            return $this->decrementQuantite($amount);
        }

        // Sinon, utiliser la méthode Eloquent standard
        return parent::decrement($column, $amount);
    }

    /**
     * Réserver une quantité (diminuer sans sortir du stock)
     *
     * @param int $quantite Quantité à réserver
     * @return bool
     */
    public function reserver(int $quantite): bool
    {
        DB::beginTransaction();

        try {
            $lot = self::lockForUpdate()->findOrFail($this->id);

            if ($quantite > $lot->qte) {
                throw new \Exception("Impossible de réserver {$quantite} unités. Stock disponible: {$lot->qte}");
            }

            // Créer ou mettre à jour la réservation
            // (Vous pouvez créer une table 'reservations' pour gérer ça)

            $this->logMouvement('ajustement', $quantite, $lot->qte, $lot->qte, 'Réservation');

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ajuster le stock (correction manuelle)
     *
     * @param int $nouvelleQuantite Nouvelle quantité exacte
     * @param string $raison Raison de l'ajustement
     * @return bool
     */
    public function ajusterStock(int $nouvelleQuantite, string $raison = 'Ajustement manuel'): bool
    {
        DB::beginTransaction();

        try {
            $lot = self::lockForUpdate()->findOrFail($this->id);
            $oldQte = $lot->qte;
            $difference = $nouvelleQuantite - $oldQte;

            $lot->qte = $nouvelleQuantite;
            $lot->save();

            // Enregistrer l'ajustement
            $this->logMouvement('ajustement', abs($difference), $oldQte, $nouvelleQuantite, $raison);

            DB::commit();
            $this->refresh();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Enregistrer l'historique des mouvements de stock
     *
     * @param string $type Type de mouvement (increment, decrement, ajustement, etc.)
     * @param int $quantite Quantité du mouvement
     * @param int $stockAvant Stock avant le mouvement
     * @param int $stockApres Stock après le mouvement
     * @param string $raison Raison du mouvement
     * @return void
     */
    protected function logMouvement(string $type, int $quantite, int $stockAvant, int $stockApres, string $raison = null, ?string $referenceType = null, ?int $referenceId = null): void
    {
        MouvementStock::create([
            'lot_id' => $this->id,
            'produit_id' => $this->produit_id,
            'type' => $type,
            'quantite' => $quantite,
            'stock_avant' => $stockAvant,
            'stock_apres' => $stockApres,
            'raison' => $raison,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * ========================================
     * MÉTHODES UTILITAIRES
     * ========================================
     */

    /**
     * Vérifier si le lot est périmé
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->expirationDate) {
            return false;
        }

        return Carbon::parse($this->expirationDate)->isPast();
    }

    /**
     * Obtenir le nombre de jours avant expiration
     *
     * @return int|null Nombre de jours (null si pas de date d'expiration)
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->expirationDate) {
            return null;
        }

        $expiration = Carbon::parse($this->expirationDate);
        return now()->diffInDays($expiration, false);
    }

    /**
     * Vérifier si le lot expire bientôt
     *
     * @param int $days Nombre de jours (par défaut 30)
     * @return bool
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expirationDate) {
            return false;
        }

        $daysUntil = $this->daysUntilExpiration();
        return $daysUntil !== null && $daysUntil >= 0 && $daysUntil <= $days;
    }

    /**
     * Vérifier si le stock est bas
     *
     * @param int $seuil Seuil de stock minimum (par défaut 10)
     * @return bool
     */
    public function isLowStock(int $seuil = 10): bool
    {
        return $this->qte > 0 && $this->qte <= $seuil;
    }

    /**
     * Vérifier si le lot est en rupture de stock
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->qte <= 0;
    }

    /**
     * Calculer la valeur totale du stock (quantité × prix d'achat)
     *
     * @return float
     */
    public function getStockValue(): float
    {
        return $this->qte * $this->prix_achat;
    }

    /**
     * Calculer le bénéfice potentiel du stock restant
     *
     * @return float
     */
    public function getPotentialProfit(): float
    {
        return $this->qte * ($this->prix_vente - $this->prix_achat);
    }

    /**
     * Calculer la marge bénéficiaire en pourcentage
     *
     * @return float
     */
    public function getProfitMargin(): float
    {
        if ($this->prix_achat == 0) {
            return 0;
        }

        return (($this->prix_vente - $this->prix_achat) / $this->prix_achat) * 100;
    }

    /**
     * ========================================
     * SCOPES (Requêtes réutilisables)
     * ========================================
     */

    /**
     * Scope : Lots en stock
     */
    public function scopeEnStock($query)
    {
        return $query->where('qte', '>', 0);
    }

    /**
     * Scope : Lots en rupture de stock
     */
    public function scopeRupture($query)
    {
        return $query->where('qte', '<=', 0);
    }

    /**
     * Scope : Lots périmés
     */
    public function scopeExpired($query)
    {
        return $query->where('expirationDate', '<', now());
    }

    /**
     * Scope : Lots expirant bientôt
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expirationDate', [
            now(),
            now()->addDays($days)
        ]);
    }

    /**
     * Scope : Lots d'un produit spécifique
     */
    public function scopeOfProduct($query, int $productId)
    {
        return $query->where('produit_id', $productId);
    }

    /**
     * Scope : FEFO (First Expired First Out) - Trier par date d'expiration
     */
    public function scopeFEFO($query)
    {
        return $query->orderBy('expirationDate', 'asc');
    }

    /**
     * Scope : Stock bas
     */
    public function scopeLowStock($query, int $seuil = 10)
    {
        return $query->where('qte', '>', 0)
            ->where('qte', '<=', $seuil);
    }

}
