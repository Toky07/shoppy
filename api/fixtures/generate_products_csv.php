<?php

declare(strict_types=1);

$groups = [
    ['T-shirt', ['Noir', 'Blanc', 'Marine', 'Rouge', 'Sauge', 'Sable', 'Lavande', 'Graphite', 'Ciel', 'Bordeaux', 'Moutarde', 'Olive'], 1999, 40, 'Coton bio, coupe droite.'],
    ['Hoodie', ['Noir', 'Gris chiné', 'Marine', 'Crème', 'Forest', 'Terracotta', 'Slate', 'Violet', 'Sable', 'Ink'], 4999, 25, 'Molleton doux, capuche ajustée.'],
    ['Sweat', ['Noir', 'Blanc', 'Navy', 'Khaki', 'Rose poudré', 'Bleu glacier', 'Charbon', 'Miel'], 3999, 30, 'Col rond, intérieur gratté.'],
    ['Casquette', ['Noire', 'Beige', 'Verte', 'Bleue', 'Rouge', 'Blanche', 'Denim', 'Kaki'], 1799, 50, 'Visière courbée, taille unique.'],
    ['Mug', ['Logo', 'Typo', 'Studio', 'Wave', 'Grid', 'Sun', 'Moon', 'City', 'Forest', 'Ocean'], 1299, 60, 'Céramique 330 ml.'],
    ['Gourde', ['Inox', 'Noir mat', 'Blanc', 'Sauge', 'Sable', 'Sky', 'Coral', 'Slate'], 2499, 35, 'Inox 500 ml, bouchon sport.'],
    ['Tote bag', ['Naturel', 'Noir', 'Marine', 'Rouge', 'Olive', 'Lavande', 'Sable', 'Gris'], 1599, 45, 'Coton canvas 280 g.'],
    ['Carnet', ['Ligné', 'Pointillé', 'Blanc', 'Kraft', 'Nuit', 'Sauge', 'Terracotta', 'Ciel'], 1199, 55, 'A5, 96 pages.'],
    ['Poster', ['Studio', 'Wave', 'Grid', 'Sun', 'Moon', 'City', 'Forest', 'Ocean', 'Type', 'Color'], 1499, 20, 'Affiche 30×40 cm, papier mat.'],
    ['Pack stickers', ['Essentiel', 'Studio', 'Voyage', 'Typo', 'Icones', 'Color'], 699, 80, 'Planche de 12 stickers vinyl.'],
    ['Chaussettes', ['Noires', 'Blanches', 'Marines', 'Rayures', 'Sauge', 'Terracotta'], 999, 70, 'Paire coton, 36-41.'],
    ['Coque', ['Clear', 'Noir', 'Sable', 'Lavande', 'Grid', 'Wave'], 1899, 40, 'Protection iPhone, bords mats.'],
];

$count = array_sum(array_map(static fn (array $group): int => count($group[1]), $groups));
if ($count !== 100) {
    fwrite(STDERR, "Expected 100 products, got {$count}\n");
    exit(1);
}

$handle = fopen(__DIR__.'/products.csv', 'wb');
fputcsv($handle, ['name', 'description', 'priceCents', 'stock', 'image']);

$index = 1;
foreach ($groups as [$kind, $variants, $priceCents, $stock, $blurb]) {
    foreach ($variants as $variant) {
        $name = $kind.' '.$variant;
        $slug = sprintf('%03d-%s', $index, strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name) ?? $name));
        $slug = trim($slug, '-').'.svg';
        fputcsv($handle, [$name, $blurb, $priceCents, $stock, $slug]);
        ++$index;
    }
}

fclose($handle);
echo "Wrote {$count} products to products.csv\n";
