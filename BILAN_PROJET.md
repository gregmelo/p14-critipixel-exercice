# BILAN DE PROJET — CritiPixel
## Session de travail : Mise en place de la qualité de code et de l'intégration continue

---

## 1. RÉSUMÉ DU PROJET

**Nom du projet :** CritiPixel  
**Objectif :** Plateforme permettant aux utilisateurs de noter et de critiquer des jeux vidéo  
**Technologies :** Symfony 6.4, PHP 8.2, MySQL 8.0, GitHub Actions

### Contexte
Le projet était fonctionnellement complet mais **dépourvu de contrôle qualité automatisé**. L'objectif de cette session a été d'implémenter une pipeline complète de garantie de qualité du code et de déploiement continu.

---

## 2. LIVRABLES PRODUITS

### 2.1 Analyse Statique des Types (PHPStan)
- ✅ Installation de PHPStan niveau 6 avec extensions Symfony et Doctrine
- ✅ **24 erreurs de typage détectées et corrigées** (incohérences type/BD, collection génériques, etc.)
- ✅ Configuration `phpstan.neon` optimisée pour le projet
- ✅ Zéro erreur PHPStan sur 42 fichiers analysés

**Erreurs corrigées incluant :**
- Contrôle de type UserInterface → User dans les controllers
- Types génériques manquants sur les Collections Doctrine
- Incohérences types Python/BD PostgreSQL
- Voter génériques Symfony non typés

### 2.2 Formatage de Code (PHP CS Fixer)
- ✅ Installation de PHP CS Fixer v3.75.0
- ✅ Configuration `php-cs-fixer.dist.php` avec règles PSR-12/Symfony
- ✅ **51 fichiers automatiquement reformatés**
- ✅ Ajout de `declare(strict_types=1)` obligatoire sur tous fichiers
- ✅ Normalisation des fins de ligne (LF partout)

**Améliorations appliquées :**
- Imports triés et nettoyés (15 fichiers)
- Virgules finales multi-lignes (35 fichiers)
- Espacement d'opérateurs unifié (8 fichiers)
- Retrait des imports inutilisés (5 fichiers)

### 2.3 Intégration Continue (GitHub Actions)
- ✅ Workflow CI complet `.github/workflows/ci.yml`
- ✅ Pipeline multi-étapes avec isolation MySQL 8.0
- ✅ Exécution automatique sur PR vers `main`
- ✅ **Tous les checks passent : 0 erreur** ✅

**Étapes CI implémentées :**
1. Setup PHP 8.2 + extensions (mysql, intl, mbstring)
2. Cache Composer
3. Installation dépendances (`composer install`)
4. Compilation SASS/assets (`sass:build`)
5. Préparation BD de test MySQL + migrations + fixtures
6. Exécution PHPUnit (tests fonctionnels + unitaires)
7. Analyse PHPStan niveau 6
8. Vérification PHP CS Fixer (dry-run)

### 2.4 Correction et Alignement BD MySQL (Local ↔ CI)
- ✅ **Problème découvert :** Divergence environnement local (MySQL) vs CI (PostgreSQL)
- ✅ Migration PostgreSQL-spécifique supprimée
- ✅ Migration MySQL complète régénérée (`Version20260507090433.php`)
- ✅ Syntaxe MySQL correcte : `INT AUTO_INCREMENT`, `TEXT`, `DATETIME`
- ✅ **Workflow CI basculé de PostgreSQL 16 → MySQL 8.0** pour alignement
- ✅ BD de test crée + migre + fixture chargée sans erreur en local
- ✅ **Tests passent en local ET en pipeline** ✅

