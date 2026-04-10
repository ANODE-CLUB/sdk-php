# ANODE - SDK PHP

## Description

Ce SDK PHP permet la gestion des métadonnées des **Mandats d'Accès aux Données des PDL** (Points De Livraison) dans les fichiers PDF. Il offre une API simple et robuste pour :

- **Lire** les métadonnées des mandats depuis des PDFs existants
- **Écrire** des métadonnées dans des PDFs de mandats
- **Gérer** les informations des mandants (particuliers et professionnels)
- **Manipuler** les données de points de livraison (PRM/PCE)

## Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer

### Installation via Composer

```bash
composer install
```

## Structure du projet

```
anode/sdk-php/
├── src/                                           # Code source du SDK
│   └── MetadonneesMandatAccesDonneesPDL/         # SDK Métadonnées Mandat d'Accès aux Données PDL
│       └── V1/                                   # Version 1 de l'API
│           ├── Dto/                              # Data Transfer Objects
│           │   ├── Mandat.php                    # Classe principale du mandat
│           │   └── Mandat/                       # Composants du mandat
│           │       ├── Parties.php              # Parties impliquées (mandant + mandataire)
│           │       ├── Parties/                 # Types de parties
│           │       │   ├── Partie.php           # Classe abstraite de partie
│           │       │   └── Partie/              # Types concrets de parties
│           │       │       ├── PersonnePhysique.php     # Personne physique
│           │       │       ├── PersonneMorale.php       # Personne morale
│           │       │       └── PersonneMorale/          # Sous-composants personne morale
│           │       │           └── RepresentantLegal.php # Représentant légal
│           │       ├── Objet.php                # Objet du mandat
│           │       ├── Objet/                   # Composants de l'objet
│           │       │   ├── Donnees.php          # Types de données demandées
│           │       │   ├── PointsDeLivraison.php       # Collection des PDL
│           │       │   ├── PointsDeLivraison/          # Types de PDL
│           │       │   │   ├── Prm.php          # Point PRM (électricité)
│           │       │   │   └── Pce.php          # Point PCE (gaz)
│           │       │   └── Delegations.php      # Délégations accordées
│           │       └── Consentement.php         # Consentement et validité
│           └── Handler/                          # Gestionnaires
│               └── Pdf.php                      # Gestionnaire pour fichiers PDF
├── test/                                      # Tests unitaires
├── exemple/                                   # Exemples d'utilisation
│   └── mandat-acces-donnees-pdl/v1/          # Exemples V1
│       ├── lecture-meta-donnees.php             # Lecture des métadonnées
│       ├── ajout-meta-donnees.php               # Ajout de métadonnées
│       ├── mandat.pdf                           # PDF d'exemple
│       └── mandat-src.pdf                       # PDF source
└── composer.json                              # Configuration Composer
```

## Utilisation

### 1. Lecture des métadonnées depuis un PDF

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf;

// Instancier le gestionnaire PDF
$pdfHandler = new Pdf('mandat.pdf');

// Extraire les métadonnées du mandat
$mandat = $pdfHandler->getMandat();

// Afficher les données
var_dump($mandat);
```

### 2. Création et ajout de métadonnées dans un PDF

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonnePhysique;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Delegations;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Donnees;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Prm;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Pce;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf;

// Créer les parties du mandat
$mandant = new PersonneMorale(
    'ACME',
    '123456789', 
    '1 rue de la République, 69001 Lyon',
    new RepresentantLegal('MARTIN', 'Julien', 'Président', 'julien.martin@domain.ext', '+33123456789')
);

$mandataire = new PersonnePhysique(
    'DUPONT',
    'Jean',
    '456 avenue des Fournisseurs, 75001 Paris',
    'jean.dupont@energy.com',
    '+33987654321'
);

$parties = new Parties($mandant, $mandataire);

// Définir l'objet du mandat
$donnees = new Donnees(
    true,  // Données techniques
    true,  // Données contractuelles  
    false, // Données d'usage
    24     // Période d'historique en mois
);

$pointsDeLivraison = (new PointsDeLivraison())
    ->add(new Prm('12345678901234'))  // PDL électricité
    ->add(new Pce('GI123456'));       // PDL gaz naturel

$delegations = (new Delegations())
    ->add('Consultation des données de consommation')
    ->add('Transmission aux fournisseurs d\'énergie');

$objet = new Objet($donnees, $pointsDeLivraison, $delegations);

// Définir le consentement
$consentement = new Consentement(
    new \DateTimeImmutable('2024-01-01 12:34:56', new \DateTimeZone('Europe/Paris')),
    new \DateTimeImmutable('2024-12-31 23:59:59', new \DateTimeZone('Europe/Paris'))
);

// Créer le mandat complet
$mandat = new Mandat($parties, $objet, $consentement);

// Intégrer dans le PDF
$pdfHandler = new Pdf('mandat.pdf');
$pdfHandler->setMandat($mandat);
```

