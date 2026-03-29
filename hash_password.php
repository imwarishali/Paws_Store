<?php
// one_time_hash_script.php
require_once 'db.php';

// --- CONFIGURE THESE TWO VALUES ---
$username_to_update = 'admin';
$plain_password = 'new_secure_password';
// ------------------------------------

$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = ?");
$stmt->execute([$hashed_password, $username_to_update]);

echo "Password for '{$username_to_update}' has been securely updated.";
