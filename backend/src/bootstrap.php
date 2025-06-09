<?php
/**
 * Initializes database connection using Eloquent ORM.
 * - Uses SQLite (data/php-chat.db).
 * - Sets Eloquent as global.
 * - Boots Eloquent.
 */

use Illuminate\Database\Capsule\Manager as Capsule;

$dbPath = __DIR__ . '/../data/php-chat.db';

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => realpath($dbPath),
    'prefix'    => '',
]);

// Make Eloquent globally available
$capsule->setAsGlobal();

// Boot Eloquent
$capsule->bootEloquent();