# Système d'Upload des Articles - Cailloux-cie

## ✨ Nouvelles Fonctionnalités

### 1. Articles de Base
Le site inclut maintenant **8 articles de démonstration** automatiquement ajoutés via le fichier `database/seed.sql` :
- Petit caillou
- Gros caillou
- Caillou rond
- Caillou plat
- Caillou pointu
- Très petit caillou
- Caillou moyen
- Caillou bizarre

**Comment charger les articles** :
1. Exécuter `database/schema.sql` d'abord (création des tables)
2. Puis exécuter `database/seed.sql` (insertion des données)

### 2. Upload d'Images
Les utilisateurs peuvent maintenant **uploader leurs propres images** pour les articles qu'ils vendent.

## 📁 Dossrier d'Upload
- **Localisation** : `/uploads/`
- **Fichiers autorisés** : JPG, JPEG, PNG, GIF, WebP
- **Taille maximale** : 5 MB par image
- **Nommage** : Automatiquement sécurisé avec timestamp (ex: `art_65f1a2b3c.jpg`)

## 🔒 Sécurité
✅ Validation du type MIME  
✅ Vérification de l'extension  
✅ Limitation de taille (5 MB)  
✅ Noms de fichiers sécurisés (pas de surcharge)  
✅ Suppression de l'ancienne image lors de la mise à jour  

## 🛒 Utilisation

### Vendre un Article (`/sell.php`)
```
1. Nom du caillou * (requis)
2. Description * (requis)
3. Prix * (en €, > 0)
4. Quantité en stock (0 ou plus)
5. Image * (optionnel - JPG, PNG, GIF, WebP)
```

Le formulaire inclut maintenant un champ **file input** au lieu de simplement une URL.

### Modifier un Article (`/edit.php`)
- Affiche l'image actuelle de l'article
- Permet de remplacer l'image par une nouvelle
- Conserve l'ancienne image si aucune nouvelle n'est uploadée

## 🖼️ Affichage des Images
Les images sont affichées sur :
- **Page de détail** (`/detail.php`) - Image pleine taille ou emoji 🪨 par défaut
- **Listings** - Support pour affichage dans les listes (via `image_link`)

## 👨‍💼 Compte Admin
- **Username** : `admin`
- **Password** : `admin123`
- **Utilité** : Préchargé avec les articles de démonstration

## 🔧 Architecture Technique

### Fichiers Modifiés
- ✏️ `sell.php` - Ajout upload d'images
- ✏️ `edit.php` - Ajout upload d'images avec prévisualisation
- 📁 `uploads/` - Nouveau dossier (créé automatiquement)
- ✏️ `uploads/.htaccess` - Autorisation d'accès aux images

### Validation Côté Serveur
```php
// Types autorisés
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Extensions autorisées
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Taille maximale
$max_upload_size = 5 * 1024 * 1024; // 5 MB
```

## 📝 Base de Données
Le champ `image_link` dans la table `article` stocke :
- **Chemin local** : `uploads/art_65f1a2b3c.jpg`
- **URLs distantes** : Toujours supportées (ex: `https://...`)
- **NULL** : Affichera l'emoji 🪨 par défaut

## 🎯 Prochaines Améliorations Possibles
- [ ] Compression d'images automatique
- [ ] Galerie multi-images par article
- [ ] Redimensionnement des images
- [ ] Cropping/édition d'image
- [ ] Suppression des vieilles images de la base de données

---

**Créé** : 2026-02-23  
**Dernière mise à jour** : 2026-02-23
