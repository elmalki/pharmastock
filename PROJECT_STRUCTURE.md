# PharmaStock - Pharmacy POS System

A Point of Sale (POS) and inventory management system for pharmacies, built with Laravel 11 and Vue 3. Designed for the Moroccan market (French UI, MAD currency).

---

## Tech Stack

| Layer       | Technology                                                                 |
|-------------|---------------------------------------------------------------------------|
| Backend     | PHP 8.2+, Laravel 11, Inertia.js 1.0                                     |
| Frontend    | Vue 3 (Composition API), PrimeVue 4.3, Vuetify 3.6, Tailwind CSS 3.4    |
| Auth        | Laravel Jetstream 5.1 + Sanctum (2FA, profile photos, API tokens)        |
| RBAC        | Spatie Laravel Permission 6.10 (roles & permissions)                     |
| Database    | MySQL (`gstock`)                                                          |
| PDF         | Barryvdh DomPDF 3.0                                                      |
| Excel       | Maatwebsite Excel 3.1                                                     |
| Barcodes    | Milon Barcode 11.0                                                        |
| Charts      | Highcharts 12.4 (area, pie, column)                                       |
| Audit Log   | Spatie Laravel Activitylog 4.9                                            |
| Build       | Vite 6.2 + laravel-vite-plugin (SSR support)                             |
| Testing     | PestPHP 2.0                                                               |

---

## Directory Structure

```
pharmastock/
├── app/
│   ├── Actions/
│   │   ├── Fortify/                    # Auth actions (CreateNewUser, UpdatePassword, etc.)
│   │   └── Jetstream/                  # DeleteUser action
│   ├── Exceptions/
│   │   └── Handdlers.php
│   ├── Exports/
│   │   ├── ProduitsExport.php          # Excel export: full stock list
│   │   └── ProduitsPerimesExport.php   # Excel export: expired products
│   ├── Filters/                        # Pipeline filter classes for search
│   │   ├── Filter.php                  # Abstract base filter
│   │   ├── StartDate.php
│   │   ├── EndDate.php
│   │   ├── FournisseurId.php
│   │   ├── NBon.php
│   │   └── NFacture.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php     # Dashboard stats + notifications
│   │   │   ├── ProduitController.php       # Products CRUD + PDF/Excel exports + barcodes
│   │   │   ├── CommandeController.php      # Purchase orders CRUD + search
│   │   │   ├── VenteController.php         # Sales CRUD (partially implemented)
│   │   │   ├── DestockageController.php    # Stock write-offs + PDF decharge
│   │   │   ├── StockageController.php      # Legacy stock entry controller
│   │   │   ├── FournisseurController.php   # Supplier CRUD
│   │   │   ├── ClientController.php        # Client CRUD
│   │   │   ├── CategoryController.php      # Category CRUD
│   │   │   ├── AgendaController.php        # Calendar events (stubs)
│   │   │   ├── UserController.php          # User management + role assignment
│   │   │   ├── RoleController.php          # Role CRUD + permission syncing
│   │   │   └── PermissionController.php    # Permission listing
│   │   ├── Middleware/
│   │   │   └── HandleInertiaRequests.php   # Shares notifications, permissions, roles
│   │   └── Requests/                       # 20+ FormRequest classes (validation stubs)
│   ├── Models/
│   │   ├── Produit.php             # Product
│   │   ├── Commande.php            # Purchase order
│   │   ├── CommandeProduit.php     # Stock lot (pivot with business logic)
│   │   ├── Vente.php               # Sale
│   │   ├── Client.php              # Customer
│   │   ├── Fournisseur.php         # Supplier
│   │   ├── Categorie.php           # Product category
│   │   ├── Entree.php              # Legacy stock entry
│   │   ├── Destockage.php          # Stock write-off
│   │   ├── Agenda.php              # Calendar event
│   │   ├── User.php                # User with roles/permissions
│   │   └── Setting.php             # App settings (destockage counter)
│   ├── Notifications/
│   │   ├── Stock.php               # Low stock alert
│   │   ├── DashboardNotification.php # Post-destockage low stock alert
│   │   ├── Expired.php             # Expiring lot alert (within 4 days)
│   │   ├── Agenda.php              # Calendar event notification
│   │   └── Test.php
│   ├── Policies/                   # Authorization policies per resource
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── AuthServiceProvider.php
│       ├── FortifyServiceProvider.php
│       └── JetstreamServiceProvider.php
│
├── database/
│   ├── factories/                  # 10 model factories (empty stubs)
│   ├── migrations/                 # 20 migration files (see schema below)
│   └── seeders/
│       ├── DatabaseSeeder.php      # Creates admin user + Administrateur role
│       └── PermissionSeeder.php    # Seeds CRUD permissions per resource
│
├── resources/
│   ├── js/
│   │   ├── app.js                  # Vue bootstrap: PrimeVue + Vuetify + Inertia
│   │   ├── ssr.js                  # Server-side rendering entry
│   │   ├── Components/             # 25+ shared Vue components
│   │   ├── Layouts/
│   │   │   ├── AppLayout.vue       # Main layout: sidebar, topbar, can() helper
│   │   │   └── Menu.vue            # Navigation menu
│   │   └── Pages/                  # 60+ Vue page components (see below)
│   └── views/
│       ├── app.blade.php           # Inertia root template
│       ├── export/                 # Excel blade templates
│       └── pdf/                    # PDF blade templates (stock, perimes, decharge, barcodes)
│
├── routes/
│   ├── web.php                     # All authenticated routes
│   └── api.php                     # Public API endpoints
│
├── config/                         # Laravel config + activitylog, excel, permission, laratex
├── composer.json
├── package.json
└── vite.config.js
```

