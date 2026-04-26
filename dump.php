<?php
require_once('c:/xampp/htdocs/heatproofing/wp-load.php');
$opts = get_option('hp_dynamic_products', []);
file_put_contents('db_dump.json', wp_json_encode($opts, JSON_PRETTY_PRINT));
echo "Dump details success";
