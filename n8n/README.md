# Workflow n8n — Génération d'idées WorkHelper

Ce dossier contient le workflow **n8n** utilisé par [WorkHelper](../README.md) pour produire **trois idées de projets SaaS** à partir d'un métier et de sa description.

Le workflow fait le lien entre :

- **Laravel** (client HTTP qui envoie le métier),
- **n8n** (orchestration),
- **LM Studio** (inférence locale, API compatible OpenAI).

---

## Table des matières

- [Vue d'ensemble](#vue-densemble)
- [Schéma du flux](#schéma-du-flux)
- [Prérequis](#prérequis)
- [Installation de n8n](#installation-de-n8n)
- [Importer le workflow](#importer-le-workflow)
- [Activer le workflow](#activer-le-workflow)
- [Configurer LM Studio](#configurer-lm-studio)
- [Télécharger et lancer Gemma](#télécharger-et-lancer-gemma)
- [Récupérer l'URL du webhook](#récupérer-lurl-du-webhook)
- [Connecter le webhook à Laravel](#connecter-le-webhook-à-laravel)
- [URLs, modes test et production](#urls-modes-test-et-production)
- [Données reçues (entrée)](#données-reçues-entrée)
- [Données retournées (sortie)](#données-retournées-sortie)
- [Alignement avec Laravel](#alignement-avec-laravel)
- [Tester le webhook manuellement](#tester-le-webhook-manuellement)
- [Dépannage](#dépannage)
- [Limitations connues](#limitations-connues)

---

## Vue d'ensemble

| Élément | Valeur |
|---------|--------|
| Fichier | `n8n/workflow.json` |
| Nom du workflow | `WORKHELPER` |
| Méthode webhook | `POST` |
| URL côté Laravel | Variable **`N8N_WEBHOOK_URL`** dans `.env` (non versionnée) |
| Format d'URL production | `http://localhost:5678/webhook/{votre-id}` |
| Format d'URL test (éditeur) | `http://localhost:5678/webhook-test/{votre-id}` |
| API IA (dans le workflow) | `http://127.0.0.1:1234/v1/chat/completions` |
| Modèle configuré (exemple) | `google/gemma-4-e4b` |

> L'identifiant `{votre-id}` est **unique** à votre instance n8n. Le workflow fourni peut afficher `eaa872c1-1a47-493b-b4bd-7300d2bb0ba3` après import, mais vous devez toujours copier **l'URL affichée chez vous** dans Laravel.

Le workflow contient **3 nœuds** :

1. **Webhook** — reçoit le métier depuis Laravel.
2. **HTTP Request** — appelle LM Studio avec un prompt système structuré.
3. **Respond to Webhook** — renvoie le JSON brut produit par le modèle.

---

## Schéma du flux

```
Laravel (IdeaController)
        │
        │  POST /webhook/{id}
        │  { "brief": { "job", "description" } }
        ▼
┌───────────────┐
│    Webhook    │  n8n — port 5678
└───────┬───────┘
        │
        ▼
┌───────────────┐
│ HTTP Request  │  POST 127.0.0.1:1234/v1/chat/completions
└───────┬───────┘
        │
        ▼
┌───────────────┐
│ LM Studio     │  Modèle Gemma chargé
│ (Gemma)       │
└───────┬───────┘
        │
        ▼
┌───────────────┐
│ Respond to    │  Corps = choices[0].message.content
│ Webhook       │  (JSON texte des 3 idées)
└───────────────┘
        │
        ▼
Laravel parse le JSON → sauvegarde → affichage
```

---

## Prérequis

- **n8n** installé (npm, Docker ou desktop)
- **LM Studio** installé
- Un modèle **Gemma** compatible (voir ci-dessous)
- **WorkHelper** configuré et accessible
- Ports libres :
  - `5678` — n8n
  - `1234` — API LM Studio (valeur par défaut courante)

---

## Installation de n8n

### Option A — npm (rapide)

```bash
npx n8n
```

### Option B — Docker

```bash
docker run -it --rm \
  -p 5678:5678 \
  -v n8n_data:/home/node/.n8n \
  docker.n8n.io/n8nio/n8n
```

### Option C — Application desktop

Téléchargez n8n depuis [https://n8n.io/](https://n8n.io/) et lancez l'application.

Interface par défaut : **http://localhost:5678**

---

## Importer le workflow

1. Ouvrez n8n (`http://localhost:5678`).
2. Menu **Workflows** → **Import from file** (ou glisser-déposer).
3. Sélectionnez :

   ```
   workhelper/n8n/workflow.json
   ```

4. Le workflow **WORKHELPER** apparaît avec les 3 nœuds connectés.
5. **Enregistrez** le workflow (Ctrl+S).
6. Passez à [Activer le workflow](#activer-le-workflow), puis [Connecter le webhook à Laravel](#connecter-le-webhook-à-laravel).

> Vous pouvez aussi créer **votre propre workflow** n8n : tant que le webhook accepte le JSON d'entrée Laravel et renvoie 3 idées au format attendu, WorkHelper fonctionnera avec votre URL.

---

## Activer le workflow

1. Ouvrez le workflow **WORKHELPER**.
2. Basculez le switch **Active** (en haut à droite) sur **ON**.
3. Ouvrez le nœud **Webhook** et copiez l'**URL de production** (voir section suivante).
4. Collez cette URL dans le `.env` Laravel : `N8N_WEBHOOK_URL=...`

---

## Configurer LM Studio

1. Installez **LM Studio** : [https://lmstudio.ai/](https://lmstudio.ai/)
2. Onglet **Local Server** (Serveur local).
3. Démarrez le serveur sur le port **1234** (par défaut).
4. Vérifiez l'endpoint affiché :

   ```
   http://127.0.0.1:1234/v1
   ```

5. Le nœud **HTTP Request** du workflow appelle :

   ```
   POST http://127.0.0.1:1234/v1/chat/completions
   ```

6. Headers envoyés :

   | Header | Valeur |
   |--------|--------|
   | `Content-Type` | `application/json` |

### Paramètres de génération (workflow)

| Paramètre | Valeur |
|-----------|--------|
| `temperature` | `0.7` |
| `max_tokens` | `2000` |

Si les réponses sont tronquées ou invalides, augmentez `max_tokens` dans le corps JSON du nœud HTTP Request.

---

## Télécharger et lancer Gemma

1. Dans LM Studio, ouvrez l'onglet **Search** / **Discover**.
2. Recherchez un modèle **Gemma** adapté à votre machine, par exemple une variante proche de :
   - `google/gemma-4-e4b` (identifiant utilisé dans `workflow.json`)
3. **Téléchargez** le modèle.
4. Onglet **Chat** ou **Local Server** → **sélectionnez le modèle** chargé.
5. Démarrez le **serveur local** avant de lancer une génération depuis WorkHelper.

### Correspondance du nom de modèle

Le champ `model` dans la requête HTTP doit correspondre **exactement** au nom affiché par LM Studio pour le modèle chargé.

Dans `workflow.json` :

```json
"model": "google/gemma-4-e4b"
```

Si LM Studio affiche un autre identifiant (ex. `gemma-3-4b-it`), modifiez cette valeur dans le nœud **HTTP Request** → corps JSON → champ `model`.

---

## Récupérer l'URL du webhook

Cette étape est **indispensable** pour connecter n8n à WorkHelper.

### Étapes dans l'interface n8n

1. Ouvrez votre workflow (ex. **WORKHELPER**).
2. Assurez-vous que le workflow est **Actif** (switch ON).
3. Cliquez sur le nœud **Webhook**.
4. Dans le panneau de droite, repérez l'URL de type :

   **Production URL** (ou équivalent selon la version de n8n) :

   ```
   http://localhost:5678/webhook/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
   ```

5. Cliquez sur **Copier** ou sélectionnez l'URL en entier.

### À quoi correspond chaque partie de l'URL ?

| Segment | Signification |
|---------|---------------|
| `http://localhost:5678` | Adresse de votre instance n8n (à adapter si Docker / autre machine) |
| `/webhook/` | Chemin **production** (workflow actif) |
| `xxxxxxxx-...` | Identifiant unique du webhook (généré par n8n) |

### Première installation ?

Si vous venez d'importer `workflow.json`, l'ID peut correspondre à celui du fichier d'exemple (`eaa872c1-1a47-493b-b4bd-7300d2bb0ba3`). **Ne supposez pas** qu'il est identique sur toutes les machines : copiez toujours l'URL affichée dans **votre** n8n.

---

## Connecter le webhook à Laravel

WorkHelper lit l'URL du webhook depuis le fichier **`.env`**, pas depuis le code source.

### 1. Ouvrir le fichier `.env`

À la racine du projet Laravel :

```
workhelper/.env
```

Si le fichier n'existe pas :

```bash
cp .env.example .env
php artisan key:generate
```

### 2. Renseigner la variable

Ajoutez ou modifiez cette ligne en collant **votre** URL copiée depuis n8n :

```env
N8N_WEBHOOK_URL=http://localhost:5678/webhook/your-webhook-id
```

**Exemple réel** (à adapter) :

```env
N8N_WEBHOOK_URL=http://localhost:5678/webhook/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3
```

### 3. Recharger la configuration Laravel

```bash
php artisan config:clear
```

### 4. Vérifier que Laravel voit la bonne URL

```bash
php artisan tinker --execute="echo config('services.n8n.webhook');"
```

Le terminal doit afficher exactement la même URL que dans `.env`.

### Chaîne technique (pour les curieux)

```
.env  →  N8N_WEBHOOK_URL
         ↓
config/services.php  →  config('services.n8n.webhook')
         ↓
IdeaController::generate()  →  Http::post(...)
```

### L'application sans webhook configuré

| Fonctionnalité | Fonctionne sans `N8N_WEBHOOK_URL` ? |
|----------------|-------------------------------------|
| Accueil, import ESCO | Oui |
| Historique des générations | Oui |
| Export PDF (générations existantes) | Oui |
| **Générer de nouvelles idées** | **Non** |

### Pourquoi ne pas coder l'URL dans le dépôt ?

- Chaque contributeur a sa propre instance n8n (port, Docker, ID différent).
- Le fichier `.env` n'est **pas** commité sur GitHub (bonne pratique Laravel).
- Vous pouvez utiliser le workflow fourni **ou** le vôtre sans modifier le code PHP.

---

## URLs, modes test et production

### URL utilisée par Laravel en production

Laravel envoie un `POST` vers la valeur de **`N8N_WEBHOOK_URL`** :

```
POST {N8N_WEBHOOK_URL}
Content-Type: application/json
```

### Mode test vs production

| Mode | Chemin URL | Quand l'utiliser |
|------|------------|------------------|
| **Production** | `/webhook/{id}` | Workflow **actif** — **à mettre dans `.env`** |
| **Test** | `/webhook-test/{id}` | Bouton « Listen for test event » dans l'éditeur n8n uniquement |

> Pour WorkHelper, configurez toujours **`N8N_WEBHOOK_URL`** avec l'URL **`/webhook/...`**, pas `/webhook-test/...`.

### Timeout

Laravel attend jusqu'à **120 secondes** (`Http::timeout(120)`). La première inférence Gemma peut être lente ; soyez patient.

### Changer host ou port

| Service | Défaut | Impact sur `.env` |
|---------|--------|-------------------|
| n8n | `localhost:5678` | Modifier le début de `N8N_WEBHOOK_URL` |
| LM Studio | `127.0.0.1:1234` | Configuré dans le nœud HTTP Request du workflow (pas dans `.env`) |

**Docker** : si n8n tourne dans un conteneur, utilisez l'host accessible depuis PHP (`host.docker.internal`, IP locale, etc.) dans `N8N_WEBHOOK_URL`.

---

## Données reçues (entrée)

Laravel envoie un JSON **POST** sur le webhook :

```json
{
  "brief": {
    "job": "Développeur web",
    "description": "Conçoit et maintient des applications web..."
  }
}
```

| Champ | Type | Description |
|-------|------|-------------|
| `brief.job` | string | Nom du métier (`occupations.name`) |
| `brief.description` | string \| null | Description ESCO du métier |

Dans n8n, le corps est accessible via `$json.body` sur le nœud Webhook.

### Prompt utilisateur (nœud HTTP Request)

Le workflow construit le message utilisateur à partir du métier. Laravel envoie **`brief.job`** (et non `brief.name`).

**Expression à utiliser dans le nœud HTTP Request :**

```javascript
Métier : ${$json.body.brief.job}

Description :
${$json.body.brief.description}
```

Si la génération renvoie des idées génériques ou vides, vérifiez ce point en priorité.

---

## Données retournées (sortie)

Le nœud **Respond to Webhook** renvoie le **texte brut** :

```
{{ $json.choices[0].message.content }}
```

Ce contenu doit être un **JSON valide** (sans markdown, sans texte avant/après), tableau de **exactement 3 objets**.

### Structure attendue par idée

```json
[
  {
    "occupation_name": "Nom du métier",
    "occupation_description": "Description du métier",
    "title": "Titre du projet",
    "application_type": "Web | Mobile | Automatisation",
    "description": "Description complète du projet",
    "why_useful": "Pourquoi ce projet est utile pour ce métier",
    "development_duration": "ex. 3 mois",
    "recommended_stack": "Laravel, Vue.js, ...",
    "business_model": "Abonnement SaaS, ...",
    "estimated_monthly_revenue": "2500"
  }
]
```

### Contraintes imposées au modèle (prompt système)

Le prompt système du workflow exige notamment :

- **Exactement 3 objets** dans le tableau ;
- **1 projet Web**, **1 Mobile**, **1 Automatisation** (pas de doublon de type) ;
- `estimated_monthly_revenue` réaliste, en **euros** ;
- `recommended_stack` moderne et cohérente ;
- **Aucun** champ supplémentaire, **aucun** objet imbriqué ;
- **JSON uniquement** (pas de ```json, pas de commentaire).

### Parsing côté Laravel

`IdeaController::extractProjects()` accepte :

- un **tableau JSON** direct en racine, ou
- un objet `{ "projects": [ ... ] }`.

Les champs `occupation_name` / `occupation_description` dans chaque objet IA sont optionnels pour la sauvegarde : Laravel utilise déjà le métier tiré de la base `occupations`.

---

## Alignement avec Laravel

| Étape | Composant Laravel |
|-------|-------------------|
| Déclenchement | `GET /ideas/generate` |
| Appel webhook | `IdeaController::generate()` via `config('services.n8n.webhook')` |
| Configuration URL | `.env` → `N8N_WEBHOOK_URL` |
| Persistance | `IdeaGenerationStore` |
| Affichage | `GET /generations/{id}` |
| PDF | `POST /ideas/pdf` |

Après succès :

1. Création d'une ligne `idea_generations`.
2. Création de 3 lignes `generated_ideas`.
3. Redirection vers la page détail.

---

## Tester le webhook manuellement

### Avec curl (workflow actif)

Remplacez l'URL par la valeur de votre `N8N_WEBHOOK_URL` :

```bash
curl -X POST "http://localhost:5678/webhook/your-webhook-id" \
  -H "Content-Type: application/json" \
  -d "{\"brief\":{\"job\":\"Conducteur de tramway\",\"description\":\"Opère un tramway en circulation régulière.\"}}"
```

### Depuis n8n (mode test)

1. Ouvrez le nœud **Webhook**.
2. Cliquez **Listen for test event**.
3. Envoyez la requête vers l'URL **webhook-test** affichée.
4. Inspectez la sortie du nœud **HTTP Request** puis **Respond to Webhook**.

### Valider le JSON

Copiez la réponse dans [https://jsonlint.com/](https://jsonlint.com/) ou :

```bash
php -r "json_decode(file_get_contents('php://stdin'), true, 512, JSON_THROW_ON_ERROR);" < response.json
```

---

## Dépannage

### Erreur « Impossible de générer les idées »

| Cause probable | Solution |
|----------------|----------|
| `N8N_WEBHOOK_URL` vide ou absente | Renseignez `.env`, puis `php artisan config:clear` |
| n8n arrêté | Lancez n8n, activez le workflow |
| Mauvaise URL | Utilisez `/webhook/` (production), pas `/webhook-test/` |
| URL obsolète après réimport du workflow | Recopiez l'URL du nœud Webhook dans `.env` |
| LM Studio arrêté | Démarrez le serveur local sur le port 1234 |
| Modèle non chargé | Chargez Gemma avant l'appel |
| Timeout 120s | Machine lente → réduire `max_tokens` ou utiliser un modèle plus petit |

### Réponse vide ou « Réponse invalide : aucun projet reçu »

- Le modèle a renvoyé du **markdown** ou du texte hors JSON → renforcer le prompt système.
- JSON **tronqué** → augmenter `max_tokens`.
- Tableau avec **moins de 3** éléments → relancer ou ajuster le prompt.

### Idées sans lien avec le métier

- Vérifiez l'expression n8n : **`brief.job`** et non `brief.name`.
- Vérifiez que `description` n'est pas vide en base.

### Erreur HTTP Request (connexion refusée)

```
connect ECONNREFUSED 127.0.0.1:1234
```

→ LM Studio n'est pas démarré ou écoute sur un autre port.

### Erreur 404 sur le webhook

→ Workflow non **actif**, ou mauvais chemin (`/webhook/` vs `/webhook-test/`).

### JSON invalide dans la réponse

→ Consulter l'exécution n8n (onglet **Executions**) et la sortie brute du modèle.

---

## Limitations connues

| Limitation | Détail |
|------------|--------|
| **IA locale** | Qualité et vitesse dépendent du matériel et du quantize du modèle |
| **Pas de streaming** | Réponse complète attendue avant traitement Laravel |
| **Pas de file d'attente** | Un seul appel synchrone ; pas de gestion de charge intégrée |
| **Prompt rigide** | Si le modèle ignore les consignes, le JSON peut être invalide |
| **Webhook non versionné** | Chaque utilisateur doit configurer `N8N_WEBHOOK_URL` dans son `.env` |
| **Pas d'authentification webhook** | Toute personne connaissant l'URL peut appeler le webhook en local |
| **Champs occupation dans la réponse IA** | Redondants avec Laravel ; non requis pour la persistance |
| **Modèle Gemma** | Nom exact variable selon la version LM Studio |

---

## Fichiers de référence

| Fichier | Rôle |
|---------|------|
| `n8n/workflow.json` | Export n8n à importer |
| `.env` / `.env.example` | `N8N_WEBHOOK_URL` — URL du webhook |
| `config/services.php` | Lecture de `N8N_WEBHOOK_URL` |
| `app/Http/Controllers/IdeaController.php` | Client HTTP vers le webhook |
| `app/Services/IdeaGenerationStore.php` | Sauvegarde des 3 idées |

---

<p align="center">
  <a href="../README.md">← Retour à la documentation WorkHelper</a>
</p>
