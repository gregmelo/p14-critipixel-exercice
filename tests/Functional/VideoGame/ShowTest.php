<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class ShowTest extends FunctionalTestCase
{
    public function testShouldShowVideoGame(): void
    {
        // Le slug correspond à la route de détail du jeu vidéo.
        $this->get('/jeu-video-0');
        self::assertResponseIsSuccessful();

        // On vérifie que le titre affiché correspond bien à la fiche demandée.
        self::assertSelectorTextContains('h1', 'Jeu vidéo 0');
    }
}
