# Cailloux & Cie — Site E-Commerce PHP

Site e-commerce **100 % PHP** (sans framework), réalisé selon le cahier des charges du projet final PHP.

## Thème

Boutique en ligne de **cailloux** : on vend des pierres tout simples, de tailles et formes différentes — pas de pierres précieuses, juste des cailloux. Les utilisateurs peuvent mettre en vente leurs cailloux, acheter avec un solde virtuel, gérer panier et factures. (Oui, vraiment.)

## Prérequis

- **PHP 8** (recommandé)
- **MySQL / MariaDB**
- **XAMPP** (Windows) installé — ou MAMP / LAMP selon votre système

---

## Guide d’installation pas à pas (détail)

Suivez ces étapes dans l’ordre.

---

### Étape 1 : Démarrer XAMPP

1. Ouvrez **XAMPP Control Panel**.
2. Cliquez sur **Start** pour **Apache**.
3. Cliquez sur **Start** pour **MySQL**.
4. Vérifiez que les deux affichent « Running » (en vert).  
   Si un des deux ne démarre pas (port 80 ou 3306 déjà utilisé), corrigez la configuration ou arrêtez l’application qui utilise le port.

---

### Étape 2 : Copier le projet dans htdocs

1. Ouvrez l’**Explorateur de fichiers** Windows.
2. Allez dans le dossier où se trouve votre projet (par exemple `C:\Users\Fabie\Desktop\ProjetPhp`).
3. **Sélectionnez tout le dossier** `ProjetPhp` (clic droit → Copier, ou Ctrl+C).
4. Allez dans le dossier **htdocs** de XAMPP :  
   **`C:\xampp\htdocs`**
