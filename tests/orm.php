<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

// Même bootstrap que l'application, afin de charger l'environnement de test.
require dirname(__DIR__) . '/vendor/autoload.php';

// Les tests ORM ont besoin des variables d'environnement définies dans .env.
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// On démarre le kernel en environnement dev pour récupérer l'EntityManager.
$kernel = new App\Kernel('dev', true);
$kernel->boot();

// Retourne l'EntityManager utilisé par les tests de persistance.
return $kernel->getContainer()->get('doctrine')->getManager();
