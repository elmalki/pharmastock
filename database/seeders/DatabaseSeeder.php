<?php

namespace Database\Seeders;

use App\Enums\SituationCommande;
use App\Enums\StatutVente;
use App\Models\Categorie;
use App\Models\Client;
use App\Models\CommandeProduit;
use App\Models\Destockage;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Setting;
use App\Models\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users & Roles ──────────────────────────────────────
        $admin = User::create([
            'name' => 'Yassine EL MALKI',
            'email' => 'yassine.elmalki@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ]);

        $pharmacien = User::create([
            'name' => 'Amina BENALI',
            'email' => 'amina.benali@pharma.ma',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        $vendeur = User::create([
            'name' => 'Karim TAZI',
            'email' => 'karim.tazi@pharma.ma',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        $adminRole = Role::create(['name' => 'Administrateur', 'guard_name' => 'web']);
        $pharmacienRole = Role::create(['name' => 'Pharmacien', 'guard_name' => 'web']);
        $vendeurRole = Role::create(['name' => 'Vendeur', 'guard_name' => 'web']);

        $adminRole->users()->attach($admin->id);
        $pharmacienRole->users()->attach($pharmacien->id);
        $vendeurRole->users()->attach($vendeur->id);

        // ── Permissions ────────────────────────────────────────
        $entities = ['Produit', 'Destockage', 'Catégorie', 'Fournisseur', 'Client', 'Approvisionnement', 'Vente'];
        $actions = ['Ajouter', 'Modifier', 'Supprimer', 'Lister'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::create(['name' => "$action $entity", 'guard_name' => 'sanctum']);
            }
        }

        // ── Settings ───────────────────────────────────────────
        Setting::create(['destockage_number' => 5, 'year' => date('Y')]);

        // ── Categories ─────────────────────────────────────────
        $categories = collect([
            'Médicaments génériques',
            'Médicaments princeps',
            'Antibiotiques',
            'Anti-inflammatoires',
            'Antalgiques',
            'Vitamines & Compléments',
            'Dermatologie',
            'Parapharmacie',
            'Matériel médical',
            'Hygiène & Soins',
        ])->map(fn($label) => Categorie::create(['label' => $label]));

        // ── Fournisseurs ───────────────────────────────────────
        $fournisseurs = collect([
            ['societe' => 'Sanofi Maroc', 'contact' => 'Mohammed Alaoui', 'adresse' => '15 Bd Zerktouni, Casablanca', 'email' => 'contact@sanofi.ma'],
            ['societe' => 'Pharma 5', 'contact' => 'Fatima Zahra Bennani', 'adresse' => 'Zone Industrielle, Berrechid', 'email' => 'commandes@pharma5.ma'],
            ['societe' => 'Cooper Pharma', 'contact' => 'Hassan El Idrissi', 'adresse' => 'Route de Rabat, Casablanca', 'email' => 'ventes@cooperpharma.ma'],
            ['societe' => 'Maphar (Sothema)', 'contact' => 'Ahmed Tazi', 'adresse' => 'Bouskoura, Casablanca', 'email' => 'info@sothema.ma'],
            ['societe' => 'Galenica', 'contact' => 'Nadia Fassi Fihri', 'adresse' => 'Ain Sebaa, Casablanca', 'email' => 'distribution@galenica.ma'],
            ['societe' => 'Laprophan', 'contact' => 'Rachid Berrada', 'adresse' => 'Zone Industrielle, Casablanca', 'email' => 'commandes@laprophan.ma'],
            ['societe' => 'Pfizer Maroc', 'contact' => 'Sara Cherkaoui', 'adresse' => 'Twin Center, Casablanca', 'email' => 'orders@pfizer.ma'],
            ['societe' => 'Novartis Maroc', 'contact' => 'Karim Benjelloun', 'adresse' => 'Bd Al Massira, Rabat', 'email' => 'supply@novartis.ma'],
        ])->map(fn($f) => Fournisseur::create($f));

        // ── Clients ────────────────────────────────────────────
        $clients = collect([
            ['nom' => 'Client comptoir', 'tel' => ''],
            ['nom' => 'Pharmacie Al Madina', 'tel' => '0522334455'],
            ['nom' => 'Clinique Atlas', 'tel' => '0522667788'],
            ['nom' => 'Hôpital Moulay Youssef', 'tel' => '0537223344'],
            ['nom' => 'Cabinet Dr. Alami', 'tel' => '0661223344'],
            ['nom' => 'Centre de Santé Hay Nahda', 'tel' => '0522889900'],
            ['nom' => 'Pharmacie Ibn Sina', 'tel' => '0522112233'],
            ['nom' => 'Dr. Benmoussa', 'tel' => '0661998877'],
            ['nom' => 'Mme. Hakimi', 'tel' => '0667554433'],
            ['nom' => 'Mr. Rachidi', 'tel' => '0655443322'],
            ['nom' => 'Clinique Al Hayat', 'tel' => '0522556677'],
            ['nom' => 'Mr. Bouazza', 'tel' => '0612345678'],
        ])->map(fn($c) => Client::create($c));

        // ── Produits ───────────────────────────────────────────
        $produitsData = [
            // Antalgiques (cat 5)
            ['barcode' => '6111000001', 'label' => 'Doliprane 1000mg', 'dci' => 'Paracétamol', 'forme' => 'Comprimé', 'dosage' => '1000mg', 'laboratoire' => 'Sanofi', 'unite' => 8, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 25.00, 'limit_command' => 20, 'categorie_id' => 5],
            ['barcode' => '6111000002', 'label' => 'Doliprane 500mg', 'dci' => 'Paracétamol', 'forme' => 'Comprimé', 'dosage' => '500mg', 'laboratoire' => 'Sanofi', 'unite' => 16, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 18.50, 'limit_command' => 25, 'categorie_id' => 5],
            ['barcode' => '6111000003', 'label' => 'Efferalgan 1g', 'dci' => 'Paracétamol', 'forme' => 'Comprimé effervescent', 'dosage' => '1g', 'laboratoire' => 'UPSA', 'unite' => 8, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 32.00, 'limit_command' => 15, 'categorie_id' => 5],
            ['barcode' => '6111000004', 'label' => 'Aspégic 1000mg', 'dci' => 'Acide acétylsalicylique', 'forme' => 'Sachet', 'dosage' => '1000mg', 'laboratoire' => 'Sanofi', 'unite' => 20, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 28.00, 'limit_command' => 10, 'categorie_id' => 5],
            // Anti-inflammatoires (cat 4)
            ['barcode' => '6111000005', 'label' => 'Voltarène 50mg', 'dci' => 'Diclofénac', 'forme' => 'Comprimé', 'dosage' => '50mg', 'laboratoire' => 'Novartis', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 45.00, 'limit_command' => 10, 'categorie_id' => 4],
            ['barcode' => '6111000006', 'label' => 'Ibuprofène 400mg', 'dci' => 'Ibuprofène', 'forme' => 'Comprimé', 'dosage' => '400mg', 'laboratoire' => 'Cooper', 'unite' => 20, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 22.00, 'limit_command' => 15, 'categorie_id' => 4],
            ['barcode' => '6111000007', 'label' => 'Kétoprofène 100mg', 'dci' => 'Kétoprofène', 'forme' => 'Gélule', 'dosage' => '100mg', 'laboratoire' => 'Sanofi', 'unite' => 14, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 55.00, 'limit_command' => 8, 'categorie_id' => 4],
            // Antibiotiques (cat 3)
            ['barcode' => '6111000008', 'label' => 'Amoxicilline 1g', 'dci' => 'Amoxicilline', 'forme' => 'Comprimé', 'dosage' => '1g', 'laboratoire' => 'Pharma 5', 'unite' => 12, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 38.50, 'limit_command' => 15, 'categorie_id' => 3],
            ['barcode' => '6111000009', 'label' => 'Augmentin 1g', 'dci' => 'Amoxicilline/Ac. clavulanique', 'forme' => 'Comprimé', 'dosage' => '1g/125mg', 'laboratoire' => 'GSK', 'unite' => 12, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 85.00, 'limit_command' => 10, 'categorie_id' => 3],
            ['barcode' => '6111000010', 'label' => 'Azithromycine 500mg', 'dci' => 'Azithromycine', 'forme' => 'Comprimé', 'dosage' => '500mg', 'laboratoire' => 'Pfizer', 'unite' => 3, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 65.00, 'limit_command' => 10, 'categorie_id' => 3],
            ['barcode' => '6111000011', 'label' => 'Ciprofloxacine 500mg', 'dci' => 'Ciprofloxacine', 'forme' => 'Comprimé', 'dosage' => '500mg', 'laboratoire' => 'Cooper', 'unite' => 10, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 42.00, 'limit_command' => 8, 'categorie_id' => 3],
            ['barcode' => '6111000012', 'label' => 'Métronidazole 500mg', 'dci' => 'Métronidazole', 'forme' => 'Comprimé', 'dosage' => '500mg', 'laboratoire' => 'Laprophan', 'unite' => 20, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 30.00, 'limit_command' => 10, 'categorie_id' => 3],
            // Génériques (cat 1)
            ['barcode' => '6111000013', 'label' => 'Oméprazole 20mg', 'dci' => 'Oméprazole', 'forme' => 'Gélule', 'dosage' => '20mg', 'laboratoire' => 'Pharma 5', 'unite' => 14, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 35.00, 'limit_command' => 12, 'categorie_id' => 1],
            ['barcode' => '6111000014', 'label' => 'Amlodipine 5mg', 'dci' => 'Amlodipine', 'forme' => 'Comprimé', 'dosage' => '5mg', 'laboratoire' => 'Maphar', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 48.00, 'limit_command' => 10, 'categorie_id' => 1],
            ['barcode' => '6111000015', 'label' => 'Metformine 850mg', 'dci' => 'Metformine', 'forme' => 'Comprimé', 'dosage' => '850mg', 'laboratoire' => 'Cooper', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 28.00, 'limit_command' => 15, 'categorie_id' => 1],
            ['barcode' => '6111000016', 'label' => 'Losartan 50mg', 'dci' => 'Losartan', 'forme' => 'Comprimé', 'dosage' => '50mg', 'laboratoire' => 'Galenica', 'unite' => 28, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 62.00, 'limit_command' => 8, 'categorie_id' => 1],
            ['barcode' => '6111000017', 'label' => 'Atorvastatine 20mg', 'dci' => 'Atorvastatine', 'forme' => 'Comprimé', 'dosage' => '20mg', 'laboratoire' => 'Pfizer', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 75.00, 'limit_command' => 8, 'categorie_id' => 1],
            // Princeps (cat 2)
            ['barcode' => '6111000018', 'label' => 'Crestor 10mg', 'dci' => 'Rosuvastatine', 'forme' => 'Comprimé', 'dosage' => '10mg', 'laboratoire' => 'AstraZeneca', 'unite' => 28, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 195.00, 'limit_command' => 5, 'categorie_id' => 2],
            ['barcode' => '6111000019', 'label' => 'Inexium 40mg', 'dci' => 'Ésoméprazole', 'forme' => 'Comprimé', 'dosage' => '40mg', 'laboratoire' => 'AstraZeneca', 'unite' => 28, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 145.00, 'limit_command' => 5, 'categorie_id' => 2],
            ['barcode' => '6111000020', 'label' => 'Coversyl 5mg', 'dci' => 'Périndopril', 'forme' => 'Comprimé', 'dosage' => '5mg', 'laboratoire' => 'Servier', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 110.00, 'limit_command' => 5, 'categorie_id' => 2],
            // Vitamines (cat 6)
            ['barcode' => '6111000021', 'label' => 'Vitamine C 1000mg', 'dci' => 'Acide ascorbique', 'forme' => 'Comprimé effervescent', 'dosage' => '1000mg', 'laboratoire' => 'Bayer', 'unite' => 20, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 55.00, 'limit_command' => 15, 'categorie_id' => 6],
            ['barcode' => '6111000022', 'label' => 'Vitamine D3 200000 UI', 'dci' => 'Cholécalciférol', 'forme' => 'Ampoule buvable', 'dosage' => '200000 UI', 'laboratoire' => 'Cooper', 'unite' => 1, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 32.00, 'limit_command' => 20, 'categorie_id' => 6],
            ['barcode' => '6111000023', 'label' => 'Mag 2', 'dci' => 'Magnésium', 'forme' => 'Comprimé', 'dosage' => '122mg', 'laboratoire' => 'Sanofi', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 48.00, 'limit_command' => 10, 'categorie_id' => 6],
            ['barcode' => '6111000024', 'label' => 'Tardyféron B9', 'dci' => 'Fer + Acide folique', 'forme' => 'Comprimé', 'dosage' => '80mg', 'laboratoire' => 'Pierre Fabre', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 42.00, 'limit_command' => 10, 'categorie_id' => 6],
            // Dermatologie (cat 7)
            ['barcode' => '6111000025', 'label' => 'Fucidine 2%', 'dci' => 'Acide fusidique', 'forme' => 'Crème', 'dosage' => '2%', 'laboratoire' => 'LEO Pharma', 'unite' => 1, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 45.00, 'limit_command' => 10, 'categorie_id' => 7],
            ['barcode' => '6111000026', 'label' => 'Bétaméthasone crème', 'dci' => 'Bétaméthasone', 'forme' => 'Crème', 'dosage' => '0.05%', 'laboratoire' => 'Cooper', 'unite' => 1, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 25.00, 'limit_command' => 10, 'categorie_id' => 7],
            // Parapharmacie (cat 8)
            ['barcode' => '6111000027', 'label' => 'Gel hydroalcoolique 500ml', 'dci' => null, 'forme' => 'Gel', 'dosage' => '500ml', 'laboratoire' => 'Cooper', 'unite' => 1, 'perissable' => false, 'ordonnance_requise' => false, 'prix_public' => 35.00, 'limit_command' => 15, 'categorie_id' => 8],
            ['barcode' => '6111000028', 'label' => 'Sérum physiologique (boîte 30)', 'dci' => 'Chlorure de sodium', 'forme' => 'Unidose', 'dosage' => '0.9%', 'laboratoire' => 'Gilbert', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 28.00, 'limit_command' => 20, 'categorie_id' => 8],
            ['barcode' => '6111000029', 'label' => 'Crème solaire SPF50', 'dci' => null, 'forme' => 'Crème', 'dosage' => 'SPF50', 'laboratoire' => 'Avène', 'unite' => 1, 'perissable' => false, 'ordonnance_requise' => false, 'prix_public' => 180.00, 'limit_command' => 5, 'categorie_id' => 8],
            // Matériel médical (cat 9)
            ['barcode' => '6111000030', 'label' => 'Masques chirurgicaux (boîte 50)', 'dci' => null, 'forme' => 'Boîte', 'dosage' => null, 'laboratoire' => null, 'unite' => 50, 'perissable' => false, 'ordonnance_requise' => false, 'prix_public' => 40.00, 'limit_command' => 10, 'categorie_id' => 9],
            ['barcode' => '6111000031', 'label' => 'Thermomètre digital', 'dci' => null, 'forme' => 'Appareil', 'dosage' => null, 'laboratoire' => 'Omron', 'unite' => 1, 'perissable' => false, 'ordonnance_requise' => false, 'prix_public' => 75.00, 'limit_command' => 5, 'categorie_id' => 9],
            ['barcode' => '6111000032', 'label' => 'Tensiomètre électronique', 'dci' => null, 'forme' => 'Appareil', 'dosage' => null, 'laboratoire' => 'Omron', 'unite' => 1, 'perissable' => false, 'ordonnance_requise' => false, 'prix_public' => 350.00, 'limit_command' => 3, 'categorie_id' => 9],
            ['barcode' => '6111000033', 'label' => 'Glucomètre + bandelettes', 'dci' => null, 'forme' => 'Kit', 'dosage' => null, 'laboratoire' => 'Accu-Chek', 'unite' => 1, 'perissable' => false, 'ordonnance_requise' => false, 'prix_public' => 280.00, 'limit_command' => 3, 'categorie_id' => 9],
            // Hygiène (cat 10)
            ['barcode' => '6111000034', 'label' => 'Dentifrice Sensodyne', 'dci' => null, 'forme' => 'Tube', 'dosage' => '75ml', 'laboratoire' => 'GSK', 'unite' => 1, 'perissable' => false, 'ordonnance_requise' => false, 'prix_public' => 45.00, 'limit_command' => 10, 'categorie_id' => 10],
            ['barcode' => '6111000035', 'label' => 'Bain de bouche Eludril', 'dci' => 'Chlorhexidine', 'forme' => 'Solution', 'dosage' => '200ml', 'laboratoire' => 'Pierre Fabre', 'unite' => 1, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 55.00, 'limit_command' => 8, 'categorie_id' => 10],
            // More generics
            ['barcode' => '6111000036', 'label' => 'Insuline Lantus SoloStar', 'dci' => 'Insuline glargine', 'forme' => 'Stylo injecteur', 'dosage' => '100 UI/ml', 'laboratoire' => 'Sanofi', 'unite' => 5, 'perissable' => true, 'ordonnance_requise' => true, 'prix_public' => 320.00, 'limit_command' => 5, 'categorie_id' => 2],
            ['barcode' => '6111000037', 'label' => 'Ventoline 100µg', 'dci' => 'Salbutamol', 'forme' => 'Aérosol', 'dosage' => '100µg', 'laboratoire' => 'GSK', 'unite' => 1, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 28.00, 'limit_command' => 15, 'categorie_id' => 2],
            ['barcode' => '6111000038', 'label' => 'Smecta', 'dci' => 'Diosmectite', 'forme' => 'Sachet', 'dosage' => '3g', 'laboratoire' => 'Ipsen', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 38.00, 'limit_command' => 15, 'categorie_id' => 1],
            ['barcode' => '6111000039', 'label' => 'Spasfon', 'dci' => 'Phloroglucinol', 'forme' => 'Comprimé', 'dosage' => '80mg', 'laboratoire' => 'Teva', 'unite' => 30, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 32.00, 'limit_command' => 12, 'categorie_id' => 1],
            ['barcode' => '6111000040', 'label' => 'Gaviscon suspension', 'dci' => 'Alginate de sodium', 'forme' => 'Flacon', 'dosage' => '250ml', 'laboratoire' => 'Reckitt', 'unite' => 1, 'perissable' => true, 'ordonnance_requise' => false, 'prix_public' => 52.00, 'limit_command' => 10, 'categorie_id' => 1],
        ];

        $produits = collect();
        foreach ($produitsData as $p) {
            $produits->push(Produit::create(array_merge($p, ['generated' => true])));
        }

        // ── Commandes (Purchase Orders) with lots ──────────────
        $now = Carbon::now();

        // Use DB::table to bypass Commande model's ModePaiement enum cast
        // (DB enum values differ from PHP enum values)
        $createCommande = function (array $commandeData, array $lots) {
            $commandeId = DB::table('commandes')->insertGetId(array_merge($commandeData, ['created_at' => now(), 'updated_at' => now()]));
            foreach ($lots as $lot) {
                CommandeProduit::create(array_merge($lot, ['commande_id' => $commandeId]));
            }
            return $commandeId;
        };

        // DB enum values for commandes.paiement: 'Éspèce','Chèque','Effet','Virement','Virsement','En compte','Multi Réglement'
        // (different from ModePaiement PHP enum, which is used for ventes)

        // Commande 1 — Sanofi, 3 months ago
        $createCommande([
            'n_bon' => 'BC-2025-001',
            'n_facture' => 'FA-SAN-4521',
            'date' => $now->copy()->subMonths(3)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->subMonths(2)->format('Y-m-d'),
            'montantPaye' => 8500.00,
            'paiement' => 'Éspèce',
            'situation' => SituationCommande::PAYEE->value,
            'fournisseur_id' => $fournisseurs[0]->id,
        ], [
            ['produit_id' => $produits[0]->id, 'n_lot' => 'SAN-A001', 'tva' => 7, 'qte' => 100, 'qte_achete' => 100, 'prix_achat' => 15.00, 'prix_vente' => 25.00, 'expirationDate' => $now->copy()->addYear()->format('Y-m-d')],
            ['produit_id' => $produits[1]->id, 'n_lot' => 'SAN-A002', 'tva' => 7, 'qte' => 150, 'qte_achete' => 150, 'prix_achat' => 10.00, 'prix_vente' => 18.50, 'expirationDate' => $now->copy()->addMonths(18)->format('Y-m-d')],
            ['produit_id' => $produits[3]->id, 'n_lot' => 'SAN-A003', 'tva' => 7, 'qte' => 80, 'qte_achete' => 80, 'prix_achat' => 17.00, 'prix_vente' => 28.00, 'expirationDate' => $now->copy()->addMonths(10)->format('Y-m-d')],
            ['produit_id' => $produits[22]->id, 'n_lot' => 'SAN-A004', 'tva' => 7, 'qte' => 60, 'qte_achete' => 60, 'prix_achat' => 28.00, 'prix_vente' => 48.00, 'expirationDate' => $now->copy()->addMonths(14)->format('Y-m-d')],
        ]);

        // Commande 2 — Pharma 5, 2 months ago
        $createCommande([
            'n_bon' => 'BC-2025-002',
            'n_facture' => 'FA-PH5-7892',
            'date' => $now->copy()->subMonths(2)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->subMonth()->format('Y-m-d'),
            'montantPaye' => 12300.00,
            'paiement' => 'Chèque',
            'situation' => SituationCommande::PAYEE->value,
            'fournisseur_id' => $fournisseurs[1]->id,
        ], [
            ['produit_id' => $produits[7]->id, 'n_lot' => 'PH5-B010', 'tva' => 7, 'qte' => 200, 'qte_achete' => 200, 'prix_achat' => 22.00, 'prix_vente' => 38.50, 'expirationDate' => $now->copy()->addMonths(24)->format('Y-m-d')],
            ['produit_id' => $produits[12]->id, 'n_lot' => 'PH5-B011', 'tva' => 7, 'qte' => 120, 'qte_achete' => 120, 'prix_achat' => 20.00, 'prix_vente' => 35.00, 'expirationDate' => $now->copy()->addMonths(20)->format('Y-m-d')],
            ['produit_id' => $produits[37]->id, 'n_lot' => 'PH5-B012', 'tva' => 7, 'qte' => 80, 'qte_achete' => 80, 'prix_achat' => 22.00, 'prix_vente' => 38.00, 'expirationDate' => $now->copy()->addMonths(16)->format('Y-m-d')],
        ]);

        // Commande 3 — Cooper Pharma, 6 weeks ago
        $createCommande([
            'n_bon' => 'BC-2025-003',
            'n_facture' => 'FA-COP-3345',
            'date' => $now->copy()->subWeeks(6)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addWeeks(2)->format('Y-m-d'),
            'montantPaye' => 6800.00,
            'paiement' => 'Effet',
            'situation' => SituationCommande::EN_COMPTE->value,
            'fournisseur_id' => $fournisseurs[2]->id,
        ], [
            ['produit_id' => $produits[5]->id, 'n_lot' => 'COP-C020', 'tva' => 7, 'qte' => 90, 'qte_achete' => 90, 'prix_achat' => 12.00, 'prix_vente' => 22.00, 'expirationDate' => $now->copy()->addMonths(12)->format('Y-m-d')],
            ['produit_id' => $produits[10]->id, 'n_lot' => 'COP-C021', 'tva' => 7, 'qte' => 60, 'qte_achete' => 60, 'prix_achat' => 25.00, 'prix_vente' => 42.00, 'expirationDate' => $now->copy()->addMonths(15)->format('Y-m-d')],
            ['produit_id' => $produits[14]->id, 'n_lot' => 'COP-C022', 'tva' => 7, 'qte' => 100, 'qte_achete' => 100, 'prix_achat' => 16.00, 'prix_vente' => 28.00, 'expirationDate' => $now->copy()->addMonths(18)->format('Y-m-d')],
            ['produit_id' => $produits[21]->id, 'n_lot' => 'COP-C023', 'tva' => 0, 'qte' => 50, 'qte_achete' => 50, 'prix_achat' => 18.00, 'prix_vente' => 32.00, 'expirationDate' => $now->copy()->addMonths(8)->format('Y-m-d')],
        ]);

        // Commande 4 — Sothema, 1 month ago
        $createCommande([
            'n_bon' => 'BC-2025-004',
            'n_facture' => 'FA-SOT-1178',
            'date' => $now->copy()->subMonth()->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addMonth()->format('Y-m-d'),
            'montantPaye' => 15200.00,
            'paiement' => 'Virement',
            'situation' => SituationCommande::CREDIT->value,
            'fournisseur_id' => $fournisseurs[3]->id,
        ], [
            ['produit_id' => $produits[13]->id, 'n_lot' => 'SOT-D030', 'tva' => 7, 'qte' => 80, 'qte_achete' => 80, 'prix_achat' => 30.00, 'prix_vente' => 48.00, 'expirationDate' => $now->copy()->addMonths(20)->format('Y-m-d')],
            ['produit_id' => $produits[38]->id, 'n_lot' => 'SOT-D031', 'tva' => 7, 'qte' => 100, 'qte_achete' => 100, 'prix_achat' => 18.00, 'prix_vente' => 32.00, 'expirationDate' => $now->copy()->addMonths(15)->format('Y-m-d')],
        ]);

        // Commande 5 — Galenica, 2 weeks ago
        $createCommande([
            'n_bon' => 'BC-2026-005',
            'n_facture' => 'FA-GAL-5590',
            'date' => $now->copy()->subWeeks(2)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addMonths(2)->format('Y-m-d'),
            'montantPaye' => 9400.00,
            'paiement' => 'Chèque',
            'situation' => SituationCommande::EN_COMPTE->value,
            'fournisseur_id' => $fournisseurs[4]->id,
        ], [
            ['produit_id' => $produits[15]->id, 'n_lot' => 'GAL-E040', 'tva' => 7, 'qte' => 60, 'qte_achete' => 60, 'prix_achat' => 38.00, 'prix_vente' => 62.00, 'expirationDate' => $now->copy()->addMonths(22)->format('Y-m-d')],
            ['produit_id' => $produits[25]->id, 'n_lot' => 'GAL-E041', 'tva' => 7, 'qte' => 40, 'qte_achete' => 40, 'prix_achat' => 14.00, 'prix_vente' => 25.00, 'expirationDate' => $now->copy()->addMonths(18)->format('Y-m-d')],
        ]);

        // Commande 6 — Pfizer, 1 week ago
        $createCommande([
            'n_bon' => 'BC-2026-006',
            'n_facture' => 'FA-PFI-8834',
            'date' => $now->copy()->subWeek()->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addMonths(3)->format('Y-m-d'),
            'montantPaye' => 18500.00,
            'paiement' => 'En compte',
            'situation' => SituationCommande::CREDIT->value,
            'fournisseur_id' => $fournisseurs[6]->id,
        ], [
            ['produit_id' => $produits[9]->id, 'n_lot' => 'PFI-F050', 'tva' => 7, 'qte' => 80, 'qte_achete' => 80, 'prix_achat' => 40.00, 'prix_vente' => 65.00, 'expirationDate' => $now->copy()->addMonths(24)->format('Y-m-d')],
            ['produit_id' => $produits[16]->id, 'n_lot' => 'PFI-F051', 'tva' => 7, 'qte' => 50, 'qte_achete' => 50, 'prix_achat' => 48.00, 'prix_vente' => 75.00, 'expirationDate' => $now->copy()->addMonths(20)->format('Y-m-d')],
        ]);

        // Commande 7 — Novartis, 3 days ago
        $createCommande([
            'n_bon' => 'BC-2026-007',
            'n_facture' => 'FA-NOV-2267',
            'date' => $now->copy()->subDays(3)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addMonths(2)->format('Y-m-d'),
            'montantPaye' => 7200.00,
            'paiement' => 'Éspèce',
            'situation' => SituationCommande::PAYEE->value,
            'fournisseur_id' => $fournisseurs[7]->id,
        ], [
            ['produit_id' => $produits[4]->id, 'n_lot' => 'NOV-G060', 'tva' => 7, 'qte' => 70, 'qte_achete' => 70, 'prix_achat' => 28.00, 'prix_vente' => 45.00, 'expirationDate' => $now->copy()->addMonths(16)->format('Y-m-d')],
            ['produit_id' => $produits[17]->id, 'n_lot' => 'NOV-G061', 'tva' => 7, 'qte' => 30, 'qte_achete' => 30, 'prix_achat' => 120.00, 'prix_vente' => 195.00, 'expirationDate' => $now->copy()->addMonths(24)->format('Y-m-d')],
        ]);

        // Commande 8 — Non-perishables (masks, thermometers, parapharmacie)
        $createCommande([
            'n_bon' => 'BC-2026-008',
            'n_facture' => 'FA-COP-9901',
            'date' => $now->copy()->subWeeks(3)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addMonth()->format('Y-m-d'),
            'montantPaye' => 5600.00,
            'paiement' => 'Éspèce',
            'situation' => SituationCommande::PAYEE->value,
            'fournisseur_id' => $fournisseurs[2]->id,
        ], [
            ['produit_id' => $produits[26]->id, 'n_lot' => 'COP-H070', 'tva' => 20, 'qte' => 50, 'qte_achete' => 50, 'prix_achat' => 20.00, 'prix_vente' => 35.00, 'expirationDate' => null],
            ['produit_id' => $produits[29]->id, 'n_lot' => 'COP-H071', 'tva' => 20, 'qte' => 100, 'qte_achete' => 100, 'prix_achat' => 22.00, 'prix_vente' => 40.00, 'expirationDate' => null],
            ['produit_id' => $produits[30]->id, 'n_lot' => 'COP-H072', 'tva' => 20, 'qte' => 20, 'qte_achete' => 20, 'prix_achat' => 45.00, 'prix_vente' => 75.00, 'expirationDate' => null],
            ['produit_id' => $produits[33]->id, 'n_lot' => 'COP-H073', 'tva' => 20, 'qte' => 30, 'qte_achete' => 30, 'prix_achat' => 28.00, 'prix_vente' => 45.00, 'expirationDate' => null],
        ]);

        // Commande 9 — Expired lot (for testing expired product display)
        $createCommande([
            'n_bon' => 'BC-2024-099',
            'n_facture' => 'FA-LAP-1122',
            'date' => $now->copy()->subYear()->format('Y-m-d'),
            'dateEcheance' => $now->copy()->subMonths(10)->format('Y-m-d'),
            'montantPaye' => 2100.00,
            'paiement' => 'Éspèce',
            'situation' => SituationCommande::PAYEE->value,
            'fournisseur_id' => $fournisseurs[5]->id,
        ], [
            ['produit_id' => $produits[11]->id, 'n_lot' => 'LAP-X001', 'tva' => 7, 'qte' => 15, 'qte_achete' => 50, 'prix_achat' => 18.00, 'prix_vente' => 30.00, 'expirationDate' => $now->copy()->subDays(10)->format('Y-m-d')],
            ['produit_id' => $produits[2]->id, 'n_lot' => 'LAP-X002', 'tva' => 7, 'qte' => 8, 'qte_achete' => 30, 'prix_achat' => 19.00, 'prix_vente' => 32.00, 'expirationDate' => $now->copy()->subDays(5)->format('Y-m-d')],
        ]);

        // Commande 10 — Expiring soon lots
        $createCommande([
            'n_bon' => 'BC-2025-010',
            'n_facture' => 'FA-SAN-6677',
            'date' => $now->copy()->subMonths(8)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->subMonths(6)->format('Y-m-d'),
            'montantPaye' => 4500.00,
            'paiement' => 'Éspèce',
            'situation' => SituationCommande::PAYEE->value,
            'fournisseur_id' => $fournisseurs[0]->id,
        ], [
            ['produit_id' => $produits[6]->id, 'n_lot' => 'SAN-Y010', 'tva' => 7, 'qte' => 25, 'qte_achete' => 40, 'prix_achat' => 32.00, 'prix_vente' => 55.00, 'expirationDate' => $now->copy()->addDays(20)->format('Y-m-d')],
            ['produit_id' => $produits[20]->id, 'n_lot' => 'SAN-Y011', 'tva' => 0, 'qte' => 40, 'qte_achete' => 60, 'prix_achat' => 30.00, 'prix_vente' => 55.00, 'expirationDate' => $now->copy()->addDays(15)->format('Y-m-d')],
        ]);

        // ── Ventes (Sales) ─────────────────────────────────────
        $allLots = CommandeProduit::all();
        $getLot = fn(string $nLot) => $allLots->firstWhere('n_lot', $nLot);

        // Use DB::table to bypass Vente model's ModePaiement enum cast
        // (DB enum values differ from PHP enum values)
        $insertVente = fn(array $data) => DB::table('ventes')->insertGetId(array_merge($data, ['created_at' => now(), 'updated_at' => now()]));

        // Vente 1 — 2 weeks ago, cash
        $vente1Id = $insertVente([
            'n_facture' => 'VT-2026-001',
            'date' => $now->copy()->subWeeks(2)->format('Y-m-d'),
            'paiement' => 'Éspèce',
            'montantPaye' => 165.50,
            'subtotal' => 165.50,
            'remise' => 0,
            'remise_amount' => 0,
            'reste' => 0,
            'benefice' => 48.00,
            'statut' => StatutVente::PAYEE->value,
            'client_id' => $clients[0]->id,
            'user_id' => $admin->id,
        ]);
        $lot = $getLot('SAN-A001');
        DB::table('vente_produit')->insert([
            ['vente_id' => $vente1Id, 'produit_id' => $produits[0]->id, 'lot_id' => $lot->id, 'prix' => 25.00, 'prix_achat' => 15.00, 'qte' => 3, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('commande_produit')->where('id', $lot->id)->decrement('qte', 3);

        $lot2 = $getLot('PH5-B010');
        DB::table('vente_produit')->insert([
            ['vente_id' => $vente1Id, 'produit_id' => $produits[7]->id, 'lot_id' => $lot2->id, 'prix' => 38.50, 'prix_achat' => 22.00, 'qte' => 2, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('commande_produit')->where('id', $lot2->id)->decrement('qte', 2);

        // Vente 2 — 10 days ago, credit, client: Pharmacie Al Madina
        $vente2Id = $insertVente([
            'n_facture' => 'VT-2026-002',
            'date' => $now->copy()->subDays(10)->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addDays(20)->format('Y-m-d'),
            'paiement' => 'Crédit',
            'montantPaye' => 0,
            'subtotal' => 1250.00,
            'remise' => 5,
            'remise_amount' => 62.50,
            'reste' => 1187.50,
            'benefice' => 380.00,
            'statut' => StatutVente::PARTIEL->value,
            'client_id' => $clients[1]->id,
            'user_id' => $pharmacien->id,
        ]);
        $lotA = $getLot('SAN-A001');
        $lotB = $getLot('SAN-A002');
        $lotC = $getLot('COP-C020');
        DB::table('vente_produit')->insert([
            ['vente_id' => $vente2Id, 'produit_id' => $produits[0]->id, 'lot_id' => $lotA->id, 'prix' => 25.00, 'prix_achat' => 15.00, 'qte' => 20, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente2Id, 'produit_id' => $produits[1]->id, 'lot_id' => $lotB->id, 'prix' => 18.50, 'prix_achat' => 10.00, 'qte' => 15, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente2Id, 'produit_id' => $produits[5]->id, 'lot_id' => $lotC->id, 'prix' => 22.00, 'prix_achat' => 12.00, 'qte' => 10, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('commande_produit')->where('id', $lotA->id)->decrement('qte', 20);
        DB::table('commande_produit')->where('id', $lotB->id)->decrement('qte', 15);
        DB::table('commande_produit')->where('id', $lotC->id)->decrement('qte', 10);

        // Vente 3 — 5 days ago, cash
        $vente3Id = $insertVente([
            'n_facture' => 'VT-2026-003',
            'date' => $now->copy()->subDays(5)->format('Y-m-d'),
            'paiement' => 'Éspèce',
            'montantPaye' => 342.00,
            'subtotal' => 342.00,
            'remise' => 0,
            'remise_amount' => 0,
            'reste' => 0,
            'benefice' => 120.00,
            'statut' => StatutVente::PAYEE->value,
            'client_id' => $clients[4]->id,
            'user_id' => $vendeur->id,
        ]);
        $lotD = $getLot('PH5-B011');
        $lotE = $getLot('NOV-G060');
        DB::table('vente_produit')->insert([
            ['vente_id' => $vente3Id, 'produit_id' => $produits[12]->id, 'lot_id' => $lotD->id, 'prix' => 35.00, 'prix_achat' => 20.00, 'qte' => 4, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente3Id, 'produit_id' => $produits[4]->id, 'lot_id' => $lotE->id, 'prix' => 45.00, 'prix_achat' => 28.00, 'qte' => 2, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente3Id, 'produit_id' => $produits[0]->id, 'lot_id' => $lotA->id, 'prix' => 25.00, 'prix_achat' => 15.00, 'qte' => 4, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('commande_produit')->where('id', $lotD->id)->decrement('qte', 4);
        DB::table('commande_produit')->where('id', $lotE->id)->decrement('qte', 2);
        DB::table('commande_produit')->where('id', $lotA->id)->decrement('qte', 4);

        // Vente 4 — 3 days ago, TPE, Clinique Atlas
        $vente4Id = $insertVente([
            'n_facture' => 'VT-2026-004',
            'date' => $now->copy()->subDays(3)->format('Y-m-d'),
            'paiement' => 'TPE',
            'montantPaye' => 2850.00,
            'subtotal' => 3000.00,
            'remise' => 5,
            'remise_amount' => 150.00,
            'reste' => 0,
            'benefice' => 950.00,
            'statut' => StatutVente::PAYEE->value,
            'client_id' => $clients[2]->id,
            'user_id' => $admin->id,
        ]);
        $lotF = $getLot('PFI-F050');
        $lotG = $getLot('PFI-F051');
        DB::table('vente_produit')->insert([
            ['vente_id' => $vente4Id, 'produit_id' => $produits[9]->id, 'lot_id' => $lotF->id, 'prix' => 65.00, 'prix_achat' => 40.00, 'qte' => 20, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente4Id, 'produit_id' => $produits[16]->id, 'lot_id' => $lotG->id, 'prix' => 75.00, 'prix_achat' => 48.00, 'qte' => 20, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('commande_produit')->where('id', $lotF->id)->decrement('qte', 20);
        DB::table('commande_produit')->where('id', $lotG->id)->decrement('qte', 20);

        // Vente 5 — Yesterday, en compte
        $vente5Id = $insertVente([
            'n_facture' => 'VT-2026-005',
            'date' => $now->copy()->subDay()->format('Y-m-d'),
            'dateEcheance' => $now->copy()->addMonth()->format('Y-m-d'),
            'paiement' => 'En compte',
            'montantPaye' => 0,
            'subtotal' => 580.00,
            'remise' => 0,
            'remise_amount' => 0,
            'reste' => 580.00,
            'benefice' => 180.00,
            'statut' => StatutVente::PARTIEL->value,
            'client_id' => $clients[3]->id,
            'user_id' => $pharmacien->id,
        ]);
        $lotH = $getLot('COP-C022');
        $lotI = $getLot('SOT-D030');
        DB::table('vente_produit')->insert([
            ['vente_id' => $vente5Id, 'produit_id' => $produits[14]->id, 'lot_id' => $lotH->id, 'prix' => 28.00, 'prix_achat' => 16.00, 'qte' => 10, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente5Id, 'produit_id' => $produits[13]->id, 'lot_id' => $lotI->id, 'prix' => 48.00, 'prix_achat' => 30.00, 'qte' => 6, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('commande_produit')->where('id', $lotH->id)->decrement('qte', 10);
        DB::table('commande_produit')->where('id', $lotI->id)->decrement('qte', 6);

        // Vente 6 — Today, cash
        $vente6Id = $insertVente([
            'n_facture' => 'VT-2026-006',
            'date' => $now->format('Y-m-d'),
            'paiement' => 'Éspèce',
            'montantPaye' => 218.00,
            'subtotal' => 218.00,
            'remise' => 0,
            'remise_amount' => 0,
            'reste' => 0,
            'benefice' => 68.00,
            'statut' => StatutVente::PAYEE->value,
            'client_id' => $clients[8]->id,
            'user_id' => $vendeur->id,
        ]);
        $lotJ = $getLot('SAN-A004');
        $lotK = $getLot('COP-H070');
        DB::table('vente_produit')->insert([
            ['vente_id' => $vente6Id, 'produit_id' => $produits[22]->id, 'lot_id' => $lotJ->id, 'prix' => 48.00, 'prix_achat' => 28.00, 'qte' => 2, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente6Id, 'produit_id' => $produits[26]->id, 'lot_id' => $lotK->id, 'prix' => 35.00, 'prix_achat' => 20.00, 'qte' => 2, 'remise' => 0, 'tva' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['vente_id' => $vente6Id, 'produit_id' => $produits[0]->id, 'lot_id' => $lotA->id, 'prix' => 25.00, 'prix_achat' => 15.00, 'qte' => 2, 'remise' => 0, 'tva' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('commande_produit')->where('id', $lotJ->id)->decrement('qte', 2);
        DB::table('commande_produit')->where('id', $lotK->id)->decrement('qte', 2);
        DB::table('commande_produit')->where('id', $lotA->id)->decrement('qte', 2);


    }
}
