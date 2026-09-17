<?php

declare(strict_types=1);

/**
 * @param list<string> $photoIds
 * @return list<string>
 */
function unsplashPhotos(array $photoIds): array
{
    return array_map(
        static fn (string $id): string => 'https://images.unsplash.com/'.$id.'?auto=format&fit=crop&w=800&h=800&q=70',
        array_values(array_unique($photoIds)),
    );
}

$apparel = unsplashPhotos([
    'photo-1521572163474-6864f9cf17ab',
    'photo-1583743814966-8936f5b7be1a',
    'photo-1571945153237-4929e783af4a',
    'photo-1503342217505-b0a15ec3261c',
    'photo-1512436991641-6745cdb1723f',
    'photo-1523381210434-271e8be1f52b',
    'photo-1525507119028-ed4c629a60a3',
    'photo-1529374255404-311a2a4f1fd9',
    'photo-1532453288672-3a27e9be9efd',
    'photo-1551488831-00ddcb6c6bd3',
    'photo-1554568218-0f1715e72254',
    'photo-1581655353564-df123a1eb820',
    'photo-1586790170083-2f9ceadc732d',
    'photo-1596755094514-f87e34085b2c',
    'photo-1603252109303-2751441dd157',
    'photo-1618354691438-25bc04584c23',
    'photo-1622445275463-afa2ab738c34',
    'photo-1552374196-1ab2a1c593e8',
    'photo-1558171813-4c088753af8f',
    'photo-1445205170230-053b83016050',
    'photo-1483985988355-763728e1935b',
    'photo-1490481651871-ab68de25d43d',
    'photo-1509631179647-0177331693ae',
]);
$knit = unsplashPhotos([
    'photo-1556821840-3a63f95609a7',
    'photo-1620799140408-edc6dcb6d633',
    'photo-1544441893-675973e31985',
    'photo-1556906781-9a412961c28c',
    'photo-1578587018452-892bacefd3f2',
    'photo-1556905055-8f358a7a47b2',
    'photo-1509942774463-acf339cf87d5',
    'photo-1556909114-f6e7ad7d3136',
    'photo-1578932750294-f5075e85f44a',
    'photo-1591047139829-d91aecb6caea',
    'photo-1434389677669-e08b4cac3105',
    'photo-1614676471928-2ed0ad1061a4',
    'photo-1620799140188-3b2a02fd9a77',
    'photo-1551028719-00167b16eac5',
]);
$caps = unsplashPhotos([
    'photo-1521369909029-2afed882baee',
    'photo-1575428652377-a2d80e2277fc',
    'photo-1534215754734-18e55d13e346',
    'photo-1576871337622-98d48d1cf531',
    'photo-1514989940723-e8e51635b782',
    'photo-1560769629-975ec94e6a86',
]);
$mugs = unsplashPhotos([
    'photo-1514228742587-6b1558fcca3d',
    'photo-1509042239860-f550ce710b93',
    'photo-1495474472287-4d71bcdd2085',
    'photo-1517256064527-09c73fc73e38',
    'photo-1572116469696-31de0f17cc34',
    'photo-1544787219-7f47ccb76574',
    'photo-1577937927133-66ef06acdf18',
]);
$bottles = unsplashPhotos([
    'photo-1548839140-29a749e1cf4d',
    'photo-1575367439058-6096bb9cf5e2',
    'photo-1589365278144-c9e705f843ba',
    'photo-1514228742587-6b1558fcca3d',
    'photo-1572116469696-31de0f17cc34',
    'photo-1544787219-7f47ccb76574',
]);
$bags = unsplashPhotos([
    'photo-1591561954557-26941169b49e',
    'photo-1553062407-98eeb64c6a62',
    'photo-1622560480605-d83c853bc5c3',
    'photo-1487412720507-e7ab37603c6f',
    'photo-1441986300917-64674bd600d8',
    'photo-1492707892479-7bc8d5a4ee93',
    'photo-1475180098004-ca77a66827be',
]);
$notebooks = unsplashPhotos([
    'photo-1531346878377-a5be20888e57',
    'photo-1517842645767-c639042777db',
    'photo-1455390582262-044cdead277a',
    'photo-1516414447565-b14be0adf13e',
    'photo-1471107340929-a87cd0f5b5f3',
    'photo-1506784365847-bbad939e9335',
    'photo-1481627834876-b7833e8f5570',
]);
$posters = unsplashPhotos([
    'photo-1541961017774-22349e4a1262',
    'photo-1513364776144-60967b0f800f',
    'photo-1460661419201-fd4cecdf8a8b',
    'photo-1579783902614-a3fb3927b6a5',
    'photo-1445205170230-053b83016050',
    'photo-1483985988355-763728e1935b',
    'photo-1490481651871-ab68de25d43d',
]);
$stickers = unsplashPhotos([
    'photo-1611532736597-de2d4265fba3',
    'photo-1608889825103-eb5ed706fc64',
    'photo-1612198188060-c7c2a3b66eae',
    'photo-1608889825205-eebdb9fc5806',
    'photo-1541961017774-22349e4a1262',
    'photo-1513364776144-60967b0f800f',
]);
$socks = unsplashPhotos([
    'photo-1582967788606-a171c1080cb0',
    'photo-1514989940723-e8e51635b782',
    'photo-1560769629-975ec94e6a86',
    'photo-1460353581641-37baddab0fa2',
    'photo-1542291026-7eec264c27ff',
    'photo-1549298916-b41d501d3772',
]);
$phones = unsplashPhotos([
    'photo-1601784551446-20c9e07cdbdb',
    'photo-1592899677977-9c10ca588bbd',
    'photo-1511707171634-5f897ff02aa9',
    'photo-1556656793-08538906a9f8',
    'photo-1510557880182-3d4d3cba35a5',
    'photo-1598327105666-5b89351aff97',
    'photo-1580910051074-3eb694886505',
]);

