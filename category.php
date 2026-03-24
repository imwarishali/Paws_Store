<?php
// Read the requested category type from the URL, defaulting to 'all'
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$_GET['category'] = $type;
include 'featured_pets.php';