5. **Collez** le dossier (Ctrl+V).  
   Vous devez obtenir : **`C:\xampp\htdocs\ProjetPhp`**  (VERIFIEZ BIEN QUE LE DOSSIER S'APPELLE "ProjetPhp")
   Avec à l’intérieur : `index.php`, `config`, `database`, `admin`, etc.

> Si votre XAMPP est installé ailleurs (ex. `D:\xampp`), utilisez ce chemin à la place de `C:\xampp`.

---

### Étape 3 : Créer la base de données avec phpMyAdmin

1. Ouvrez votre **navigateur** (Chrome, Firefox, Edge…).
2. Dans la barre d’adresse, tapez :  
   **`http://localhost/phpmyadmin`**  
   puis Entrée.
3. Si une page de connexion s’affiche :
   - **Utilisateur** : `root`
   - **Mot de passe** : laissez vide, ou `root` si vous l’avez configuré ainsi  
   puis validez.
4. Dans le menu de gauche, cliquez sur **« Nouvelle base de données »** (ou **« New »**).
5. Dans **« Nom de la base de données »**, tapez exactement :  
   **`php_exam_db`**
6. Laissez **Interclassement** par défaut (ex. `utf8mb4_unicode_ci`).
7. Cliquez sur **« Créer »**.
8. Dans le menu de gauche, cliquez sur la base **`php_exam_db`** pour la sélectionner.
9. En haut, cliquez sur l’onglet **« Importer »** (ou **« Import »**).
10. Cliquez sur **« Choisir un fichier »** (ou **« Browse »**).
11. Allez dans :  
    **`C:\xampp\htdocs\ProjetPhp\database`**  
    et sélectionnez le fichier **`schema.sql`**.
12. En bas de la page, cliquez sur **« Exécuter »** (ou **« Go »**).
13. Attendez le message du type « Import réussi » ou « X requêtes exécutées ».  
    La base contient maintenant les tables : `user`, `article`, `stock`, `cart`, `invoice`, etc.
14. Ajoutez maintenant **`seed.sql`** pour ajouter les articles de bases du site. (!Important sinon 
   vous vous retrouverez avec un site vide !!")

---

### Étape 4 : Configurer la connexion MySQL (config/database.php)

1. Ouvrez le fichier **`config/database.php`** du projet avec un éditeur de texte (Bloc-notes, Notepad++, VS Code, Cursor…).  
   Chemin complet : **`C:\xampp\htdocs\ProjetPhp\config\database.php`**
2. Vérifiez ces lignes :

   ```php
   $db_host = 'localhost';
   $db_user = 'root';
   $db_pass = 'root';  // ou '' si pas de mot de passe
   $db_name = 'php_exam_db';
   ```

3. **Si vous n’avez pas de mot de passe MySQL** (cas courant avec XAMPP par défaut), remplacez la ligne du mot de passe par :  
   **`$db_pass = '';`**
4. Enregistrez le fichier (Ctrl+S).

---

### Étape 5 : Configurer le sous-dossier (config/app.php)

Comme le site est dans **htdocs/ProjetPhp**, l’URL du site contient **/ProjetPhp**. Il faut le dire au site.

1. Ouvrez le fichier **`config/app.php`** :  
   **`C:\xampp\htdocs\ProjetPhp\config\app.php`**
2. Remplacez la ligne :

   ```php
   define('BASE', '');
   ```

   par :

   ```php
   define('BASE', '/ProjetPhp');
   ```

3. Enregistrez le fichier (Ctrl+S).

> Si un jour vous mettez le projet à la racine de htdocs (dossier renommé ou déplacé pour que l’URL soit `http://localhost/`), remettez `define('BASE', '');`.

---

### Étape 6 : Ouvrir le site dans le navigateur

1. Dans la barre d’adresse du navigateur, tapez :  
   **`http://localhost/ProjetPhp/`**  
   ou **`http://localhost/ProjetPhp/index.php`**
2. Vous devez voir la page d’**accueil** « Cailloux & Cie » (liste des articles, éventuellement vide au début).

---

### Étape 7 : Tester le site

1. **S’inscrire** : cliquez sur **Inscription**, créez un compte (email, nom d’utilisateur, mot de passe).  
   Vous devez être connecté automatiquement et redirigé vers l’accueil.
2. **Ajouter du solde** : allez dans **Mon compte**, section « Ajouter au solde », entrez un montant (ex. 100) et validez.
3. **Mettre en vente un caillou** : **Vendre** → remplir nom, description, prix, stock, éventuellement un lien d’image → valider.
4. **Acheter** : sur la fiche d’un article, ajoutez au panier, puis **Panier** → **Passer la commande** → remplir l’adresse de facturation → confirmer.
5. **Admin** : déconnectez-vous, connectez-vous avec **admin@stones.local** / **password**. Le lien **Admin** apparaît dans le menu ; vous pouvez gérer tous les articles et utilisateurs.

---

## En résumé

| Étape | Action |
|-------|--------|
| 1 | Démarrer **Apache** et **MySQL** dans XAMPP |
| 2 | Copier le dossier **ProjetPhp** dans **`C:\xampp\htdocs`** |
| 3 | Dans phpMyAdmin : créer la base **php_exam_db**, puis importer **database/schema.sql** |
| 4 | Dans **config/database.php** : mettre **`$db_pass = '';`** si pas de mot de passe MySQL |
| 5 | Dans **config/app.php** : mettre **`define('BASE', '/ProjetPhp');`** |
| 6 | Ouvrir **http://localhost/ProjetPhp/** dans le navigateur |

## Compte administrateur (fourni par le schéma)

- **Identifiant** : `admin@stones.local`
- **Mot de passe** : `password`


## Pages et fonctionnalités

| Page | URL | Description |
|------|-----|-------------|
| **Accueil** | `/index.php` | Liste des articles (cailloux) avec tri (récent, prix, nom). Accessible sans connexion. |
| **Inscription** | `/register.php` | Création de compte ; connexion automatique après inscription. |
| **Connexion** | `/login.php` | Connexion ; redirection vers la page demandée après login. |
| **Vendre** | `/sell.php` | Création d’un article (nom, description, prix, stock, image). Réservé aux utilisateurs connectés. |
| **Détail** | `/detail.php?id=...` | Fiche article, ajout au panier (avec quantité si stock géré). Accessible sans connexion. |
| **Panier** | `/cart.php` | Contenu du panier, modification des quantités, suppression de lignes, passage commande si solde suffisant. |
| **Valider commande** | `/cart/validate.php` | Saisie des informations de facturation, validation, génération de la facture, mise à jour des stocks et soldes. |
| **Modifier** | `/edit.php` | Modification ou suppression d’un article (auteur ou admin uniquement). |
| **Compte** | `/account.php` | Infos du compte, articles publiés, articles achetés, factures ; modification email / mot de passe et ajout de solde (pour son propre compte). Vue d’un autre compte via `?user=id`. |
| **Admin** | `/admin/` | Tableau de bord : liste des articles et des utilisateurs, modification / suppression (réservé aux comptes admin). |

## Règles métier

- **Accès** : seules **Accueil** et **Détail** sont accessibles sans être connecté ; les autres pages redirigent vers la page de connexion.
- **Solde** : chaque utilisateur a un solde ; les achats sont débités et le vendeur est crédité du montant de la vente.
- **Stock** : prise en charge du stock par article ; impossible de commander plus que le stock disponible.
- **Rôle admin** : modification/suppression de tout article et tout utilisateur (hors soi-même).

## Structure du projet

```
ProjetPhp/
├── admin/           # Back-office (articles, utilisateurs)
├── assets/
│   ├── css/         # Feuille de style
│   └── js/          # Scripts optionnels
├── config/          # Base de données, auth, app (BASE)
├── cart/            # Validation du panier
├── database/        # schema.sql (création BDD)
├── includes/        # header, footer, init
├── index.php        # Accueil
├── login.php, register.php, logout.php
├── sell.php, detail.php, edit.php
├── cart.php
├── account.php
└── README.md
```

## Fichier SQL de la base de données

Le fichier **`database/schema.sql`** permet de recréer la base et les tables.

## Licence

Projet pédagogique — module PHP.