---

## Database Schema

### Core Tables

#### `produits` — Products
| Column         | Type           | Description                    |
|----------------|----------------|--------------------------------|
| id             | bigint (PK)    | Auto-increment                 |
| barcode        | string?        | Product barcode                |
| label          | string?        | Product name                   |
| unite          | string?        | Unit of measure                |
| description    | string?        | Product description            |
| perissable     | boolean        | Is perishable (default: false) |
| generated      | boolean        | Barcode auto-generated         |
| limit_command  | uint?          | Low stock threshold            |
| categorie_id   | FK → categories| Product category               |

#### `commande_produit` — Stock Lots (Core Inventory)
> The central stock management table. Each row represents a lot from a purchase order.

| Column         | Type           | Description                         |
|----------------|----------------|-------------------------------------|
| produit_id     | FK → produits  | Product reference                   |
| commande_id    | FK → commandes | Source purchase order                |
| n_lot          | string         | Lot number                          |
| tva            | uint           | Tax rate (default: 0)               |
| qte            | uint           | **Current available stock**          |
| qte_achete     | uint           | Original purchased quantity          |
| prix_achat     | decimal(8,2)   | Purchase price                      |
| prix_vente     | decimal(8,2)   | Selling price                       |
| expirationDate | date?          | Lot expiration date                 |

#### `commandes` — Purchase Orders
| Column         | Type           | Description                           |
|----------------|----------------|---------------------------------------|
| id             | bigint (PK)    | Auto-increment                        |
| n_bon          | string         | Delivery note number                  |
| n_facture      | string         | Invoice number                        |
| date           | date           | Order date                            |
| dateEcheance   | date           | Payment due date                      |
| montantPaye    | decimal        | Amount paid                           |
| paiement       | enum           | Espece/Cheque/Effet/Virement/...      |
| situation      | string         | Order status                          |
| fournisseur_id | FK → fournisseurs | Supplier                           |

#### `ventes` — Sales
| Column         | Type           | Description                           |
|----------------|----------------|---------------------------------------|
| id             | bigint (PK)    | Auto-increment                        |
| n_facture      | string         | Invoice number                        |
| date           | date           | Sale date                             |
| dateEcheance   | date           | Payment due date                      |
| ordonnance     | string         | Prescription reference                |
| paiement       | enum           | Credit/Espece/TPE/Cheque/...          |
| montantPaye    | decimal(8,2)   | Amount paid                           |
| benefice       | decimal(8,2)   | Profit                                |
| client_id      | FK → clients?  | Customer                              |
| subtotal       | decimal        | Subtotal before discount              |
| remise         | uint           | Discount percentage                   |
| remise_amount  | decimal        | Discount amount                       |
| reste          | decimal        | Remaining balance                     |
| statut         | string         | Sale status                           |

