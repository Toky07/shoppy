# Paiement Stripe (production)

`PAYMENT_PROVIDER=local` (paiement simulé via `POST /payments/complete`) est **refusé** en production. Il faut Stripe.

## Variables

À définir dans le `.env` **à la racine du dépôt** (lu par `compose.prod.yaml`).

| Variable | Obligatoire | Rôle |
|----------|-------------|------|
| `PAYMENT_PROVIDER` | oui | `stripe` (défaut prod) |
| `STRIPE_SECRET_KEY` | oui | Clé **secrète** `sk_live_…` (jamais `pk_…`) |
| `STRIPE_WEBHOOK_SECRET` | oui | Secret d’endpoint `whsec_…` |
| `FRONTEND_BASE_URL` | oui | Origine autorisée des URLs de retour Checkout |
| `APP_ALLOWED_ORIGINS` | recommandé | Même origine que la boutique (CORS + Checkout) |

`STRIPE_SECRET_KEY` et `STRIPE_WEBHOOK_SECRET` vides : le checkout et le webhook échouent.

## Activer

1. Dashboard Stripe → mode **Live** → [clés API](https://dashboard.stripe.com/apikeys) → copier la clé secrète `sk_live_…`.
2. [Webhooks](https://dashboard.stripe.com/webhooks) → Add endpoint :

   - URL : `https://<hôte-boutique>/api/payments/webhooks/stripe`  
     (nginx enlève le préfixe `/api` avant l’API.)
   - Événement : `checkout.session.completed` uniquement (les autres sont ignorés).
   - Copier le *Signing secret* `whsec_…`.

3. Dans le `.env` racine :

```env
PAYMENT_PROVIDER=stripe
STRIPE_SECRET_KEY=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
FRONTEND_BASE_URL=https://boutique.votre-domaine.fr
APP_ALLOWED_ORIGINS=https://boutique.votre-domaine.fr
```

`successUrl` / `cancelUrl` du checkout doivent avoir **exactement** cette origine (schéma + hôte + port). Sinon `400` (`successUrl` / `cancelUrl`).

4. Relancer :

```sh
POSTGRES_PASSWORD=... APP_SECRET=... make prod
```

## Flux

1. Le client démarre le paiement : `POST /payments/checkout` → session Stripe, `redirectUrl`.
2. Stripe encaisse, puis appelle `POST /payments/webhooks/stripe` (signature `Stripe-Signature`).
3. L’API marque le paiement, la commande, émet la facture et envoie les emails.
4. Au retour boutique (`?payment=success`), `POST /payments/confirm` revérifie la session (filet si le webhook est en retard).

`POST /payments/complete` (paiement local) est bloqué dès que `PAYMENT_PROVIDER=stripe` ou `APP_ENV=prod`.

Le webhook n’exige pas de session utilisateur. Seul le propriétaire de la commande peut `checkout` / `confirm`.

## Contrôle

- Dashboard → Developers → Webhooks : livraisons `checkout.session.completed` en `2xx`.
- Après un paiement test live (petit montant) : commande `paid`, PDF `GET /invoices/by-order/{orderId}/pdf`, emails client + admin.
- `409 payment_not_payable` : commande déjà payée ou annulée.
- `400` sur le webhook : signature invalide ou montant Stripe ≠ montant du paiement.

## Développement

Clés **test** (`sk_test_…`) dans `api/.env`. Recréer l’API après changement. Pour le webhook local :

```sh
make stripe-listen
```

Copier le `whsec_…` affiché dans `STRIPE_WEBHOOK_SECRET` (.env). Le front confirme aussi la session au retour de Checkout.