## API Principale

### DTOs (Data Transfer Objects)

#### Structure du mandat
- **`Mandat`** : DTO principal représentant un mandat complet d'accès aux données
  - **`Parties`** : Les parties impliquées dans le mandat (mandant + mandataire)
  - **`Objet`** : L'objet du mandat (données demandées, PDL concernés)
  - **`Consentement`** : Le consentement donné et sa période de validité

#### Types de parties 
- **`Partie`** : Classe abstraite représentant une partie au mandat
  - **`PersonnePhysique`** : Personne physique (particulier)
  - **`PersonneMorale`** : Personne morale (entreprise, association...)
    - **`RepresentantLegal`** : Représentant légal de la personne morale

#### Objet du mandat
- **`Objet`** : Regroupe ce qui est demandé dans le mandat
  - **`Donnees`** : Types de données et permissions d'accès
  - **`PointsDeLivraison`** : Collection de points de livraison concernés
  - **`Delegations`** : Délégations accordées (optionnel)

#### Composants techniques
- **`PointsDeLivraison`** : Collection des points de mesure
  - **`Prm`** : Point Référence Mesure (électricité)
  - **`Pce`** : Point de Comptage et d'Estimation (gaz)
- **`Consentement`** : Période de validité du mandat

### Handlers (Gestionnaires)

- **`Pdf`** : Gestionnaire pour lire/écrire les métadonnées dans les fichiers PDF

### Types de points de livraison

- **`Prm`** : Point de Reference Mesure (électricité)
- **`Pce`** : Point de Comptage et d'Estimation (gaz)

## Exemples d'implémentation

Des exemples complets sont disponibles dans le dossier [`exemple/`](exemple/) :

- [`lecture-meta-donnees.php`](exemple/lecture-meta-donnees.php) : Extraction de métadonnées
- [`ajout-meta-donnees.php`](exemple/ajout-meta-donnees.php) : Création et intégration de métadonnées

## Développement

### Scripts disponibles

```bash
# Formatage du code (PSR-12)
composer run lint

# Analyse statique
composer run analyse

# Tests unitaires
composer run test

# Couverture de code
composer run coverage
```

### Architecture

Le SDK suit une architecture en couches avec une approche **orientée métier juridique** :

- **DTOs** : Classes immutables (`readonly`) utilisant la terminologie juridique française
  - **Mandat** : Le document juridique principal
  - **Parties** : Mandant (qui donne le mandat) et Mandataire (qui le reçoit)
  - **Objet** : Ce qui est demandé dans le mandat 
  - **Consentement** : Validation et période de validité
- **Handlers** : Gestionnaires responsables des opérations sur les fichiers PDF  
- **Sérialisation** : Chaque DTO dispose de méthodes `buildXml()` et `makeFromXml()` pour la conversion XML

Cette architecture garantit :
- ✅ **Terminologie juridique appropriée** (mandant/mandataire, consentement)
- ✅ **Immutabilité des données** (DTOs readonly)  
- ✅ **Séparation des responsabilités** (DTOs vs Handlers)
- ✅ **Structure intuitive** reflétant le domaine métier juridique

## Standards

- **PSR-4** : Autoloading des classes
- **PSR-12** : Style de code
- Compatible **PHP 8.1+**
- Tests avec **PHPUnit**
- Analyse statique avec **PHPStan**

## Licence

Ce projet est développé par CNNE pour la gestion des mandats d'accès aux données des points de livraison d'énergie.

