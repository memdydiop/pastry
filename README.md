# Pastry - Application Web Laravel

Pastry est une application web moderne construite avec le framework Laravel. Elle sert de base solide pour le développement d'applications robustes, sécurisées et performantes, en intégrant des fonctionnalités essentielles comme l'authentification complète, la gestion de profil et une architecture backend/frontend moderne.

## ✨ Fonctionnalités Principales

* **Authentification Complète** : Inscription, connexion, réinitialisation de mot de passe, et vérification d'email.
* **Sécurité Renforcée** : Authentification à deux facteurs (2FA) disponible.
* **Gestion de Profil Utilisateur** : Création et mise à jour des informations de profil.
* **Middleware de complétion de profil** : Force les utilisateurs à compléter leur profil après l'inscription.
* **Stack Technique Moderne (TALL Stack)** : Utilisation de Tailwind CSS, Alpine.js, Livewire, et Laravel.
* **Tests Automatisés** : Suite de tests complète avec Pest pour garantir la fiabilité du code.
* **Intégration Continue** : Workflows GitHub Actions pour le linting et les tests automatiques.

## 🛠️ Technologies Utilisées

* **Backend** : PHP 8.2+ / Laravel 11
* **Frontend** : Livewire, Blade, Alpine.js, Tailwind CSS, Vite
* **Base de données** : Compatible avec MySQL, PostgreSQL, SQLite
* **Tests** : Pest

## 🚀 Installation et Démarrage Rapide

Suivez ces étapes pour installer et lancer le projet sur votre machine locale.

### Prérequis

* PHP >= 8.2
* Composer
* Node.js & NPM (ou Yarn)
* Une base de données (ex: MySQL)

### Étapes d'installation

1.  **Cloner le dépôt**
    ```bash
    git clone [https://github.com/memdydiop/pastry.git](https://github.com/memdydiop/pastry.git)
    cd pastry
    ```

2.  **Installer les dépendances PHP**
    ```bash
    composer install
    ```

3.  **Installer les dépendances JavaScript**
    ```bash
    npm install
    ```

4.  **Configurer l'environnement**
    Copiez le fichier d'exemple pour l'environnement et générez la clé de l'application.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configurer la base de données**
    Ouvrez le fichier `.env` et mettez à jour les variables `DB_*` avec les informations de votre base de données locale.
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=pastry
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6.  **Lancer les migrations de la base de données**
    Cette commande créera les tables nécessaires.
    ```bash
    php artisan migrate
    ```

7.  **Compiler les assets frontend**
    ```bash
    npm run dev
    ```

8.  **Lancer le serveur de développement**
    ```bash
    php artisan serve
    ```

L'application est maintenant accessible à l'adresse [http://127.0.0.1:8000](http://127.0.0.1:8000).

## ✅ Lancer les Tests

Pour exécuter la suite de tests automatisés, utilisez la commande suivante :
```bash
php artisan test
```