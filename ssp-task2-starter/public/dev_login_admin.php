<?php
// Load bootstrap (fixed path)
require_once __DIR__ . '/../bootstrap.php';

// Load the Admin model (fixed path)
require_once __DIR__ . '/../Models/Admin.php';

$db = Database::pdo();
$email = 'admin@example.com';

/* Find or create an admin user */
$st = $db->prepare("SELECT id, username AS name, email FROM admins WHERE LOWER(email)=LOWER(?) LIMIT 1");
$st->execute([$email]);
$admin = $st->fetch();

if (!$admin) {
    $ins = $db->prepare("INSERT INTO admins (username, email, password) VALUES (?,?,?)");
    $ins->execute(['Admin', $email, password_hash('admin123', PASSWORD_DEFAULT)]);
    $admin = [
        'id'    => (int)$db->lastInsertId(),
        'name'  => 'Admin',
        'email' => $email
    ];
}

/* Impersonate admin */
session_start();
$_SESSION['user'] = [
  'id'    => (int)$admin['id'],
  'name'  => $admin['name'],
  'email' => $admin['email'],
  'role'  => 'admin',
];
session_regenerate_id(true);

/* Go to dashboard */
header('Location: ' . url('dashboard'));
exit;
