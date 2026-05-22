# Site Artiste — Kaz Ahmed Koné

Projet réalisé dans le cadre du développement d’un site vitrine pour un artiste plasticien basé à Nancy.  
L’objectif était de créer un espace en ligne permettant de présenter ses œuvres, valoriser son univers artistique et faciliter les prises de contact pour une éventuelle acquisition.

## À propos du projet

Ce site a été conçu comme une galerie numérique moderne et accessible.  
L’idée n’était pas seulement d’afficher des œuvres, mais aussi de proposer une navigation fluide et une expérience utilisateur agréable sur différents supports (ordinateur, tablette et mobile).

Ce projet m’a également permis d’apprendre concrètement plusieurs aspects du développement web, aussi bien côté interface utilisateur que côté back-end, base de données et sécurité.

---

## Technologies utilisées

### Front-end
- HTML5 pour la structure des pages
- CSS3 pour le design et l’adaptation responsive
- JavaScript Vanilla pour les interactions dynamiques

### Back-end
- PHP 8 pour rendre le site dynamique

### Base de données
- MySQL, administrée avec phpMyAdmin, pour gérer :
    - les œuvres
    - les catégories
    - les messages de contact

### Sécurité
- Requêtes préparées avec PDO pour limiter les risques d’injections SQL
- Nettoyage des données utilisateurs afin de réduire les risques de failles XSS
- Gestion des sessions utilisateur
- Hachage des mots de passe avec Bcrypt

### Environnement de développement
- XAMPP pour tester le site en local
- Git et GitHub pour le suivi des versions du projet

---

## Étapes du développement

### 1. Création du Front-End
La première étape a consisté à développer l’interface du site.

Pages réalisées :
- Accueil
- Galerie
- À propos
- Contact

Une attention particulière a été portée au responsive design afin de garantir une bonne expérience utilisateur sur mobile, tablette et ordinateur.

### 2. Dynamisation du site avec PHP et MySQL
Une fois l’interface terminée, le site a été connecté à une base de données afin de rendre le contenu dynamique.

Mise en place :
- affichage dynamique des œuvres depuis la base de données
- gestion des catégories artistiques
- création de fiches détaillées pour chaque œuvre
- pré-remplissage automatique de certaines informations via les paramètres d’URL (`$_GET`)

### 3. Sécurisation du projet
Un travail a également été effectué sur la sécurité du site :

- protection contre les injections SQL grâce à PDO
- sécurisation des données utilisateurs contre les scripts malveillants (XSS)
- création d’un espace administrateur sécurisé
- authentification avec mot de passe chiffré

---

## Auteure

**Sarah Kabouche**  
GitHub : https://github.com/kabouchesarah4-cmd  
LinkedIn : https://www.linkedin.com/in/sarah-kabouche-2004263a2/