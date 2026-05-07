<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class LoginTest extends FunctionalTestCase
{
    public function testThatLoginShouldSucceeded(): void
    {
        // On charge la page de connexion avant d'envoyer le formulaire.
        $this->get('/auth/login');

        // Un mot de passe valide doit ouvrir une session authentifiée.
        $this->client->submitForm('Se connecter', [
            'email' => 'user+1@email.com',
            'password' => 'password',
        ]);

        // Le service de sécurité permet de vérifier l'état d'authentification.
        $authorizationChecker = $this->service(AuthorizationCheckerInterface::class);

        self::assertTrue($authorizationChecker->isGranted('IS_AUTHENTICATED'));

        // Après déconnexion, l'utilisateur ne doit plus être reconnu comme connecté.
        $this->get('/auth/logout');

        self::assertFalse($authorizationChecker->isGranted('IS_AUTHENTICATED'));
    }

    public function testThatLoginShouldFailed(): void
    {
        // Même scénario, mais avec un mot de passe invalide.
        $this->get('/auth/login');

        $this->client->submitForm('Se connecter', [
            'email' => 'user+1@email.com',
            'password' => 'fail',
        ]);

        // Aucun token d'authentification ne doit être créé.
        $authorizationChecker = $this->service(AuthorizationCheckerInterface::class);

        self::assertFalse($authorizationChecker->isGranted('IS_AUTHENTICATED'));
    }
}