$groups = [
    ['T-shirt', ['Noir', 'Blanc', 'Marine', 'Rouge', 'Sauge', 'Sable', 'Lavande', 'Graphite', 'Ciel', 'Bordeaux', 'Moutarde', 'Olive'], 1999, 40, 'Coton bio, coupe droite, col rond. Un basique du quotidien, lavable à 30 °.', $apparel],
    ['Hoodie', ['Noir', 'Gris chiné', 'Marine', 'Crème', 'Forest', 'Terracotta', 'Slate', 'Violet', 'Sable', 'Ink'], 4999, 25, 'Molleton doux, capuche ajustée et poche kangourou. Chaud sans être lourd.', $knit],
    ['Sweat', ['Noir', 'Blanc', 'Navy', 'Khaki', 'Rose poudré', 'Bleu glacier', 'Charbon', 'Miel'], 3999, 30, 'Col rond, intérieur gratté. Coupe décontractée pour le studio comme la ville.', $knit],
    ['Casquette', ['Noire', 'Beige', 'Verte', 'Bleue', 'Rouge', 'Blanche', 'Denim', 'Kaki'], 1799, 50, 'Visière courbée, taille unique, sangle ajustable. Broderie ton sur ton.', $caps],
    ['Mug', ['Logo', 'Typo', 'Studio', 'Wave', 'Grid', 'Sun', 'Moon', 'City', 'Forest', 'Ocean'], 1299, 60, 'Céramique 330 ml, anse confortable, passe au lave-vaisselle et au micro-ondes.', $mugs],
    ['Gourde', ['Inox', 'Noir mat', 'Blanc', 'Sauge', 'Sable', 'Sky', 'Coral', 'Slate'], 2499, 35, 'Inox 500 ml, bouchon sport, garde au frais 12 h. Idéale bureau et vélo.', $bottles],
    ['Tote bag', ['Naturel', 'Noir', 'Marine', 'Rouge', 'Olive', 'Lavande', 'Sable', 'Gris'], 1599, 45, 'Coton canvas 280 g, anses longues, format A4. Pour le marché ou le laptop.', $bags],
    ['Carnet', ['Ligné', 'Pointillé', 'Blanc', 'Kraft', 'Nuit', 'Sauge', 'Terracotta', 'Ciel'], 1199, 55, 'A5, 96 pages, papier 90 g. Couverture souple et élastique de fermeture.', $notebooks],
    ['Poster', ['Studio', 'Wave', 'Grid', 'Sun', 'Moon', 'City', 'Forest', 'Ocean', 'Type', 'Color'], 1499, 20, 'Affiche 30×40 cm, papier mat 200 g. Livrée roulée, cadre non inclus.', $posters],
    ['Pack stickers', ['Essentiel', 'Studio', 'Voyage', 'Typo', 'Icones', 'Color'], 699, 80, 'Planche de 12 stickers vinyl mat. Résistants à l’eau, pour laptop et gourde.', $stickers],
    ['Chaussettes', ['Noires', 'Blanches', 'Marines', 'Rayures', 'Sauge', 'Terracotta'], 999, 70, 'Paire coton peigné, 36-41, renforts talon et pointe. Pack unitaire.', $socks],
    ['Coque', ['Clear', 'Noir', 'Sable', 'Lavande', 'Grid', 'Wave'], 1899, 40, 'Coque iPhone, bords mats, protection antichoc. Découpe précise des boutons.', $phones],
];

$count = array_sum(array_map(static fn (array $group): int => count($group[1]), $groups));
if ($count !== 100) {
    fwrite(STDERR, "Expected 100 products, got {$count}\n");
    exit(1);
}

$handle = fopen(__DIR__.'/products.csv', 'wb');
fputcsv($handle, ['name', 'description', 'priceCents', 'stock', 'images']);

$index = 0;
foreach ($groups as [$kind, $variants, $priceCents, $stock, $blurb, $photos]) {
    if (count($photos) < 5) {
        fwrite(STDERR, "Need at least 5 photos for {$kind}\n");
        exit(1);
    }

    foreach ($variants as $variantIndex => $variant) {
        $images = [];
        for ($offset = 0; $offset < 5; ++$offset) {
            $images[] = $photos[($variantIndex + $offset) % count($photos)];
        }

        if (count(array_unique($images)) !== 5) {
            fwrite(STDERR, "Duplicate gallery for {$kind} {$variant}\n");
            exit(1);
        }

        fputcsv($handle, [
            $kind.' '.$variant,
            $blurb,
            $priceCents,
            $stock,
            implode('|', $images),
        ]);
        ++$index;
    }
}

fclose($handle);
echo "Wrote {$index} products to products.csv\n";
