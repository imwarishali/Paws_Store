<?php

/**
 * Sitemap XML Generator
 * Run this periodically to generate sitemap.xml
 */

require_once 'db.php';

header('Content-Type: application/xml; charset=utf-8');

$base_url = 'https://pawsstore.in'; // Change this to your domain

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static pages
$static_pages = [
    'index.php' => '1.0',
    'category.php' => '0.9',
    'cart.php' => '0.8',
    'profile.php' => '0.7',
    'testimonials.php' => '0.8',
    'contact.php' => '0.9',
    'privacy-policy.php' => '0.7',
    'terms-conditions.php' => '0.7',
    'refund-policy.php' => '0.7',
    'order_history.php' => '0.6',
    'FAQ.php' => '0.8'
];

foreach ($static_pages as $page => $priority) {
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . $base_url . "/" . $page . "</loc>\n";
    $xml .= "    <priority>" . $priority . "</priority>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "  </url>\n";
}

// Dynamic pet pages
try {
    $stmt = $pdo->query("SELECT id FROM pets ORDER BY id");
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pets as $pet) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . $base_url . "/pet_details.php?id=" . $pet['id'] . "</loc>\n";
        $xml .= "    <priority>0.8</priority>\n";
        $xml .= "    <changefreq>monthly</changefreq>\n";
        $xml .= "  </url>\n";
    }
} catch (Exception $e) {
    // Silently fail if pets table doesn't exist
}

// Dynamic category pages
try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM pets");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as $cat) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . $base_url . "/category.php?name=" . urlencode($cat['category']) . "</loc>\n";
        $xml .= "    <priority>0.9</priority>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "  </url>\n";
    }
} catch (Exception $e) {
    // Silently fail
}

$xml .= '</urlset>';

echo $xml;
exit;
