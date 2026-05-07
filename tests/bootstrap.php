<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

// Charge l'autoloader Composer avant toute exécution de test.
require dirname(__DIR__) . '/vendor/autoload.php';

// Permet de lire les variables du fichier .env dans l'environnement de test.
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// En mode debug, on crée les fichiers avec des permissions plus ouvertes.
if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
