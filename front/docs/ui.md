# UI, i18n, thème, SEO

## Design

`assets/main.css` : Tailwind 4 + jetons sémantiques (`--ui-canvas`, `--ui-accent`, `--ui-danger`, …). Le thème sombre bascule la classe `.dark` sur `<html>` ; les composants utilisent `bg-surface`, `text-muted`, `btn-primary`, pas une ribambelle de `dark:`.

Typo : Inter (corps), Bricolage Grotesque (titres `font-display`).

Composants partagés : `AppLayout` (header, nav mobile, footer, bannière cookies, back-to-top), `AdminLayout`, `PageHeader`, `PageStatus` (loading / error / empty / ready), `EmptyState`, `StatusNotice`, `Pagination`, `ImageCarousel`, `AppImage`, `AppIcon`, `BrandMark`, `AccountMenu`, `ThemeToggle`, `LanguageSwitcher`, `CookieBanner`.

Icônes : sprite interne `shared/ui/icons.ts` + `AppIcon name="cart"`.

## i18n

Locales : `fr` (défaut, source de vérité), `en`. Catalogues JSON `shared/i18n/locales/{fr,en}.json`. Clés en notation point (`orders.payNow`).

`translateMessage` : cherche dans la locale, **fallback `fr`**, puis la clé brute.

`useI18n()` → `{ t, locale, setLocale }`. `document.documentElement.lang` est posé au boot (`index.html` inline script) et à chaque changement.

Dates : `formatDate` + `INTL_LOCALES` (`fr-FR`, `en-GB`).

Les pages légales (`legalDocuments.ts`) sont rédigées en français dans le code ; le sélecteur de langue ne les traduit pas encore.

Pour une nouvelle chaîne : l’ajouter dans **`fr.json` et `en.json`** (même arbre). Un test de catalogues vérifie la parité des clés.

## Thème

`useTheme()` : préférence `light` | `dark` | `system` (`shoppy.theme`). `system` suit `prefers-color-scheme`. Script inline dans `index.html` pour éviter le flash.

## Cookies (consentement)

Bannière au premier visit. Choix `necessary` | `all` (`shoppy.cookie-consent`). `allowsAnalytics` est vrai seulement pour `all`. Aujourd’hui aucun tracker tiers n’est branché ; le flag est prêt.

## SEO

`applyPageMeta` : `document.title`, description, canonical, Open Graph, Twitter card. Utilisé sur la fiche produit et les pages légales. `resetPageMeta` au leave.

L’API sert `GET /sitemap.xml` et `GET /robots.txt` (proxifiés).

## Images

Uploads raster `/uploads/…` : dérivés `thumb` (128), `card` (600), `detail` (1200) via `imageDerivatives.ts` (`fichier.card.jpg`). SVG/GIF : original. `AppImage` pose `srcset` / `sizes`.

## Livraison (affichage)

Devis front aligné sur l’API :

| Méthode | Tarif | Franco |
|---------|-------|--------|
| `standard` | 4,90 € | dès 49 € de marchandise |
| `express` | 9,90 € | jamais |

`quoteShipping` dans `order/domain/ShippingMethod.ts`. Le total réel est celui de la commande API après checkout.