#### `vente_produit` — Sale Line Items
| Column     | Type          | Description          |
|------------|---------------|----------------------|
| produit_id | FK → produits | Product              |
| vente_id   | FK → ventes   | Sale                 |
| tva        | uint          | Tax rate             |
| prix       | double(8,2)   | Selling price        |
| prix_achat | double(8,2)   | Cost price           |
| qte        | uint          | Quantity sold        |
| remise     | uint          | Line item discount   |

#### `destockages` — Stock Write-offs
| Column         | Type          | Description                   |
|----------------|---------------|-------------------------------|
| id             | bigint (PK)   | Auto-increment                |
| n_destockage   | string?       | Write-off number (e.g. 5/2026)|
| fonctionnaire  | string?       | Responsible official          |
| motifs         | varchar(500)  | Reason for write-off          |
| user_id        | FK → users?   | User who performed it         |

#### `destockage_produit` — Write-off Line Items
| Column        | Type              | Description       |
|---------------|-------------------|-------------------|
| produit_id    | FK → produits     | Product           |
| destockage_id | FK → destockages  | Write-off         |
| qte           | uint              | Quantity removed  |

### Supporting Tables

#### `categories`
| Column | Type        | Description     |
|--------|-------------|-----------------|
| id     | bigint (PK) | Auto-increment  |
| label  | string      | Category name   |

#### `fournisseurs` — Suppliers
| Column  | Type        | Description        |
|---------|-------------|--------------------|
| id      | bigint (PK) | Auto-increment     |
| societe | string?     | Company name       |
| contact | string?     | Contact person     |
| adresse | string?     | Address            |
| email   | string?     | Email address      |

#### `clients` — Customers
| Column | Type        | Description    |
|--------|-------------|----------------|
| id     | bigint (PK) | Auto-increment |
| nom    | string      | Client name    |
| tel    | string?     | Phone number   |

#### `agendas` — Calendar Events
| Column      | Type        | Description                          |
|-------------|-------------|--------------------------------------|
| id          | bigint (PK) | Auto-increment                       |
| title       | string      | Event title                          |
| description | string      | Event description                    |
| degree      | enum        | Urgent / Moyen / Ordinaire           |
| debut/fin   | date        | Start/end date                       |
| hdebut/hfin | time        | Start/end time                       |
| isRecurrent | boolean     | Is recurring event                   |
| event_id    | FK → self?  | Parent event (for recurrence)        |

#### `settings` — Application Settings
| Column            | Type        | Description                        |
|-------------------|-------------|------------------------------------|
| destockage_number | int         | Auto-incrementing counter per year |
| year              | year        | Current year                       |

#### `users` — Users (Jetstream)
Standard Laravel + Jetstream fields: name, email, password, profile_photo_path, etc.

#### System Tables
- `notifications` — Laravel database notifications (UUID, polymorphic)
- `activity_log` — Spatie activity log (audit trail)
- `permissions` / `roles` / `model_has_roles` / `role_has_permissions` — Spatie RBAC

---

## Model Relationships

```
User ──has many──▶ Roles ──has many──▶ Permissions

Categorie ──has many──▶ Produit

Fournisseur ──has many──▶ Commande

Commande ◀──many-to-many──▶ Produit
         (via commande_produit: lot-level stock)

Vente ◀──many-to-many──▶ Produit
      (via vente_produit: sale line items)
Vente ──belongs to──▶ Client

Destockage ◀──many-to-many──▶ Produit
           (via destockage_produit)
Destockage ──belongs to──▶ User

Agenda ──belongs to──▶ Agenda (self-referential for recurrence)
```

---

## Routes

### Web Routes (all require `auth:sanctum` + `verified`)

