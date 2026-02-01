<?php
/**
 * H&M Immobilier — API : Biens en vedette (3 derniers actifs)
 * Retourne du JSON. Utilisé par index.html via fetch().
 * Pas d'authentification nécessaire (données publiques).
 */

require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/PropertyModel.php';

// Pas de session nécessaire
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=300'); // Cache 5 minutes

$model  = new PropertyModel();
$result = $model->findPublicAll([], 1, 3); // 3 derniers biens actifs

$output = [];
foreach ($result['properties'] as $p) {
    $details = [];
    if ($p['bedrooms'])  $details[] = $p['bedrooms'] . ' chambres';
    if ($p['bathrooms']) $details[] = $p['bathrooms'] . ' SdB';
    if ($p['surface'])   $details[] = number_format((float)$p['surface'], 0) . 'm²';
    if ($p['has_pool'])  $details[] = 'piscine';

    $output[] = [
        'id'              => (int)$p['id'],
        'title'           => $p['title'],
        'location'        => $p['location'],
        'property_type'   => $p['property_type'],
        'price_formatted' => number_format((float)$p['price'], 0, ',', ' '),
        'details'         => implode(', ', $details),
        'main_image'      => $p['main_image'],
    ];
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