### 2.5 Rapports de Corrections
- ✅ `phpstan-rapport-corrections.txt` : détail des 24 erreurs PHPStan (24 catégories d'erreurs)
- ✅ `php-cs-fixer-rapport-corrections.txt` : détail des 51 fichiers reformatés

---

## 3. ÉTAPES FRANCHIES

### Phase 1 : Installation et Configuration des Outils
**Objectif :** Mettre en place les outils de qualité

```bash
# PHPStan
composer require --dev phpstan/phpstan phpstan/phpstan-symfony phpstan/phpstan-doctrine

# PHP CS Fixer
composer require --dev friendsofphp/php-cs-fixer
```

**Fichiers créés :**
- `phpstan.neon` — Configuration PHPStan
- `.php-cs-fixer.dist.php` — Configuration PHP CS Fixer

**Apprentissage clé :** Les extensions Symfony et Doctrine pour PHPStan sont **essentielles** pour valider les entités ORM et les services de conteneur.

---

### Phase 2 : Exécution de PHPStan et Correction des Erreurs
**Commande :**
```bash
vendor/bin/phpstan analyse --memory-limit=512M
```

**Résultat initial :** 24 erreurs sur 42 fichiers

**Corrections appliquées :**

| Catégorie | Fichiers | Solution |
|-----------|----------|----------|
| **argument.type** | 4 | Ajout contrôle `instanceof` avant setUser() |
| **missingType.generics** | 8 | Ajout types génériques `Collection<int, Entity>` |
| **doctrine.columnType** | 2 | Changement `DateTimeInterface` → `DateTimeImmutable` |
| **missingType.iterableValue** | 6 | Ajout `@return Type[]` sur getters/setters |
| **argument.templateType** | 2 | Suppression contrainte `T of array` → `T` dans helpers |
| **return.type** | 1 | Ajout return type `void` sur configureOptions() |
| **method.childReturnType** | 1 | Correction types surcharge méthode |

**Résultat final :** 0 erreur ✅

---

### Phase 3 : Exécution de PHP CS Fixer et Reformatage
**Commandes :**
```bash
# Prévisualisation
vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php

# Application
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php
```

**Résultat initial :** 51 fichiers nécessitant reformatage

**Corrections appliquées :**
- ✅ `declare(strict_types=1)` ajouté (~30 fichiers)
- ✅ Virgules finales multi-lignes (~35 fichiers)
- ✅ Espacement d'opérateurs unifié (~8 fichiers)
- ✅ Imports inutilisés supprimés (~5 fichiers)
- ✅ Types de retour setters unifiés (Filter → self, etc.)

**Défi rencontré :** Problème de fins de ligne CRLF/LF entre Windows et Linux CI.  
**Solution :** Ajout de `->setLineEnding("\n")` dans la config PHP CS Fixer pour forcer LF partout.

**Résultat final :** 0 correction restante ✅

---

### Phase 4 : Mise en Place de la Pipeline GitHub Actions
**Fichier créé :** `.github/workflows/ci.yml`

**Architecture :**
```yaml
Checkout → PHP 8.2 + ext → Composer Cache
         → composer install
         → SASS build (assets)
         → PostgreSQL service + migration + fixtures
         → PHPUnit tests
         → PHPStan analyse
         → PHP CS Fixer verify
```

**Défi majeur 1 :** Syntaxe YAML indentation incorrecte  
**Solution :** Restructuration avec `services`, `steps` correctement imbriqués sous `jobs.ci`

**Défi majeur 2 :** Migration MySQL incompatible avec PostgreSQL  
**Solution :** Réécriture complète migration en SQL PostgreSQL natif avec colonnes `number_of_ratings_per_value_*` correctes

**Défi majeur 3 :** Tests fonctionnels échouaient, boutons non trouvés  
**Solution :** Ajout étape `php bin/console sass:build` pour compiler assets SCSS (Dart Sass téléchargé automatiquement)

**Résultat final :** Pipeline complètement verte ✅

```bash
# Voir le statut de la pipeline
# https://github.com/gregmelo/p14-critipixel-exercice/actions
```

---

### Phase 5 : Découverte et Correction de la Divergence Local ↔ CI

**Problème découvert :**
- Tests passaient en **GitHub Actions** (PostgreSQL 16) ✅
- Tests échouaient **en local** (MySQL 8.0) ❌
- Root cause : Deux plateformes DB différentes = migrations incompatibles

**Diagnostic :**
```
CI (.github/workflows/ci.yml)     : postgres:16
Local (.env.test.local)           : mysql://root:@127.0.0.1:3306
Migration (Version20260429...)    : PostgreSQL-spécifique (SERIAL, quoted keywords)
```

**Actions correctives :**

1. **Mettre à jour le workflow CI vers MySQL 8.0**
```yaml
# Avant
services:
  postgres:
    image: postgres:16

# Après
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: critipixel_test
```

2. **Régénérer la migration pour MySQL**
```bash
# Supprimer la migration PostgreSQL problématique
rm migrations/Version20260429120000.php

# Régénérer depuis le schéma MySQL
php bin/console doctrine:database:drop --force --if-exists --env=test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:diff --env=test --from-empty-schema
```

3. **Résultat :** Migration MySQL `Version20260507090433.php` générée automatiquement ✅

**Vérification finale :**
```bash
# Local
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
php bin/phpunit
# Résultat: ✅ 32 tests, 74 assertions - OK
```

**Commit et Push :**
```bash
git add -A
git commit -m "fix: align CI environment to local MySQL setup + regenerate migrations"
git push
```

**Apprentissage clé :** 
> **Ne pas supposer que les environnements sont identiques.** CI et local doivent utiliser **la même plateforme DB** pour que les tests soient cohérents. Cela évite les surprises du type "ça marche en local mais pas en CI".

---

## 4. APPRENTISSAGES ACQUIS

### Concepts Techniques

#### 4.1 PHPStan et Analyse Statique
- **Niveaux PHPStan (0-9) :** Niveau 6 = bon équilibre entre rigueur et pragmatisme
- **Extensions essentielles :** Symfony + Doctrine pour valider:
  - Services du conteneur DI
  - Entités ORM et mappages Doctrine
  - Voters génériques
- **Types génériques :** `Collection<TKey, T>` requis pour les relations Doctrine (Symfony 6.4+)
- **Embedded entities :** Colonne prefix affecte les noms générés (attention aux migrations)

#### 4.2 PHP CS Fixer et Formatage
- **Règles @Symfony :** Inclut PSR-1 + PSR-12 + conventions Symfony
- **Trailing commas :** Élément clé pour les diffs git propres multi-lignes
- **Fins de ligne :** `setLineEnding("\n")` essentiel pour cohérence Windows/Linux
- **Cache :** `.php-cs-fixer.cache` peut masquer les problèmes (à régénérer)

#### 4.3 GitHub Actions et CI/CD
- **Services Docker :** Conteneur MySQL 8.0 avec health checks
- **Cache Composer :** Accélération majeure (30% + rapide si hashFiles() identique)
- **Env variables :** Surcharger DATABASE_URL pour chaque step (nécessaire)
- **Asset compilation :** SASS/Dart Sass doit être compilé AVANT tests (prérequisite)
- **Line endings :** Problème classique Windows (CRLF) vs Linux (LF) en CI
- **Alignement environnement :** Local et CI doivent utiliser la **même plateforme DB** (MySQL 8.0 ici)

#### 4.4 Choix de Plateforme DB : MySQL vs PostgreSQL

**Contexte :** Le projet initial utilisait PostgreSQL 16 en CI mais MySQL 8.0 en local, causant une divergence. Cette incompatibilité a été **résolue en alignant tout sur MySQL 8.0**.

**Référence des différences (pour la culture générale) :**

| Aspect | MySQL | PostgreSQL |
|--------|-------|------------|
| Auto-increment | `INT AUTO_INCREMENT` | `SERIAL` |
| Long text | `LONGTEXT` | `TEXT` |
| Immutable Date | N/A | `DATE` (with comment) |
| Reserved keywords | Backticks `` `user` `` | Double quotes `"user"` |
| Foreign keys | `NOT DEFERRABLE` optionnel | `NOT DEFERRABLE INITIALLY IMMEDIATE` requis |

**Choix effectué :** **MySQL 8.0** partout (local + CI) pour cohérence.

### Outils et Méthodologies

#### Commandes Essentielles (à mémoriser)
```bash
# PHPStan
vendor/bin/phpstan analyse --memory-limit=512M

# PHP CS Fixer
vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

# Symfony console
php bin/console sass:build
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate

# Git workflow
git checkout -b feature/branch
git add -A
git commit -m "descriptive message"
git push origin feature/branch
# Créer PR sur GitHub
```

#### Méthodologie Appliquée
1. **Plan Before Code :** Toujours explorer/planifier avant de corriger
2. **Iterative Fixing :** Fixer un type d'erreur à la fois (PHPStan: 24 erreurs par catégories)
3. **Test Each Layer :** DB (migrations), Code (static), Tests (PHPUnit), Style (CS Fixer)
4. **CI-First Mindset :** Si ça passe localement mais échoue en CI = problème environnement

---

## 5. POINTS FORTS — CE QUI A BIEN FONCTIONNÉ

### 5.1 Approche Méthodique
**Point fort :** Identification des problèmes racine avant la solution
- **Exemple 1 :** Erreur "boutons non trouvés en tests" 
  - Diagnose: SASS bundle échoue sans Dart Sass
  - Solution: Ajouter `sass:build` avant tests
  - Résultat: Tests passent immédiatement
  
- **Exemple 2 :** Erreur "AUTO_INCREMENT" en migration PostgreSQL
  - Diagnose: Migration écrite pour MySQL, pas PostgreSQL
  - Solution: Réécriture complète migration pour PostgreSQL
  - Résultat: BD se crée et migrate sans erreur

### 5.2 Rigueur dans la Correction de Bugs
**Point fort :** Ne pas masquer les erreurs, les comprendre et les fixer correctement

Exemple avec PHPStan `argument.type` :
```php
// ❌ MAUVAIS : Ignorer l'erreur ou caster
$review->setUser((User) $this->getUser());

// ✅ BON : Vérifier et valider le type
$user = $this->getUser();
if (!$user instanceof User) {
    throw new \LogicException('User must be authenticated.');
}
$review->setUser($user);
```

### 5.3 Gestion des Problèmes d'Environnement
**Point fort :** Identifier et résoudre les différences Windows/Linux

Problème CRLF/LF qui causa PHP CS Fixer à trouver différences en CI:
```php
// Solution: Forcer LF dans la config
->setLineEnding("\n")
```

### 5.4 Documentation et Rapports
**Point fort :** Créer des rapports détaillés pour comprendre ce qui a été fait

- `phpstan-rapport-corrections.txt` : 24 erreurs documentées par catégorie
- `php-cs-fixer-rapport-corrections.txt` : 51 fichiers reformatés, détail par fichier
- Permet review/audit complet du travail

### 5.5 Structure et Clarté du Workflow CI
**Point fort :** Pipeline claire et facile à déboguer

Chaque step a:
- Nom descriptif en français
- Objectif unique
- Sortie visible en logs GitHub
- Ordre logique (dépendances respectées)

**Résultat :** 0 erreur CI sur toutes les étapes ✅

---

## 6. DIFFICULTÉS RENCONTRÉES

### 6.1 Complexité des Types Génériques (PHPStan)

**Défi :** Comprendre `Collection<TKey, T>` pour Doctrine

```php
// ❌ INITIAL (PHPStan erreur)
@var Collection<Tag>

// ✅ CORRECT
@var Collection<int, Tag>
```

**Apprentissage :** Doctrine Collections en Symfony 6.4+ sont toujours génériques avec TKey.

**Comment surmonter :** Lire la documentation Doctrine ORM sur les types génériques + essais/erreurs.

---

### 6.2 Migration MySQL → PostgreSQL Incompatible

**Défi :** Synthaxe SQL radicalement différente

```sql
-- ❌ MySQL
INT AUTO_INCREMENT NOT NULL
ENGINE = InnoDB
LONGTEXT
`reserved_word`

-- ✅ PostgreSQL
SERIAL NOT NULL
(pas de ENGINE)
TEXT
"reserved_word"
```

**Pourquoi :** Le projet avait `.env` PostgreSQL mais migration MySQL originale → incohérence.

**Comment surmonter :** Connaître les 3-4 principales différences MySQL/PostgreSQL, chercher doc Doctrine pour chaque DB spécifique.

---

### 6.3 Problème YAML Indentation (GitHub Actions)

**Défi :** Erreur cryptique "Unexpected item 'parameters › symfony › container_xml_path'"

```yaml
# ❌ MAUVAIS (services au même niveau que ci)
jobs:
  ci:
    runs-on: ubuntu-latest
  services:
    postgres: ...

# ✅ CORRECT (services imbriqué sous ci)
jobs:
  ci:
    runs-on: ubuntu-latest
    services:
      postgres: ...
```

**Apprentissage :** YAML est très strict sur l'indentation = 2 espaces, pas de mélange tabs/spaces.

**Comment surmonter :** Utiliser un validateur YAML online ou linter local avant de pousser.

---

### 6.4 SASS Compilation Manquante en Tests

**Défi :** Tests échouent avec "Bouton 'Se connecter' non trouvé"

**Root cause :** Twig rendrait une page 500 si SASS échoue lors de compilation assets.

**Solution :** Ajouter `php bin/console sass:build` avant tests pour compiler assets.

**Apprentissage :** Asset compilation est un **prérequisite** pour tests fonctionnels qui rendent HTML, pas un nice-to-have.

---

### 6.5 Fins de Lignes Windows vs Linux CI

**Défi :** PHP CS Fixer trouve 3 fichiers à corriger en CI mais 0 localement

**Root cause :** 
- Windows: fichiers en CRLF, PHP CS Fixer produit CRLF
- Linux CI: fichiers en LF, PHP CS Fixer produit LF différemment

**Solution :** `->setLineEnding("\n")` force PHP CS Fixer à produire LF partout.

**Apprentissage :** Pour CI/CD multi-plateforme = normaliser les fins de ligne est **critique**.

---

### 6.6 Divergence Environnement : Tests Passent en CI mais Échouent Localement

**Défi :** Tests passaient en GitHub Actions mais échouaient en local

```
Local  : ❌ SQLSTATE[42000]: Syntax error near '"user"'
CI     : ✅ Tous les tests passent
```

**Root cause :** 
- **CI utilisait PostgreSQL 16** (service Docker `postgres:16`)
- **Local utilisait MySQL 8.0** (configuration `.env.test.local`)
- **Migration était PostgreSQL-spécifique** (`CREATE TABLE "user"` avec guillemets, `SERIAL`, etc.)
- MySQL rejette la syntaxe PostgreSQL → erreur en local

**Symptômes de ce problème :**
- Test passe en CI, échoue en local = divergence environnement
- Impossible de déboguer correctement en local si l'env diffère de CI
- Fausse confiance aux tests (ils passent mais ne représentent pas la réalité locale)

**Solution appliquée :**
1. Basculer le workflow CI de PostgreSQL → **MySQL 8.0**
2. Régénérer la migration adaptée à MySQL (suppression de l'ancienne PostgreSQL)
3. Vérifier que tests passent local ET CI

**Apprentissage clé :** 
> **Règle d'or : CI et local doivent avoir le même environnement DB.**  
> Cela évite les surprises et permet un débogage cohérent.  
> Si vous changez la plateforme DB, changez-la **partout** (local + CI + migrations).

---

## 7. FICHIERS CLÉ À MONTRER

### Configuration et Setup

| Fichier | Rôle | Montrer |
|---------|------|--------|
| `phpstan.neon` | Configuration PHPStan | Extensions Symfony/Doctrine, level 6 |
| `.php-cs-fixer.dist.php` | Configuration PHP CS Fixer | Règles @Symfony, setLineEnding("\n") |
| `.github/workflows/ci.yml` | Pipeline GitHub Actions | Architecture CI avec MySQL 8.0 |
| `migrations/Version20260507090433.php` | Migration MySQL | Syntaxe MySQL correcte pour toutes les tables |

### Rapports

| Fichier | Contenu |
|---------|---------|
| `phpstan-rapport-corrections.txt` | 24 erreurs PHPStan détaillées par catégorie + solutions |
| `php-cs-fixer-rapport-corrections.txt` | 51 fichiers reformatés + détails par fichier |

### Exemples de Code Corrigé

| Fichier | Changement |
|---------|-----------|
| `src/Controller/VideoGameController.php` | Ajout contrôle `instanceof User` avant setUser() |
| `src/Model/Entity/VideoGame.php` | Types génériques Collection<int, Tag> ajoutés |
| `src/Doctrine/DataFixtures/UserFixtures.php` | Fins de ligne normalisées (CRLF → LF) |
| `config/helpers.php` | Template T of array → T (générique décontraint) |

---

## 8. COMMANDES DE DÉMONSTRATION

### 8.1 Afficher l'état de la qualité de code

```bash
# Vérifier les erreurs PHPStan
vendor/bin/phpstan analyse --memory-limit=512M

# Montrer le résultat attendu
# [OK] No errors

# Vérifier la conformité CSS Fixer (dry-run)
vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php

# Montrer le résultat attendu
# Found 0 of 56 files that can be fixed
```

### 8.2 Lancer les tests

```bash
# Préparer la BD de test
php bin/console doctrine:database:drop --force --if-exists --env=test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction

# Lancer les tests
php bin/phpunit

# Montrer le résultat
# OK (32 tests, 74 assertions)
```

### 8.3 Montrer la pipeline GitHub Actions

```bash
# Voir le statut des workflows
# Aller sur: https://github.com/gregmelo/p14-critipixel-exercice/actions

# Montrer:
# - Workflow CI passe complètement (vert)
# - Tous les steps réussissent
# - Aucune erreur PHPStan
# - Aucune correction PHP CS Fixer nécessaire
# - Tests passent tous
```

### 8.4 Montrer les rapports détaillés

```bash
# Lire le rapport PHPStan
cat phpstan-rapport-corrections.txt

# Lire le rapport PHP CS Fixer
cat php-cs-fixer-rapport-corrections.txt
```

---

## 9. CHIFFRES CLÉS À RETENIR

| Métrique | Valeur | Statut |
|----------|--------|--------|
| **Erreurs PHPStan corrigées** | 24 | ✅ 0 restantes |
| **Fichiers PHP CS Fixer reformatés** | 51 | ✅ 0 restantes |
| **Fichiers analysés PHPStan** | 42 | ✅ 0 erreur |
| **Tests PHPUnit** | 32 | ✅ tous passent (74 assertions) |
| **Steps pipeline CI** | 8 | ✅ tous passent |
| **Temps pipeline** | ~1m 7s | ✅ acceptable |
| **Coverage de code** | N/A | (à configurer) |
| **Migration générée (MySQL)** | 1 | ✅ Version20260507090433.php (compatible local + CI) |

---

## 10. CONCLUSION

### Points Clés

1. **Pipeline CI complète** : De checkout à vérification qualité = 8 étapes automatisées
2. **Zéro dette technique immédiate** : Tous les outils qualité passent
3. **Processus scalable** : Facile d'ajouter nouveaux checks (coverage, SAST, etc.)
4. **Documentation claire** : Rapports détaillés pour audit et apprentissage

### Prochaines Étapes Recommandées

1. **Code Coverage** : Ajouter PHPUnit coverage report (viser 80%+)
2. **Sécurité** : Ajouter scan Dependency Check ou Snyk
3. **Performance** : Monitoring temps build (actuellement ~1m 7s OK)
4. **Documentation** : API documentation avec PHPDocumentor ou Swagger
5. **Releases** : Ajouter release automation (version bump, changelog, tags)

---

**Session réalisée :** [Date]  
**Mentor :** [Nom du mentor]  
**Durée totale :** ~3-4 heures de travail intense
