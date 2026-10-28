# TODO

Audit du 27 octobre 2026.

## Fonctionnalités

### Achat, livraison, commande

- [ ] TVA / taxes (le total est une somme de lignes, affiché « TTC » sans calcul)
- [ ] Codes promo
- [ ] Retour visuel Stripe (`?payment=success` / `?payment=cancel` sont dans l’URL, `OrderDetailPage` ne s’en sert pas)
- [ ] Statuts d’expédition et suivi colis (commande : `pending` / `paid` / `cancelled` seulement)
- [ ] Email d’expédition, quand le suivi existera
- [ ] Facture PDF (la confirmation joint encore `recu.txt`)
- [ ] Remboursement (paiement : `pending` / `completed` / `cancelled` seulement)
- [ ] Filtres d’historique (statut, date) côté client et admin
- [ ] Annulation admin (le détail admin ne propose que « Marquer comme payée »)
- [ ] Afficher l’email client sur la fiche commande admin (la liste l’a, le détail affiche encore l’UUID)

## Qualité

- [ ] Parcours e2e (auth, achat, admin) — Playwright ne couvre qu’un smoke catalogue, et pas en CI
- [ ] Lint front dans la CI (`npm run lint` existe, le workflow ne le lance pas)
