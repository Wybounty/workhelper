# WorkHelper

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js&logoColor=white)](https://vuejs.org/)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-3-9558E8)](https://inertiajs.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-38B2AC?logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![n8n](https://img.shields.io/badge/n8n-workflow-EA4B71?logo=n8n&logoColor=white)](n8n/README.md)

**WorkHelper** transforme un métier issu du référentiel européen **ESCO** en trois idées de projets numériques (Web, Mobile, Automatisation), avec stack recommandée, modèle économique et estimation de revenus. Les générations sont historisées et exportables en PDF.

---

## Table des matières

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Captures d'écran](#captures-décran)
- [Stack technique](#stack-technique)
- [Architecture](#architecture)
- [Source des données](#source-des-données)
- [Prérequis](#prérequis)
- [Installation locale](#installation-locale)
- [Configuration `.env`](#configuration-env)
- [Configuration du webhook n8n](#configuration-du-webhook-n8n)
- [Lancement du projet](#lancement-du-projet)
- [Import des métiers ESCO](#import-des-métiers-esco)
- [Génération d'idées SaaS](#génération-didées-saas)
- [Historique des générations](#historique-des-générations)
- [Export PDF](#export-pdf)
- [Workflow n8n & IA locale](#workflow-n8n--ia-locale)
- [Structure du projet](#structure-du-projet)
- [Routes principales](#routes-principales)
- [Contribution](#contribution)
- [Licence](#licence)
- [Crédits](#crédits)

---

## Présentation

WorkHelper est une application web open source qui aide les développeurs, entrepreneurs et product managers à **explorer des opportunités produit** à partir d'un métier réel.

Le parcours utilisateur :

1. **Intégrer** les métiers ESCO en base de données (fichier fourni localement).
2. **Générer** trois concepts de projets via un workflow **n8n** connecté à un modèle local (**LM Studio** + Gemma).
3. **Consulter** les résultats sous forme de cartes SaaS modernes.
4. **Consulter l'historique** de toutes les générations passées.
5. **Télécharger** chaque idée au format PDF (mini étude de projet).

---

## Fonctionnalités

| Fonctionnalité | Description |
|----------------|-------------|
| **Intégration ESCO** | Import par lots (`upsert`) depuis `database/data/occupations.json` |
| **Génération IA** | Métier aléatoire → webhook n8n → 3 idées structurées |
| **Persistance** | Tables `idea_generations` et `generated_ideas` |
| **Historique** | Liste paginée des générations avec métier, date et nombre d'idées |
| **Détail** | Affichage des 3 idées avec le même design que la page résultats |
| **Export PDF** | DomPDF, une étude par idée, téléchargement sans rechargement |
| **Interface** | Vue 3 + Inertia + Tailwind, design SaaS responsive |

---

## Captures d'écran

> Ajoutez vos captures dans `docs/screenshots/` puis mettez à jour les chemins ci-dessous.

| Page | Fichier suggéré |
|------|-----------------|
| Accueil | `docs/screenshots/home.png` |
| Historique | `docs/screenshots/generations-index.png` |
| Détail d'une génération | `docs/screenshots/generation-show.png` |
| Export PDF (aperçu) | `docs/screenshots/pdf-export.png` |

---

## Stack technique

### Backend

- **PHP** 8.3+
- **Laravel** 13
- **Inertia.js** (Laravel adapter v3)
- **SQLite** par défaut (MySQL/MariaDB compatible)
- **DomPDF** (`barryvdh/laravel-dompdf`) pour les exports PDF

### Frontend

- **Vue.js** 3 (Composition API, `<script setup>`)
- **TypeScript**
- **Tailwind CSS** 4
- **Vite** 8
- **Axios** (téléchargement PDF via `fetch`)

### Automatisation & IA

- **n8n** (webhook HTTP)
- **LM Studio** (API OpenAI-compatible locale)
- Modèle documenté : **Gemma** (`google/gemma-4-e4b` dans le workflow fourni)

Voir la documentation détaillée : **[n8n/README.md](n8n/README.md)**.

---

## Architecture

```
┌─────────────┐     POST brief (job + description)      ┌──────────────┐
│   Laravel   │ ──────────────────────────────────────► │     n8n      │
│  WorkHelper │                                         │   Webhook    │
└──────┬──────┘                                         └──────┬───────┘
       │                                                         │
       │                                                         ▼
       │                                                  ┌──────────────┐
       │                                                  │  LM Studio   │
       │                                                  │  :1234/v1    │
       │                                                  └──────┬───────┘
       │                                                         │
       │◄──────── JSON (tableau de 3 idées) ─────────────────────┘
       │
       ▼
┌──────────────────────────────────────────────────────────────┐
│  SQLite                                              │
│  • occupations                                               │
│  • idea_generations (métier + description)                   │
│  • generated_ideas (8 champs par idée)                       │
└──────────────────────────────────────────────────────────────┘
```

### Modèles de données

**`occupations`**

| Colonne | Type |
|---------|------|
| `id` | bigint |
| `name` | string (unique) |
| `description` | text, nullable |

**`idea_generations`**

| Colonne | Type |
|---------|------|
| `id` | bigint |
| `occupation_name` | string |
| `occupation_description` | text, nullable |

**`generated_ideas`**

| Colonne | Type |
|---------|------|
| `id` | bigint |
| `generation_id` | FK → `idea_generations` (cascade) |
| `title` | string |
| `application_type` | string |
| `description` | text |
| `why_useful` | text |
| `development_duration` | string |
| `recommended_stack` | text |
| `business_model` | string |
| `estimated_monthly_revenue` | string |

**Relations Eloquent**

- `IdeaGeneration` **hasMany** `GeneratedIdea` (alias `ideas()`)
- `GeneratedIdea` **belongsTo** `IdeaGeneration`

La sauvegarde est gérée par `App\Services\IdeaGenerationStore` (transaction DB).

---

## Source des données

### ESCO (European Skills, Competences, Qualifications and Occupations)

**This project uses the ESCO classification of the European Commission.**

Les métiers et leurs descriptions proviennent de la taxonomie **ESCO**, publiée par la [Commission européenne](https://commission.europa.eu/). ESCO relie compétences, qualifications et occupations dans l'Union européenne.

- Site officiel : [https://esco.ec.europa.eu/](https://esco.ec.europa.eu/)
- Documentation & téléchargements : [https://esco.ec.europa.eu/en/use-esco/download](https://esco.ec.europa.eu/en/use-esco/download)

WorkHelper n'est **pas** affilié à la Commission européenne. Les données ESCO sont réutilisées conformément à leur licence (voir le site ESCO pour les conditions d'utilisation et d'attribution).

### Fichier local

Le dépôt attend un fichier JSON préparé :

```
database/data/occupations.json
```

Chaque entrée doit au minimum exposer :

- `preferredLabel` → nom du métier
- `description` → description (optionnelle)

> Le fichier n'est en général **pas** versionné (volume important). Placez-le manuellement après avoir extrait/transformé les données ESCO au format attendu par l'import.

---

## Prérequis

- **PHP** ≥ 8.3 avec extensions : `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
- **Composer** 2
- **Node.js** ≥ 20 et **npm**
- **n8n** (local ou Docker)
- **LM Studio** avec un modèle Gemma chargé
- (Optionnel) [Laravel Herd](https://herd.laravel.com/) sur macOS/Windows pour servir l'application

---

## Installation locale

### 1. Cloner le dépôt

```bash
git clone https://github.com/VOTRE_ORGANISATION/workhelper.git
cd workhelper
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances front-end

```bash
npm install
```

### 4. Environnement & clé d'application

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Base de données

SQLite (par défaut) :

```bash
touch database/database.sqlite
php artisan migrate
```

MySQL : configurez `DB_*` dans `.env`, créez la base, puis `php artisan migrate`.

### 6. Données ESCO

Placez votre fichier :

```
database/data/occupations.json
```

### 7. Workflow n8n

Suivez **[n8n/README.md](n8n/README.md)** pour :

1. Installer et lancer **n8n**
2. Importer `n8n/workflow.json` (workflow fourni ou le vôtre)
3. **Activer** le workflow
4. Configurer **LM Studio** + modèle Gemma
5. **Copier l'URL du webhook** affichée dans n8n

### 8. Configurer le webhook dans Laravel

Dans votre fichier `.env`, renseignez l'URL copiée depuis n8n :

```env
N8N_WEBHOOK_URL=http://localhost:5678/webhook/your-webhook-id
```

Puis rechargez la configuration :

```bash
php artisan config:clear
```

> **La génération d'idées ne fonctionnera pas** tant que `N8N_WEBHOOK_URL` n'est pas définie avec une URL valide. Voir la section [Configuration du webhook n8n](#configuration-du-webhook-n8n).

### 9. Build front (production)

```bash
npm run build
```

---

## Configuration `.env`

| Variable | Description | Valeur par défaut |
|----------|-------------|-------------------|
| `APP_NAME` | Nom affiché | `Laravel` → renommez en `WorkHelper` |
| `APP_URL` | URL publique de l'app | `http://localhost` ou URL Herd |
| `APP_KEY` | Clé de chiffrement | Générée via `key:generate` |
| `DB_CONNECTION` | Driver BDD | `sqlite` |
| `DB_DATABASE` | Chemin SQLite | `database/database.sqlite` |
| `N8N_WEBHOOK_URL` | URL du webhook n8n (**obligatoire**) | — (à renseigner) |

Exemple minimal (SQLite + Herd + n8n) :

```env
APP_NAME=WorkHelper
APP_URL=http://workhelper.test
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=sqlite
# DB_DATABASE est résolu automatiquement vers database/database.sqlite

# Webhook n8n — remplacez your-webhook-id par l'ID affiché dans n8n
N8N_WEBHOOK_URL=http://localhost:5678/webhook/your-webhook-id
```

---

## Configuration du webhook n8n

La génération d'idées repose sur un appel HTTP de Laravel vers votre instance **n8n**. L'URL du webhook n'est **plus codée en dur** dans le code : chaque utilisateur la configure dans son fichier **`.env`**.

### Pourquoi cette approche ?

| Avantage | Explication |
|----------|-------------|
| **Portabilité** | Chaque développeur peut utiliser son propre host, port ou instance Docker |
| **Open source** | Aucune URL personnelle ou de production n'est versionnée dans le dépôt |
| **Personnalisation** | Vous pouvez importer le workflow fourni **ou** créer le vôtre dans n8n |
| **Sécurité** | L'URL reste locale à votre machine (fichier `.env` non commité) |

### Où configurer Laravel ?

1. Ouvrez le fichier **`.env`** à la racine du projet (copié depuis `.env.example`).
2. Ajoutez ou modifiez la variable :

```env
N8N_WEBHOOK_URL=http://localhost:5678/webhook/your-webhook-id
```

3. Remplacez `your-webhook-id` par l'identifiant **réel** affiché par n8n (voir ci-dessous).
4. Exécutez `php artisan config:clear` après chaque modification.

La valeur est lue via `config/services.php` :

```php
'n8n' => [
    'webhook' => env('N8N_WEBHOOK_URL'),
],
```

Et utilisée dans `IdeaController::generate()` :

```php
Http::timeout(120)->post(config('services.n8n.webhook'), [ /* ... */ ]);
```

### Comment récupérer l'URL dans n8n ?

1. Importez et **activez** le workflow (voir [n8n/README.md](n8n/README.md)).
2. Ouvrez le nœud **Webhook** du workflow.
3. Copiez l'**URL de production** affichée lorsque le workflow est actif. Elle ressemble à :

```
http://localhost:5678/webhook/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

4. Collez cette URL **en entier** dans `N8N_WEBHOOK_URL`.

> Utilisez l'URL **`/webhook/...`** (production), pas **`/webhook-test/...`** (mode test de l'éditeur), sauf pour du débogage ponctuel.

### Exemple complet de configuration

**Fichier `.env` :**

```env
APP_NAME=WorkHelper
APP_URL=http://workhelper.test

DB_CONNECTION=sqlite

# Exemple avec le workflow fourni après import dans n8n local
N8N_WEBHOOK_URL=http://localhost:5678/webhook/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3
```

**Vérification rapide :**

```bash
php artisan tinker --execute="echo config('services.n8n.webhook');"
```

La commande doit afficher la même URL que dans votre `.env`. Si elle est vide ou `null`, la génération échouera.

### Comportement si la variable est absente

| Situation | Conséquence |
|-----------|-------------|
| `N8N_WEBHOOK_URL` non définie | `config('services.n8n.webhook')` vaut `null` → erreur à la génération |
| URL incorrecte (404, workflow inactif) | Message *« Impossible de générer les idées pour le moment »* |
| n8n ou LM Studio arrêté | Même erreur ou timeout (120 s) |

**L'application fonctionne** pour l'import ESCO et la consultation de l'historique **sans** n8n. Seule l'action **« Générer des idées »** nécessite `N8N_WEBHOOK_URL`.

### Utiliser son propre workflow n8n

Le fichier `n8n/workflow.json` est un **point de départ**. Vous pouvez :

- le modifier (prompt, modèle, logique) ;
- ou créer un workflow entièrement nouveau.

Conditions pour que Laravel fonctionne avec votre workflow :

1. Un nœud **Webhook** en entrée (méthode `POST`)
2. Une réponse JSON contenant **3 idées** (format décrit dans [n8n/README.md](n8n/README.md))
3. L'URL de production copiée dans **`N8N_WEBHOOK_URL`**

---

## Lancement du projet

### Mode développement (recommandé)

Lance en parallèle le serveur PHP, la queue et Vite :

```bash
composer run dev
```

Accès habituel :

- Application : `http://localhost:8000` ou votre domaine Herd
- Vite HMR : port affiché dans le terminal

### Mode manuel

```bash
php artisan serve
npm run dev
```

### Production locale

```bash
npm run build
php artisan serve
```

---

## Import des métiers ESCO

1. Vérifiez que `database/data/occupations.json` est présent.
2. Ouvrez l'**accueil** (`/`).
3. Cliquez sur **« Intégrer les données ESCO »**.

L'import :

- Lit le JSON local (aucun téléchargement automatique depuis ESCO).
- Insère ou met à jour par **nom** (`upsert` par lots de 500).
- Affiche le nombre total de métiers en base.

Route : `GET /occupations/import` (`occupations.import`).

---

## Génération d'idées SaaS

**Prérequis :**

- Métiers importés en base
- **`N8N_WEBHOOK_URL`** correctement renseignée dans `.env`
- Workflow n8n **actif** + **LM Studio** démarré avec Gemma

1. Depuis l'accueil ou l'historique, lancez **« Générer 3 idées de projets »**.

Processus côté Laravel :

1. Sélection aléatoire d'un métier en base.
2. `POST` vers l'URL définie par `N8N_WEBHOOK_URL` avec :

```json
{
  "brief": {
    "job": "Nom du métier",
    "description": "Description du métier"
  }
}
```

3. Réception d'un tableau JSON de **3 projets**.
4. Sauvegarde via `IdeaGenerationStore`.
5. Redirection vers `/generations/{id}`.

Route : `GET /ideas/generate` (timeout HTTP : **120 secondes**).

---

## Historique des générations

| Route | Page |
|-------|------|
| `GET /generations` | Liste paginée (12 par page) |
| `GET /generations/{id}` | Détail : métier + 3 cartes |

Chaque entrée d'historique affiche :

- le **métier** analysé ;
- la **date** de génération ;
- le **nombre d'idées** (3).

`GET /ideas` redirige vers la dernière génération ou vers l'historique s'il est vide.

---

## Export PDF

Sur la page détail d'une génération, chaque carte propose **« Télécharger le PDF »**.

- Route : `POST /ideas/pdf`
- Validation : `DownloadIdeaPdfRequest`
- Vue : `resources/views/pdf/idea.blade.php`
- Format : A4, style mini étude projet
- Téléchargement : `fetch` + blob (pas de rechargement Inertia)

Le nom de fichier est dérivé du titre (`Str::slug` côté serveur).

---

## Workflow n8n & IA locale

Le cœur de la génération IA n'est pas dans Laravel : il est délégué à **n8n**, qui appelle **LM Studio**. Laravel ne connaît que l'URL configurée dans **`N8N_WEBHOOK_URL`**.

Documentation complète (import, activation, récupération de l'URL, Gemma, dépannage) :

**→ [n8n/README.md](n8n/README.md)**

---

## Structure du projet

```
workhelper/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── IdeaController.php
│   │   │   ├── IdeaGenerationController.php
│   │   │   └── OccupationController.php
│   │   └── Requests/
│   │       └── DownloadIdeaPdfRequest.php
│   ├── Models/
│   │   ├── IdeaGeneration.php
│   │   ├── GeneratedIdea.php
│   │   └── Occupation.php
│   └── Services/
│       └── IdeaGenerationStore.php
├── database/
│   ├── data/
│   │   └── occupations.json          # À fournir (ESCO)
│   └── migrations/
├── n8n/
│   ├── workflow.json
│   └── README.md
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── IdeaProjectGrid.vue
│   │   │   └── Footer.vue
│   │   └── pages/
│   │       ├── Home.vue
│   │       └── Generations/
│   │           ├── Index.vue
│   │           └── Show.vue
│   └── views/
│       ├── app.blade.php
│       └── pdf/
│           └── idea.blade.php
├── routes/
│   └── web.php
├── docs/
│   └── screenshots/                  # Captures (à ajouter)
└── README.md
```

---

## Routes principales

| Méthode | URI | Nom | Action |
|---------|-----|-----|--------|
| GET | `/` | `home` | Accueil |
| GET | `/occupations/import` | `occupations.import` | Intégration ESCO |
| GET | `/ideas/generate` | `ideas.generate` | Génération IA |
| GET | `/ideas` | `ideas.index` | Redirection dernière génération |
| POST | `/ideas/pdf` | `ideas.pdf` | Export PDF |
| GET | `/generations` | `generations.index` | Historique |
| GET | `/generations/{generation}` | `generations.show` | Détail |

---

## Contribution

Les contributions sont les bienvenues.

1. Forkez le projet.
2. Créez une branche : `git checkout -b feature/ma-fonctionnalite`
3. Committez vos changements.
4. Poussez la branche et ouvrez une **Pull Request**.

Bonnes pratiques :

- `composer run lint` (Laravel Pint)
- `npm run lint:check` et `npm run types:check`
- `composer run test` (Pest)

Pour les changements du workflow IA, documentez-les dans `n8n/README.md`.

---

## Licence

Ce projet est publié sous la licence **MIT**. Voir le fichier [LICENSE](LICENSE) (à ajouter à la racine si absent — le `composer.json` du projet indique `MIT`).

---

## Crédits

- **WorkHelper** — application open source par la communauté du dépôt.
- **[ESCO](https://esco.ec.europa.eu/)** — European Skills, Competences, Qualifications and Occupations, European Commission.
- **[Laravel](https://laravel.com/)**, **[Vue.js](https://vuejs.org/)**, **[Inertia.js](https://inertiajs.com/)**, **[Tailwind CSS](https://tailwindcss.com/)**, **[n8n](https://n8n.io/)**, **[LM Studio](https://lmstudio.ai/)**.

---

<p align="center">
  <strong>WorkHelper</strong> — du référentiel métier à l'idée produit.<br>
  <sub>Données métiers : référentiel ESCO (Commission européenne)</sub>
</p>
