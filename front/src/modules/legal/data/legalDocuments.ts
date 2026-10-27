import type { Locale } from '@/shared/i18n/locale'

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
          "Nous collectons l'email, le mot de passe (stocké de façon irréversible), l'adresse de livraison et l'historique de commandes lorsque vous créez un compte ou passez commande. L'email fourni pour la lettre d'information est conservé tant que vous restez inscrit. Le paiement est traité par Stripe : nous ne stockons pas le numéro de carte.",
        ],
      },
      {
        heading: 'Finalités',
        paragraphs: [
          "Ces données servent à exécuter la commande, sécuriser le compte, vous écrire au sujet d'un achat, envoyer la lettre d'information si vous vous y êtes inscrit, et, si la loi l'exige, conserver une trace comptable. Nous ne vendons pas vos données et ne faisons pas de profilage publicitaire.",
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
      {
        heading: 'Cookies',
        paragraphs: [
          'Les cookies strictement nécessaires (session, panier, sécurité) font fonctionner la boutique et ne demandent pas votre consentement.',
          "Aucun cookie de mesure d'audience ou publicitaire n'est déposé tant que vous n'acceptez pas. Vous pouvez changer d'avis à tout moment via le lien Cookies du pied de page.",
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

const legalDocumentsEn: readonly LegalDocument[] = [
  {
    slug: 'cgv',
    path: '/cgv',
    navLabel: 'Terms',
    title: 'Terms of sale',
    description: 'Price, order, delivery, withdrawal and warranties that apply to Shoppy purchases.',
    sections: [
      {
        heading: 'Purpose',
        paragraphs: [
          'These terms govern sales on the Shoppy shop, a selection of objects shipped from France. Placing an order means you accept this document.',
        ],
      },
      {
        heading: 'Price and payment',
        paragraphs: [
          'Prices are in euros, tax included. Payment is due at checkout by card via Stripe. The order is confirmed only after payment is collected.',
        ],
      },
      {
        heading: 'Order',
        paragraphs: [
          'You can order as a guest or with an account. A summary is shown before payment. We may refuse an order if stock is insufficient, fraud is suspected, or the price is clearly wrong.',
        ],
      },
      {
        heading: 'Delivery',
        paragraphs: [
          'Products ship within 48 business hours, with tracking. Delivery is free from €49. Stated times are estimates, excluding weekends and public holidays.',
        ],
      },
      {
        heading: 'Right of withdrawal',
        paragraphs: [
          'You have 30 days from receipt to return an item, without giving a reason. Returns are free. The refund is issued after we receive the parcel in its original condition.',
        ],
      },
      {
        heading: 'Warranties',
        paragraphs: [
          'Products benefit from the legal warranty of conformity and hidden defects. A two-year commercial warranty on parts and labour is added, except for normal wear or misuse.',
        ],
      },
    ],
  },
  {
    slug: 'confidentialite',
    path: '/confidentialite',
    navLabel: 'Privacy',
    title: 'Privacy policy',
    description: 'What data Shoppy collects, why, and which rights you can exercise.',
    sections: [
      {
        heading: 'Data we collect',
        paragraphs: [
          'We collect your email, password (stored irreversibly), delivery address and order history when you create an account or place an order. The email given for the newsletter is kept while you remain subscribed. Payment is handled by Stripe: we do not store card numbers.',
        ],
      },
      {
        heading: 'Purposes',
        paragraphs: [
          'This data is used to fulfil the order, secure the account, write to you about a purchase, send the newsletter if you subscribed, and keep accounting records when the law requires it. We do not sell your data or build advertising profiles.',
        ],
      },
      {
        heading: 'Retention',
        paragraphs: [
          'Account data is kept while the account exists. Orders are kept for as long as legal duties require. You can ask us to delete your account, except records we must keep.',
        ],
      },
      {
        heading: 'Your rights',
        paragraphs: [
          'You can ask for access, correction or deletion of your data, as well as restriction or portability, by writing to contact@shoppy.test. You can also lodge a complaint with the CNIL.',
        ],
      },
      {
        heading: 'Cookies',
        paragraphs: [
          'Strictly necessary cookies (session, cart, security) make the shop work and do not require your consent.',
          'No audience-measurement or advertising cookies are set until you accept. You can change your mind at any time via the Cookies link in the footer.',
        ],
      },
    ],
  },
  {
    slug: 'mentions',
    path: '/mentions',
    navLabel: 'Legal notice',
    title: 'Legal notice',
    description: 'Publisher, hosting and contact for the Shoppy shop.',
    sections: [
      {
        heading: 'Publisher',
        paragraphs: [
          'Shoppy is a demonstration shop. It is not a registered company: the identity notes below describe how the site works, not a company extract.',
          'Publication director: the Shoppy team. Contact: contact@shoppy.test.',
        ],
      },
      {
        heading: 'Hosting',
        paragraphs: [
          'The site is served from the infrastructure you deployed it on. Product media is stored with the application.',
        ],
      },
      {
        heading: 'Intellectual property',
        paragraphs: [
          'Texts, visuals and trademarks on Shoppy remain the property of their owners. Unauthorised reproduction is prohibited.',
        ],
      },
    ],
  },
  {
    slug: 'retours',
    path: '/retours',
    navLabel: 'Returns',
    title: 'Delivery and returns',
    description: 'Shipping times, delivery fees and how to return an item within 30 days.',
    sections: [
      {
        heading: 'Shipping',
        paragraphs: [
          'Orders leave France within 48 business hours, with tracking. Delivery is free from €49. Below that, shipping fees are shown before payment.',
        ],
      },
      {
        heading: 'Returns',
        paragraphs: [
          'You have 30 days after receipt to return an item, free of charge, if it is complete and in a condition that allows resale. Give your order number to contact@shoppy.test: we will send the label.',
        ],
      },
      {
        heading: 'Refund',
        paragraphs: [
          'The refund is issued to the original payment method after we receive the parcel. An item damaged on arrival is replaced or refunded, at no cost.',
        ],
      },
    ],
  },
]

export function legalDocumentsFor(locale: Locale): readonly LegalDocument[] {
  return locale === 'en' ? legalDocumentsEn : legalDocuments
}

export function legalDocumentByPath(path: string, locale: Locale = 'fr'): LegalDocument | undefined {
  return legalDocumentsFor(locale).find((page) => page.path === path)
}