| Method   | URI                      | Controller@Action              | Description                    |
|----------|--------------------------|--------------------------------|--------------------------------|
| GET      | `/`                      | redirect → `/dashboard`        | Home redirect                  |
| GET      | `/dashboard`             | DashboardController@index      | Dashboard with stats & charts  |
| GET      | `/notifications`         | DashboardController@notifications | Notification inbox          |
| GET      | `/markasread`            | DashboardController@markAllAsRead | Mark all read              |
| DELETE   | `/notifications/all`     | DashboardController@deleteAll  | Clear all notifications        |
| DELETE   | `/notifications/{id}`    | DashboardController@delete     | Delete single notification     |
| Resource | `/produits`              | ProduitController              | Full CRUD for products         |
| Resource | `/commandes`             | CommandeController             | Full CRUD for purchase orders  |
| Resource | `/ventes`                | VenteController                | Full CRUD for sales            |
| Resource | `/destockages`           | DestockageController           | Full CRUD for write-offs       |
| Resource | `/fournisseurs`          | FournisseurController          | Full CRUD for suppliers        |
| Resource | `/clients`               | ClientController               | Full CRUD for customers        |
| Resource | `/categories`            | CategoryController             | Full CRUD for categories       |
| Resource | `/users`                 | UserController                 | User management                |
| Resource | `/roles`                 | RoleController                 | Role management                |
| Resource | `/permissions`           | PermissionController           | Permission listing             |
| GET      | `/export`                | ProduitController@export       | Excel stock export             |
| GET      | `/exportPerimes`         | ProduitController@exportPerimes| Excel expired products export  |
| POST     | `/stock`                 | ProduitController@stock        | PDF stock list                 |
| POST     | `/barcodes`              | ProduitController@barcodes     | PDF barcode sheet              |
| GET      | `/perimes`               | ProduitController@perimes      | Expired products page          |
| POST     | `/perimesPDF`            | ProduitController@perimesPDF   | PDF expired lots               |
| GET      | `/peremption`            | —                              | Expiry search page             |
| GET      | `/echeances`             | —                              | Calendar / due dates           |
| GET      | `/commandes/search`      | —                              | Purchase order search page     |
| POST     | `/commandes/search`      | CommandeController@search      | Search purchase orders         |
| GET      | `/decharge/{destockage}` | DestockageController@decharge  | PDF write-off document         |

### API Routes (mostly public, no auth)

| Method | URI                | Description                              |
|--------|--------------------|------------------------------------------|
| GET    | `/api/produits`    | All products                             |
| GET    | `/api/fournisseurs`| All suppliers                            |
| GET    | `/api/clients`     | All clients                              |
| POST   | `/api/peremption`  | Lots expiring in date range              |
| POST   | `/api/commandes`   | Purchase orders expiring in date range   |
| POST   | `/api/getLots`     | Available lots for a product (qte > 0)   |

---

## Vue Pages

```
Pages/
├── Dashboard.vue                   # Stats cards, Highcharts (area/pie/column)
├── Calendar.vue                    # Schedule calendar for due dates
├── Facture.vue                     # Invoice view
│
├── Produits/
│   ├── Index.vue                   # Product list (DataTable, sort, search, PDF/Excel export)
│   ├── Create.vue                  # Product form (barcode, category, perishable toggle)
│   ├── Edit.vue                    # Edit product
│   ├── Show.vue                    # Product detail with lot breakdown
│   ├── Perimes.vue                 # Expired lots list
│   ├── Peremption.vue              # Expiry date range search
│   ├── SelectProducts.vue          # Multi-product selector for purchases
│   ├── SelectProductsOut.vue       # Multi-product selector for sales (with lot picker)
│   └── product-input.vue           # Per-product row: lot, qty, price, expiry, TVA
│
├── Commandes/
│   ├── Index.vue                   # Purchase order list
│   ├── Create.vue                  # Create purchase order
│   ├── Edit.vue                    # Edit purchase order
│   ├── Show.vue                    # Purchase order detail
│   └── Search.vue                  # Filtered search
│
├── Ventes/
│   ├── Index.vue                   # Sales list
│   └── Create.vue                  # POS sale form (client, products, lots, totals, discounts)
│
├── Destockages/
│   ├── Index.vue                   # Write-off list
│   └── Create.vue                  # Create write-off (rich text motifs, product selection)
│
├── Clients/
│   ├── Index.vue / Create.vue / Edit.vue
│   └── SelectClient.vue            # Client autocomplete
│
├── Fournisseurs/
│   ├── Index.vue / Create.vue / Edit.vue
│   └── SelectFournisseur.vue       # Supplier autocomplete
│
├── Categories/
│   └── Index.vue                   # Inline CRUD table
│
├── Users/
│   └── Index.vue / Create.vue / Edit.vue   # User management with role assignment
│
├── Roles/
│   └── Index.vue / Create.vue / Edit.vue   # Role + permission management
│
├── Dashboard/
│   └── Notifications.vue           # Notification inbox
│
├── Auth/                           # Jetstream: Login, Register, 2FA, etc.
├── Profile/                        # Jetstream: Profile, password, sessions
└── API/                            # Jetstream: API token management
```

