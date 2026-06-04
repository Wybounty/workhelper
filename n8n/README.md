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
- [URLs et webhook](#urls-et-webhook)
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
| ID webhook | `eaa872c1-1a47-493b-b4bd-7300d2bb0ba3` |
| URL locale (production n8n) | `http://localhost:5678/webhook/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3` |
| URL locale (mode test n8n) | `http://localhost:5678/webhook-test/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3` |
| API IA | `http://127.0.0.1:1234/v1/chat/completions` |
| Modèle configuré | `google/gemma-4-e4b` |

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

> Après import, l'**ID du webhook** dans l'URL peut rester `eaa872c1-1a47-493b-b4bd-7300d2bb0ba3` (défini dans le fichier). Si n8n génère un nouvel ID, mettez à jour l'URL dans `app/Http/Controllers/IdeaController.php`.

---

## Activer le workflow

1. Ouvrez le workflow **WORKHELPER**.
2. Basculez le switch **Active** (en haut à droite) sur **ON**.
3. Vérifiez que le nœud **Webhook** affiche l'URL de production :

   ```
   http://localhost:5678/webhook/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3
   ```

### Mode test vs production

| Mode | URL | Usage |
|------|-----|--------|
| **Production** (workflow actif) | `/webhook/{id}` | Utilisé par Laravel en conditions réelles |
| **Test** (bouton « Listen » sur le nœud) | `/webhook-test/{id}` | Débogage manuel dans l'éditeur n8n |

Laravel est configuré pour l'URL **production** :

```php
'http://localhost:5678/webhook/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3'
```

Pour tester depuis l'éditeur n8n sans activer le workflow, utilisez temporairement l'URL **webhook-test** dans `IdeaController.php`.

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

## URLs et webhook

### URL appelée par Laravel

```
POST http://localhost:5678/webhook/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3
```

Fichier source : `app/Http/Controllers/IdeaController.php`, méthode `generate()`.

### Timeout

Laravel attend jusqu'à **120 secondes** (`Http::timeout(120)`). La première inférence Gemma peut être lente ; soyez patient.

### Changer host ou port

| Service | Défaut |
|---------|--------|
| n8n | `localhost:5678` |
| LM Studio | `127.0.0.1:1234` |

Docker n8n sur une autre machine : remplacez `localhost` par l'IP ou le nom du conteneur accessible depuis PHP.

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

Le workflow construit le message utilisateur à partir du métier. **Important** : le fichier `workflow.json` fourni référence historiquement `brief.name`. Laravel envoie **`brief.job`**.

**Expression correcte à utiliser dans le nœud HTTP Request :**

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
| Appel webhook | `IdeaController::generate()` |
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

```bash
curl -X POST "http://localhost:5678/webhook/eaa872c1-1a47-493b-b4bd-7300d2bb0ba3" \
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
| n8n arrêté | Lancez n8n, activez le workflow |
| Mauvaise URL | Vérifiez `/webhook/` vs `/webhook-test/` |
| Webhook ID différent après import | Copiez l'URL du nœud Webhook dans `IdeaController.php` |
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
| **URL en dur** | Webhook et modèle sont codés dans le dépôt ; pas de variables `.env` côté n8n dans cette version |
| **Pas d'authentification webhook** | Toute personne connaissant l'URL peut appeler le webhook en local |
| **Champs occupation dans la réponse IA** | Redondants avec Laravel ; non requis pour la persistance |
| **Modèle Gemma** | Nom exact variable selon la version LM Studio |

---

## Fichiers de référence

| Fichier | Rôle |
|---------|------|
| `n8n/workflow.json` | Export n8n à importer |
| `app/Http/Controllers/IdeaController.php` | Client HTTP vers le webhook |
| `app/Services/IdeaGenerationStore.php` | Sauvegarde des 3 idées |

---

<p align="center">
  <a href="../README.md">← Retour à la documentation WorkHelper</a>
</p>
