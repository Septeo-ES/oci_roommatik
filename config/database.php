<?php
return [
    'driver'    => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
    'port'      => $_ENV['DB_PORT'] ?? 3306,
    'database'  => $_ENV['DB_DATABASE'] ?? 'roommatik',
    'username'  => $_ENV['DB_USERNAME'] ?? 'usuario',
    'password'  => $_ENV['DB_PASSWORD'] ?? 'contraseña',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
