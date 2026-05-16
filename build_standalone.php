<?php
define('WP_USE_THEMES', false);
require_once( dirname(__FILE__) . '/../../../wp-load.php' );

$html = hp_estimator_html();
$css = file_get_contents(dirname(__FILE__) . '/assets/css/style.css');
$js = file_get_contents(dirname(__FILE__) . '/assets/js/script.js');

// Since this is standalone, we inject hpSettings manually into the HTML before the script
$hp_prices = array(
    'coating'       => get_option('hp_coating_price', 7500),
    'chipsSpecial'  => get_option('hp_chips_special_price', 2800),
    'chipsSuper'    => get_option('hp_chips_super_price', 2300),
    'chipsEconomy'  => get_option('hp_chips_economy_price', 1800),
    'tileMat'       => get_option('hp_tile_mat_price', 300),
    'tileSummer'    => get_option('hp_tile_summer_price', 250),
    'ajaxUrl'       => '', // No Ajax in standalone
    'whatsappNumber'=> get_option('hp_whatsapp_number', '923006786890')
);

$settings_js = "var hpSettings = " . json_encode($hp_prices) . ";";

$full_html = '<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ہیٹ پروفنگ ایسٹیمیٹر | Heatproofing Estimator</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
    body, button, input, textarea, select, h1, h2, h3, h4, h5, h6, p, ul, li, a, span, div {
        font-family: "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", Arial, sans-serif !important;
    }
    body { background-color: #f0f2f5; margin: 0; padding: 20px 10px; color: #333; line-height: 1.8; }
    ' . $css . '
    </style>
</head>
<body class="">
    ' . $html . '
    <script>
    ' . $settings_js . '
    ' . $js . '
    </script>
</body>
</html>';

$target = 'c:/xampp/htdocs/heatproofing/Heatproofing-estimator/index.html';
file_put_contents($target, $full_html);
echo "Successfully generated $target\n";
