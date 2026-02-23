-- Données de base : cailloux en vente (à exécuter APRÈS schema.sql)
-- Les articles sont attribués au compte admin (id = 1)
-- Oui, ce sont juste des cailloux. Rien de précieux.

USE php_exam_db;

-- Cailloux tout simples, de tailles et formes différentes
INSERT INTO `article` (`name`, `description`, `price`, `author_id`, `image_link`) VALUES
('Galet de Batard', 'Tema La circonférience du Galet', 0.99, 1, 'uploads/art_699c572addd86.jpg'),
('Gros caillou', 'Un gros caillou. Plus lourd que le petit. Idéal si vous aimez les choses qui pèsent un peu.', 2.50, 1, NULL),
('Caillou rond', 'Un caillou rond. Enfin, rond-ish. La nature ne fait pas de parfaits. Très caillou.', 1.20, 1, NULL),
('Caillou plat', 'Plat. Genre galet. Vous pouvez le faire ricocher sur l\'eau si vous avez le bras. Ou le poser sur une étagère.', 1.50, 1, NULL),
('Caillou pointu', 'Attention aux doigts. C\'est pointu. En pierre. Un vrai caillou avec du caractère.', 1.80, 1, NULL),
('Très petit caillou', 'Minuscule. Presque un grain. Parfait pour les collectionneurs de cailloux minimalistes.', 0.50, 1, NULL),
('Caillou moyen', 'Taille moyenne. Ni petit ni gros. Le juste milieu. La pierre philosophale du pauvre.', 1.00, 1, NULL),
('Caillou bizarre', 'On ne sait pas trop. Une forme. De la pierre. Vous jugerez.', 1.99, 1, NULL);

-- Stock pour chaque article (id 1 à 8)
INSERT INTO `stock` (`article_id`, `quantity`) VALUES
(1, 50),
(2, 30),
(3, 40),
(4, 35),
(5, 25),
(6, 100),
(7, 60),
(8, 45);
