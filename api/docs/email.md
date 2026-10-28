# Email (production)

Les modules publient `EmailRequested` sur le bus. Le module Email les livre via `App\Email\Application\Port\Mailer` (`SymfonyMailer` + Symfony Mailer).

Sans un `MAILER_DSN` SMTP réel, rien ne part : le défaut production est `null://null`.

## Variables

À définir dans le `.env` **à la racine du dépôt** (celui lu par `compose.prod.yaml`), pas dans `api/.env.dev`.

| Variable | Obligatoire | Rôle |
|----------|-------------|------|
| `MAILER_DSN` | oui | Transport SMTP Symfony |
| `MAILER_FROM` | oui | Expéditeur (`noreply@votre-domaine.fr`) |
| `FRONTEND_BASE_URL` | oui | Liens dans les mails (commande, admin) |

`PAYMENT_PROVIDER=local` est refusé en production. Le seed démo aussi.

## Activer

1. Créer un compte SMTP (Mailgun, Postmark, Amazon SES, SMTP du registrar…).
2. Vérifier le domaine d’envoi (SPF, DKIM, éventuellement DMARC) pour que `MAILER_FROM` ne parte pas en spam.
3. Renseigner le DSN Symfony, par exemple :

```env
MAILER_FROM=noreply@votre-domaine.fr
MAILER_DSN=smtp://USER:PASSWORD@smtp.exemple.com:587
FRONTEND_BASE_URL=https://boutique.votre-domaine.fr
```

Encodage d’URL si le mot de passe contient `@`, `:`, `/`. SMTPS (465) : `smtps://USER:PASSWORD@smtp.exemple.com:465`.

4. Relancer l’API pour prendre les variables :

```sh
POSTGRES_PASSWORD=... APP_SECRET=... make prod
```

Ou, si la stack tourne déjà : recréer le service `api` du compose production.

## Ce qui est envoyé

| Déclencheur | Destinataire | Contenu |
|-------------|--------------|---------|
| Compte (vérif, reset, changement d’email) | client | Lien vers `FRONTEND_BASE_URL` |
| Commande placée / annulée | client | Texte ; `recu.txt` à la confirmation |
| Paiement confirmé | client | Reçu de paiement |
| Facture émise (après paiement) | client | HTML + PDF `FAC-YYYY-NNNN.pdf` |
| Facture émise | chaque compte `admin` | HTML + PDF, lien `/admin/orders/…` |
| Newsletter | abonné | Confirmation d’inscription |

Les administrateurs sont les utilisateurs avec le rôle `admin`. Sans admin en base, le mail facture admin n’est pas envoyé.

## Contrôle

- Un paiement Stripe réussi doit déposer un mail facture chez le client **et** chez l’admin.
- Une erreur SMTP (`email_delivery_failed`) remonte pendant la requête qui a déclenché l’envoi : le paiement peut déjà être enregistré.

## Développement

Compose de dev envoie vers Mailpit (`smtp://mailer:1025`), boîte http://localhost:8025. Les tests remplacent le port par `InMemoryMailer`.
