<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

#   e x a m - w e b s h o p  :  s t e p s

## Ontwikkelingsstappen (Feature-Based via Livewire)

Dit document volgt een verticale integratie per feature. Alles wordt strikt via **Livewire 4** afgehandeld. Controllers en standaard Blade components (zoals anonieme componenten of traditionele controllers) worden vermeden ten gunste van **Full-Page Livewire componenten**, geneste Livewire componenten en Livewire Form Objects.

---

### Fase 1: Project Fundament & Layouts
- [x] **Logica:** Initialiseer Laravel 13, Livewire 4 en het `stancl/tenancy` pakket. Configureer de database connectie en tenant/domain routes uitsluitend via `Route::livewire()`.
- [x] **View:** Installeer en configureer Flux UI. Ontwerp de basis layouts uitsluitend als Livewire layout bestanden (`components/layouts/app.blade.php`). Bouw de globale navigatiestructuur als een dynamisch Livewire component (`Navigation`).

---

### Fase 2: Authenticatie & Rollen (Platform vs Tenant)
- [x] **Logica:** Maak migraties voor `users` en koppel rollen (`platform_admin`, `vendor_admin`, `vendor_staff`, `customer`). Implementeer tenant-middleware en basis Policies.
- [x] **View:** Bouw de Login en Register flows als Full-Page Livewire componenten (`Auth\Login` en `Auth\Register`). Gebruik Livewire Form Objects voor de validatie en dataverwerking.

---

### Fase 3: Catalogus & Productbeheer (Vendor Backoffice)
- [x] **Logica:** Migraties en modellen voor `shops`, `categories`, `products`, en `product_variants`. Implementeer tenant-scoping en gebruik Action classes, aangeroepen vanuit Livewire.
- [x] **View:** Bouw het Vendor Dashboard via Full-Page Livewire componenten (`Seller\Dashboard`). Maak overzichten met `Seller\Products\Index` en formulieren via `Seller\Products\Create` en `Seller\Products\Edit` (inclusief variantbeheer), alles met Livewire Form Objects.

---

### Fase 4: Storefront (Klantweergave)
- [ ] **Logica:** Geen controllers. Gebruik component methodes (`mount()`, `with()`) om actieve producten op te halen voor de huidige tenant, inclusief eager loading.
- [ ] **View:** Bouw de storefront volledig via Full-Page Livewire componenten: `Shop\Home`, `Shop\Products` en de interactieve `Shop\ProductDetail` voor variant-selectie zonder page reloads.

---

### Fase 5: Winkelwagen & Visuele Voorraad (Klant)
- [ ] **Logica:** Ontwikkel een Cart Service geïntegreerd met de basisvoorraadvelden (`stock_on_hand`, `stock_reserved`, `stock_available`). Roep deze aan vanuit het Livewire winkelwagen component.
- [ ] **View:** Bouw het `Shop\Cart` Livewire component. Toon visuele voorraadwaarschuwingen (bijv. "Nog 2 op voorraad") en implementeer asynchrone updates voor hoeveelheden en prijzen via Livewire acties.

---

### Fase 6: Checkout Flow (Klant)
- [ ] **Logica:** Vervang de CheckoutController door een Full-Page Livewire component (`Checkout\Index`). Behandel hierin de winkelwagen validatie, snapshotting in `order_items`, het aanmaken van `orders`/`payments` en de Stripe/Mollie redirect.
- [ ] **View:** Bouw het formulier voor adresgegevens en betaalmethode ín het `Checkout\Index` component. Maak `Checkout\Success` en `Checkout\Cancel` Full-Page componenten voor de afhandeling na de redirect.

---

### Fase 7: Webhooks, Orderverwerking & Order UI (Achtergrond + Vendor)
- [ ] **Logica:** Bouw een externe Webhook listener (enige uitzondering: dit mag een invokable controller/route zijn voor de API webhook van Stripe/Mollie). Verwerk hier de `payment_logs`, `stock_movements` en queue de mails.
- [ ] **View:** Breid de vendor backoffice uit met `Seller\Orders\Index` en `Seller\Orders\Detail` Livewire componenten. Werk de statussen real-time bij (optioneel via Livewire polling of broadcasting) zodat de verkoper direct de betalingsstatus ziet.

---

### Fase 8: Platform Dashboard (Admin)
- [ ] **Logica:** Haal complexe queries op (platformomzet, omzet per vendor, failed payments, actieve tenants) via Livewire Computed Properties in het dashboard component.
- [ ] **View:** Bouw het centrale `Admin\Dashboard` Full-Page Livewire component. Implementeer interactieve datatabellen en grafieken (via Flux UI) die asynchroon filteren op datums of vendor zonder page reloads.

---

### Fase 9: Quality Assurance & Testing flows
- [ ] **Logica:** Schrijf Pest backend tests voor tenant-isolatie en policies. Zorg dat je via Livewire's test helpers test (`livewire()->test(ProductDetail::class)`) in plaats van HTTP requests waar mogelijk.
- [ ] **View/Flow:** Schrijf Livewire integratietests die de volledige gebruikersreis testen (product toevoegen aan cart component -> actie op checkout component -> order generatie) om te bewijzen dat Livewire en de actions naadloos samenwerken.
