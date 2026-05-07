<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        // Chaque test repart d'un client HTTP neuf pour éviter les effets de bord
        // entre deux scénarios fonctionnels.
        $this->client = static::createClient();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        // Raccourci pour accéder rapidement à Doctrine depuis les tests.
        return $this->service(EntityManagerInterface::class);
    }

    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    protected function service(string $id): object
    {
        // Le conteneur de test donne accès aux services comme en runtime.
        return $this->client->getContainer()->get($id);
    }

    protected function get(string $uri, array $parameters = []): Crawler
    {
        // Wrapper simple autour d'une requête GET pour alléger les tests.
        return $this->client->request('GET', $uri, $parameters);
    }

    protected function login(string $email = 'user+0@email.com'): void
    {
        // Connexion directe via le token de sécurité, sans passer par le formulaire.
        $user = $this->service(EntityManagerInterface::class)->getRepository(User::class)->findOneByEmail($email);

        $this->client->loginUser($user);
    }
}