---

## Authorization & Permissions

### Roles
- **Administrateur** — Full access (bypasses all permission checks)
- Custom roles can be created via the UI with granular permissions

### Permissions (per resource)
For each of: Produit, Destockage, Categorie, Fournisseur, Client, Approvisionnement, Vente:
- `Ajouter {resource}` — Create
- `Modifier {resource}` — Update
- `Supprimer {resource}` — Delete
- `Lister {resource}` — View/List

Frontend permission check via `can()` helper in `AppLayout.vue`:
```js
function can(permission) {
    return roles[0] === 'Administrateur' || permissions.includes(permission);
}
```

---

## Notification System

| Notification          | Trigger                                      | Description                                  |
|-----------------------|----------------------------------------------|----------------------------------------------|
| `Stock`               | Product quantity falls below `limit_command`  | Low stock alert                              |
| `DashboardNotification`| After destockage reduces stock below limit  | Post write-off low stock alert               |
| `Expired`             | Lot expires within 4 days                    | Expiration warning                           |
| `Agenda`              | Calendar event (not wired)                   | Event reminder                               |

---

## Core Business Logic

### Inventory Flow
1. **Purchasing (Approvisionnement):** `CommandeController@store` creates a purchase order and inserts lot records into `commande_produit` with initial `qte` = `qte_achete`
2. **Selling (Vente):** `VenteController@store` creates a sale and decrements `commande_produit.qte` using FEFO (First Expired, First Out)
3. **Write-off (Destockage):** `DestockageController@store` removes stock from lots and generates a PDF "decharge" document
4. **Stock tracking:** Total product stock = `SUM(commande_produit.qte)` for that product across all lots

### Stock Lot Model (`CommandeProduit`)
The `commande_produit` table is the heart of the inventory system. Key features:
- **Row-level locking** via `lockForUpdate()` during stock operations
- **DB transactions** for atomic stock changes
- **Scopes:** `enStock`, `rupture`, `expired`, `expiringSoon`, `FEFO`, `lowStock`
- **Methods:** `incrementQuantite()`, `decrementQuantite()`, `ajusterStock()`, `reserver()`
- **Computed:** `isExpired()`, `daysUntilExpiration()`, `getStockValue()`, `getPotentialProfit()`

### Search Pipeline
Purchase orders and sales use Laravel's Pipeline pattern for filtered search:
```
Query → StartDate → EndDate → NBon → NFacture → FournisseurId → Result
```

---

## Exports & Reports

| Format | Route            | Description                          |
|--------|------------------|--------------------------------------|
| Excel  | `/export`        | Full stock list                      |
| Excel  | `/exportPerimes` | Expired products                     |
| PDF    | `/stock`         | Stock list document                  |
| PDF    | `/perimesPDF`    | Expired lots document                |
| PDF    | `/barcodes`      | Barcode sheet for selected products  |
| PDF    | `/decharge/{id}` | Write-off certificate (décharge)     |

---

## Default Admin User

| Field    | Value                        |
|----------|------------------------------|
| Name     | Yassine EL MALKI             |
| Email    | yassine.elmalki@gmail.com    |
| Password | password                     |
| Role     | Administrateur               |
