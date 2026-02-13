<?php
/**
 * Galerie d'images de cailloux : photos réalistes (Pexels, licence libre).
 * Utilisée pour le placeholder quand un article n'a pas d'image, et pour la sélection dans les formulaires.
 */

if (!defined('BASE')) define('BASE', '');

/**
 * URLs d'images réalistes de cailloux / pierres (Pexels, format 800px).
 * Vous pouvez remplacer par d'autres URLs d'images libres de droits.
 */
$GALLERY_IMAGES = [
    'https://images.pexels.com/photos/289586/pexels-photo-289586.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/17078843/pexels-photo-17078843.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/13105773/pexels-photo-13105773.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/1698618/pexels-photo-1698618.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/257352/pexels-photo-257352.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/8560918/pexels-photo-8560918.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/267596/pexels-photo-267596.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/3467946/pexels-photo-3467946.jpeg?auto=compress&cs=tinysrgb&w=800',
];

/**
 * Retourne l'URL de l'image placeholder pour un article sans image.
 */
function gallery_placeholder_url() {
    global $GALLERY_IMAGES;
    return $GALLERY_IMAGES[0] ?? '';
}

/**
 * Retourne la liste des URLs des images de la galerie.
 */
function gallery_image_urls() {
    global $GALLERY_IMAGES;
    return $GALLERY_IMAGES;
}
