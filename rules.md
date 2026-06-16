# Rituals Multi-Location E-Commerce Platform — Project Rules & Architecture

> Laravel 13 + Livewire 4  
> Traject B (zonder stage) — Eindopdracht E-Commerce Platform  
> Multi-tenant architectuur met meerdere Rituals locaties (shops), centrale platform-admin, robuuste betalingen en schaalbare backend

---

## Inhoud

- [Doel van dit document](#doel-van-dit-document)
- [Projectcontext](#projectcontext)
- [Niet-onderhandelbare versie- en syntaxregels](#niet-onderhandelbare-versie--en-syntaxregels)
- [Architectuurprincipes](#architectuurprincipes)
- [Kernmodules](#kernmodules)
- [Multi-tenancy regels](#multi-tenancy-regels)
- [Rollen en autorisatie](#rollen-en-autorisatie)
- [Datamodelregels](#datamodelregels)
- [Productregels](#productregels)
- [Voorraadregels](#voorraadregels)
- [Checkout en betalingen](#checkout-en-betalingen)
- [Orders](#orders)
- [Dashboards](#dashboards)
- [Mail en queues](#mail-en-queues)
- [Logging en foutafhandeling](#logging-en-foutafhandeling)
- [Testing](#testing)
- [Examengerichte hoofdregel](#examengerichte-hoofdregel)
- [Samenvattende hoofdregel](#samenvattende-hoofdregel)

---

## Doel van dit document

Dit document definieert de vaste architectuur-, code- en projectregels voor dit project in VS Code. Alles wat gegenereerd, aangepast of voorgesteld wordt, moet compatibel zijn met:

- Laravel 13
- Livewire 4
- Flux UI free tier
- MySQL of MariaDB
- Pest

Dit project is geen demo-webshop en geen klassiek single-tenant CMS.  
Dit project is een **multi-tenant e-commerce platform voor Rituals** in Laravel 13 + Livewire 4 waarin meerdere fysieke winkellocaties (shops) hun eigen voorraad en verkopen beheren via één gedeeld platform.

Het systeem ondersteunt:

- meerdere Rituals shops / locaties
- tenant-isolatie per locatie
- centrale Rituals platformadministratie
- shop dashboards
- voorraad- en productbeheer per locatie
- checkout en betalingen
- orderverwerking
- logging
- queues
- schaalbare architectuur

Dit document is bindend voor alle code, architectuurkeuzes, dataflows en gegenereerde voorstellen.

---

## Projectcontext

### Hoofddoel

Bouw een **multi-tenant e-commerce platform** voor de brand Rituals waarin meerdere fysieke winkellocaties (shops) hun eigen operaties beheren binnen één centrale applicatie.

### Actoren

#### Shop Manager / Locatiebeheerder
Elke shop:

- beheert de eigen toegewezen producten
- ziet enkel zijn eigen bestellingen
- beheert enkel zijn eigen voorraad voor die specifieke locatie
- heeft een eigen locatie-dashboard
- werkt binnen een afgeschermde tenant-context (de specifieke shop)

#### Centrale Rituals Admin (Platform Admin)
De centrale admin:

- beheert alle tenants (locaties)
- beheert alle shop managers
- ziet alle orders van alle locaties
- ziet alle betalingen platformbreed
- beheert globale platforminstellingen
- bewaakt platformbrede integriteit

#### Klant / Customer
De klant:

- bezoekt de storefront
- bekijkt producten (eventueel locatie-specifiek)
- plaatst producten in winkelwagen
- rekent af
- ontvangt orderbevestiging
- volgt bestellingen op

Deze architectuur moet voldoen aan de Traject B verwachtingen uit de eindopdracht: multi-tenancy, logging, foutafhandeling, robuuste betaalflow, schaalbaarheid en professionele architectuur.

---

## Niet-onderhandelbare versie- en syntaxregels

### Verboden

Gebruik nooit:

- Livewire 2 of 3 syntax
- verouderde Laravel syntax
- business logic in Blade
- business logic in routes
- business logic in models buiten scopes/casts/accessors
- inline queries in views
- `float` voor geld
- hardcoded tenant IDs
- hardcoded shop IDs
- globale queries zonder tenant-scope
- Stripe calls in views/components
- stock updates zonder transactionele afhandeling
- admin filtering enkel in UI
- multi-tenant filtering via frontend-only checks
- mock data als eindoplossing
- “één tabel voor alles”-architectuur
- tutorial hacks
- inline classes of volledige namespace paden (FQCN) ergens midden in de code

### Verplicht

Gebruik altijd:

- **`use` statements bovenaan het bestand in plaats van de volledige namespace (link) inline in de code te gebruiken.**
- `Route::livewire()` waar logisch
- Livewire 4 single-file components
- constructor dependency injection
- Services
- Actions
- Form Objects
- Policies
- middleware
- Enums
- eager loading
- typed properties
- return types
- transactions waar data-integriteit kritisch is
- tenant scoping op databankniveau
- queue-based async flows waar professioneel vereist

---

## Architectuurprincipes

Dit project volgt strikt:

- separation of concerns
- domain-driven structuur
- thin controllers
- thin Livewire components
- action-driven business logic
- service-based integraties
- policy-based authorization
- tenant-aware querying
- transaction-safe writes
- queue-based background processing
- logging van kritieke flows

Geen enkele laag mag verantwoordelijkheden van een andere laag overnemen.

---

## Kernmodules

Gebruik deze domeinen als basis:

- Tenancy
- Shops (Locaties)
- Catalog
- Inventory
- Cart
- Checkout
- Payments
- Orders
- Customers
- Admin
- Platform
- Analytics

---

## Multi-tenancy regels

### Hoofdregel

Alles is tenant-aware tenzij expliciet platform-level.

Dat betekent:

- voorraad behoort tot een tenant/shop
- orders behoren tot een tenant/shop
- dashboards zijn tenant-scoped
- queries zijn tenant-scoped
- policies zijn tenant-scoped
- validatie is tenant-aware

### Verplicht

Elke tenant-gebonden tabel bevat tenant-context.

Gebruik minimaal één van deze patronen:

- `tenant_id`
- `shop_id`

Bij tenant-gebonden data moet altijd expliciet duidelijk zijn aan welke locatie de data gekoppeld is.

### Verboden

Nooit:

- orders ophalen zonder tenant-scope
- voorraad ophalen zonder tenant-scope
- adminfilters enkel in frontend toepassen
- vertrouwen op verborgen UI als beveiliging
- tenant-isolatie oplossen met conventie i.p.v. technische afdwinging

### Verplicht tenant patroon

Elke query op tenant-data (shop data) moet:

- expliciet tenant-scoped zijn
- policy-gecontroleerd zijn
- niet afhankelijk zijn van UI filtering

---

## Rollen en autorisatie

Gebruik minimaal deze rollen:

- `platform_admin`
- `shop_admin`
- `shop_staff`
- `customer`

### Toegang

#### platform_admin (Centrale Rituals Admin)
Kan:

- alle locaties (shops) beheren
- alle orders zien
- alle betalingen zien
- alle dashboards zien
- globale Rituals instellingen beheren

#### shop_admin (Locatiebeheerder)
Kan:

- eigen shop beheren
- eigen voorraad beheren
- eigen orders beheren
- eigen personeel beheren
- eigen analytics zien

#### shop_staff (Winkelmedewerker)
Kan:

- eigen voorraad raadplegen/beheren
- eigen orders verwerken
- beperkte backoffice acties uitvoeren

#### customer
Kan:

- browsen
- bestellen
- eigen orders zien
- account beheren

Autorisatie gebeurt via:

- Policies
- Gates indien nuttig
- middleware
- nooit enkel via UI

---

## Datamodelregels

Minimaal voorzien:

- tenants
- shops (fysieke locaties)
- tenant_users
- categories
- products
- product_variants
- product_images
- carts
- cart_items
- orders
- order_items
- payments
- payment_logs
- stock_movements
- addresses

### Verplicht

#### tenants
Platform tenant context

#### shops
Fysieke locatie/winkel binnen het Rituals platform

#### tenant_users
Koppelt users aan een tenant/shop met specifieke rol

#### payments
Betaaltransacties

#### payment_logs
Webhook + payment event logging

#### stock_movements
Volledige stockhistoriek per shop

Stock is geen los veld-only systeem.  
Stockmutaties moeten historisch en transactioneel correct zijn per locatie.

---

## Productregels

Producten moeten ondersteunen:

- categorie
- shop/locatie beschikbaarheid
- tenant
- naam
- slug
- beschrijving
- prijs
- status
- actieve status
- featured status
- stock (per shop)
- varianten
- afbeeldingen

Minstens één varianttype verplicht (bijv. geurlijn of inhoudsmaat):

- maat (ml)
- geurlijn / collectie
- uitvoering

Dit is expliciet vereist in de opdracht.

---

## Voorraadregels

Voorraad is geen simpel integer veld, en verschilt per locatie.

Gebruik:

- `stock_on_hand`
- `stock_reserved`
- `stock_available`

Gebruik stockmutaties via:

- `stock_movements`

Voorraadupdates:

- altijd transactioneel
- nooit blind overschrijven
- nooit race-condition gevoelig
- altijd auditbaar

Gebruik Actions voor stockmutaties.

---

## Checkout en betalingen

### Verplicht

Checkoutflow:

1. klant vult gegevens in
2. cart wordt gevalideerd (en gecheckt tegen locatie voorraad)
3. order wordt aangemaakt
4. order items worden gesnapshot
5. payment record wordt aangemaakt
6. checkout session wordt gestart
7. klant betaalt
8. webhook verwerkt definitieve status
9. orderstatus wordt aangepast
10. stock van de gekozen shop wordt definitief aangepast
11. mail wordt gequeued

### Verboden

Nooit:

- order op paid zetten via redirect-only
- betaling vertrouwen op query params
- stock verlagen vóór bevestigde payment
- webhook events niet loggen

### Verplicht

Gebruik:

- Stripe of Mollie
- webhook verwerking
- payment statuses
- payment logs
- retries
- foutafhandeling
- logging

Webhook-verwerking is verplicht in Traject B.

---

## Orders

Orders bevatten minimaal:

- tenant_id
- shop_id (welke locatie levert/verkoopt)
- customer_id nullable
- order_number
- status
- payment_status
- subtotal
- tax_total
- grand_total
- billing snapshot
- shipping snapshot

Order items bevatten snapshots:

- product_name
- sku
- variant
- unit_price
- quantity
- subtotal

Orders zijn immutable-ish records.  
Na betaling geen destructieve herschrijvingen van historische orderdata.

---

## Dashboards

### Shop dashboard (Locatie)

Minimaal:

- omzet van deze specifieke shop
- orders van deze shop
- top producten
- recente bestellingen
- statusfilter
- datumfilters

### Platform dashboard (Centraal Rituals)

Minimaal:

- omzet platformbreed
- omzet per shop/locatie
- actieve shops
- failed payments
- pending orders
- top presterende locaties

Traject B verwacht expliciet uitgebreidere dashboards.

---

## Mail en queues

Verplicht:

- order confirmation mail
- payment confirmation mail
- optioneel status update mails

Gebruik:

- queued jobs
- queued mailables

Geen synchrone mail in checkout flow.

---

## Logging en foutafhandeling

Verplicht loggen:

- payment events
- webhook events
- checkout failures
- stock conflicts
- critical admin actions

Verplicht:

- nette foutafhandeling
- geen silent failures
- geen swallowed exceptions
- bruikbare logs

Logging is verplicht in Traject B.

---

## Testing

Gebruik Pest.

Minimaal testen:

- tenant isolation per shop
- shop admin authorization
- product CRUD
- checkout flow
- payment webhook flow
- stock mutation flow
- policy tests
- dashboard access
- platform admin access
- payment logging

---

## Examengerichte hoofdregel

Elke architecturale keuze moet verdedigbaar zijn tegenover de jury.

Code moet aantonen:

- dat multi-tenancy bewust is uitgewerkt voor de locaties
- dat tenant-isolatie technisch afgedwongen wordt
- dat betalingen robuust verwerkt worden
- dat stock per locatie veilig beheerd wordt
- dat de 'use' regel strict wordt toegepast
- dat logging aanwezig is
- dat queues correct gebruikt worden
- dat architectuur onderhoudbaar is
- dat code professioneel schaalbaar is

Niet enkel “werkend”, maar verdedigbaar, logisch en professioneel opgebouwd.

---

## Samenvattende hoofdregel

Bouw dit project alsof je het officiële, schaalbare e-commerce platform ontwikkelt voor de Rituals brand, waarin meerdere fysieke winkellocaties veilig, gescheiden en professioneel de online verkopen afhandelen via één platform, met Laravel 13 + Livewire 4, production-minded architectuur, transactionele betrouwbaarheid en jury-verdedigbare technische keuzes.
