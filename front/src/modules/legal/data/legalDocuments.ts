export type LegalSlug = 'cgv' | 'confidentialite' | 'mentions' | 'retours'

export type LegalSection = {
  heading: string
  paragraphs: string[]
}

export type LegalDocument = {
  slug: LegalSlug
  path: `/${LegalSlug}`
  navLabel: string
  title: string
  description: string
  sections: LegalSection[]
}

export const legalDocuments: readonly LegalDocument[] = [
  {
    slug: 'cgv',
    path: '/cgv',
    navLabel: 'CGV',
    title: 'Conditions générales de vente',
    description: 'Prix, commande, livraison, rétractation et garanties applicables aux achats Shoppy.',
    sections: [
      {
        heading: 'Objet',
        paragraphs: [
          "Les présentes conditions régissent les ventes conclues sur la boutique Shoppy, une sélection d'objets expédiés depuis la France. Toute commande implique l'acceptation de ce document.",
        ],
      },
      {
        heading: 'Prix et paiement',
        paragraphs: [
          'Les prix sont indiqués en euros, toutes taxes comprises. Le paiement est exigé à la commande, par carte bancaire via Stripe. La commande n\'est confirmée qu\'après encaissement.',
        ],
      },
      {
        heading: 'Commande',
        paragraphs: [
          "Vous pouvez commander en tant qu'invité ou avec un compte. Un récapitulatif vous est présenté avant le paiement. Nous pouvons refuser une commande en cas de stock insuffisant, de soupçon de fraude ou d'erreur manifeste sur le prix.",
        ],
      },
      {
        heading: 'Livraison',
        paragraphs: [
          "Les produits sont expédiés sous 48 heures ouvrées, avec un numéro de suivi. La livraison est offerte à partir de 49 € d'achat. Les délais indiqués sont des estimations, hors week-ends et jours fériés.",
        ],
      },
      {
        heading: 'Droit de rétractation',
        paragraphs: [
          "Vous disposez de 30 jours à compter de la réception pour renvoyer un article, sans avoir à vous justifier. Les retours sont gratuits. Le remboursement intervient après réception du colis dans son état d'origine.",
        ],
      },
      {
        heading: 'Garanties',
        paragraphs: [
          "Les produits bénéficient de la garantie légale de conformité et de la garantie des vices cachés. Une garantie commerciale de deux ans s'y ajoute sur les pièces et la main d'œuvre, sauf usure normale ou mauvaise utilisation.",
        ],
      },
    ],
  },
  {
    slug: 'confidentialite',
    path: '/confidentialite',
    navLabel: 'Confidentialité',
    title: 'Politique de confidentialité',
    description: 'Quelles données Shoppy collecte, pourquoi, et quels droits vous pouvez exercer.',
    sections: [
      {
        heading: 'Données collectées',
        paragraphs: [
          "Nous collectons l'email, le mot de passe (stocké de façon irréversible), l'adresse de livraison et l'historique de commandes lorsque vous créez un compte ou passez commande. Le paiement est traité par Stripe : nous ne stockons pas le numéro de carte.",
        ],
      },
      {
        heading: 'Finalités',
        paragraphs: [
          "Ces données servent à exécuter la commande, sécuriser le compte, vous écrire au sujet d'un achat et, si la loi l'exige, conserver une trace comptable. Nous ne vendons pas vos données et ne faisons pas de profilage publicitaire.",
        ],
      },
      {
        heading: 'Conservation',
        paragraphs: [
          "Les données de compte sont conservées tant que le compte existe. Les commandes sont gardées le temps des obligations légales. Vous pouvez demander la suppression de votre compte, hors pièces que la loi nous impose de conserver.",
        ],
      },
      {
        heading: 'Vos droits',
        paragraphs: [
          "Vous pouvez demander l'accès, la rectification ou la suppression de vos données, ainsi que la limitation ou la portabilité, en écrivant à contact@shoppy.test. Vous pouvez aussi introduire une réclamation auprès de la CNIL.",
        ],
      },
    ],
  },
  {
    slug: 'mentions',
    path: '/mentions',
    navLabel: 'Mentions légales',
    title: 'Mentions légales',
    description: "Éditeur, hébergement et contact de la boutique Shoppy.",
    sections: [
      {
        heading: 'Éditeur',
        paragraphs: [
          "Shoppy est une boutique de démonstration. Elle n'est pas une société immatriculée : les mentions d'identité commerciale ci-dessous décrivent le fonctionnement du site, pas un extrait Kbis.",
          'Directeur de la publication : l\'équipe Shoppy. Contact : contact@shoppy.test.',
        ],
      },
      {
        heading: 'Hébergement',
        paragraphs: [
          "Le site est servi depuis l'infrastructure sur laquelle vous l'avez déployé. Les médias produits sont stockés avec l'application.",
        ],
      },
      {
        heading: 'Propriété intellectuelle',
        paragraphs: [
          "Les textes, visuels et marques présentés sur Shoppy restent la propriété de leurs titulaires. Toute reproduction non autorisée est interdite.",
        ],
      },
    ],
  },
  {
    slug: 'retours',
    path: '/retours',
    navLabel: 'Retours',
    title: 'Livraison et retours',
    description: 'Délais d\'expédition, frais de port et modalités de retour sous 30 jours.',
    sections: [
      {
        heading: 'Expédition',
        paragraphs: [
          "Les commandes partent de France sous 48 heures ouvrées, avec un numéro de suivi. La livraison est offerte à partir de 49 € d'achat. En dessous, les frais de port sont indiqués avant le paiement.",
        ],
      },
      {
        heading: 'Retours',
        paragraphs: [
          "Vous avez 30 jours après réception pour renvoyer un article, gratuitement, s'il est complet et dans un état permettant une remise en vente. Indiquez le numéro de commande à contact@shoppy.test : nous vous transmettons l'étiquette.",
        ],
      },
      {
        heading: 'Remboursement',
        paragraphs: [
          "Le remboursement est effectué sur le moyen de paiement d'origine après réception du colis. Un article endommagé à l'arrivée est remplacé ou remboursé, sans frais.",
        ],
      },
    ],
  },
]

export function legalDocumentByPath(path: string): LegalDocument | undefined {
  return legalDocuments.find((page) => page.path === path)
}
