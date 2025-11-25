# Moodle Collaborative Assignment Plugin

**Projet de Fin d'Études – EST Safi (Génie Informatique)**

Ce repository présente le travail réalisé dans le cadre d'un Projet de Fin d'Études (PFE) : la conception et le développement d'un plugin Moodle permettant de créer et gérer des travaux collaboratifs entre étudiants.

Ce plugin introduit un nouveau type d'activité dans Moodle, destiné à améliorer l'enseignement à distance en permettant aux étudiants de travailler en groupes, soumettre des projets et faciliter l'évaluation par les enseignants.

## Objectif du projet

Développer un plugin Moodle de type activité offrant :

- ✔️ Création automatique de groupes d'étudiants
- ✔️ Attribution d'un responsable par groupe
- ✔️ Répartition du travail entre les membres
- ✔️ Soumission du travail par le responsable
- ✔️ Notifications automatiques pour tous les étudiants
- ✔️ Suivi et notation par l'enseignant
- ✔️ Interface claire et intégrée au style Moodle
- ✔️ Possibilité future de correction inter-groupes (perspective)

## Technologies utilisées

| Technologie | Utilisation |
|------------|-------------|
| **Moodle** | Plateforme cible pour le plugin |
| **PHP** | Développement de la logique du plugin |
| **HTML / CSS** | Interface utilisateur dans Moodle |
| **XML** | Définition des tables de la base de données |
| **MySQL** | Stockage des données du plugin |

## Structure du plugin

Un plugin Moodle doit inclure obligatoirement :
```
mod/
└── collaborativeassignment/
    ├── version.php          # Informations du plugin
    ├── db/
    │   └── install.xml      # Scripts de création des tables
    ├── lang/
    │   └── fr/...
    │   └── en/...
    ├── index.php            # Interface principale
    ├── view.php             # Affichage pour l'étudiant
    ├── classes/             # Logique métier
    ├── pix/
    │   └── icon.png         # Icône de l'activité
    └── form.php             # Formulaire de configuration
```

## Conception du projet

### Modèle Conceptuel de Données (MCD)
- Tables pour les groupes, les responsables, les travaux soumis
- Relations entre étudiants, groupes, et activités

### Modèle Logique de Données (MLD)
- Définition des clés primaires/étrangères
- Suivi de la cohérence avec les tables déjà existantes dans Moodle

### Spécifications fonctionnelles
- Création automatique des groupes
- Un seul responsable par groupe
- Dépôt du travail par groupe
- Notifications internes Moodle
- Affichage propre côté enseignant et étudiant

## Fonctionnement du plugin

### 1️⃣ Installation

Deux méthodes disponibles :
- via l'interface Moodle (Administration → Plugins → Installer un plugin)
- en déposant directement le dossier dans `moodle/mod/`

### 2️⃣ Création d'un travail collaboratif

L'enseignant remplit un formulaire contenant :
- titre
- description
- nombre d'étudiants par groupe
- date de soumission

### 3️⃣ Création automatique des groupes

Le plugin :
- génère les groupes
- assigne les membres
- désigne un responsable
- notifie les étudiants

### 4️⃣ Soumission du travail

Le responsable dépose le fichier du groupe.

### 5️⃣ Suivi côté enseignant

L'enseignant peut :
- visualiser les groupes
- voir les responsables
- vérifier les soumissions
- attribuer une note

## Aperçu des interfaces

- Interface enseignant
- Formulaire d'activité
- Notifications Moodle
- Liste des groupes
- Interface de dépôt pour les étudiants

*(Les captures sont disponibles dans le rapport.)*

## Perspectives d'amélioration

Prévu dans les versions futures :

- 🔄 Correction inter-groupes
- 📊 Comparaison entre note de l'enseignant et notes des étudiants
- 🧠 Ajout d'un tableau de bord interactif
- 📥 Téléchargement groupé des travaux

## Contact

Pour toute question ou collaboration :

- 📧 Email: votre-email@example.com
- 🔗 LinkedIn: [Votre profil](https://linkedin.com/in/votre-profil)

## Licence

Ce projet est développé dans le cadre d'un PFE académique à l'EST Safi.
