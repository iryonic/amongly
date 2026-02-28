-- Seed Data for Amongly
USE `amongly`;

INSERT INTO `categories` (`name`) VALUES ('Animals'), ('Food'), ('Movies'), ('Travel');

-- Animals
INSERT INTO `words` (`category_id`, `word`, `difficulty`) VALUES 
(1, 'Elephant', 'easy'),
(1, 'Giraffe', 'easy'),
(1, 'Penguin', 'easy'),
(1, 'Kangaroo', 'medium'),
(1, 'Platypus', 'hard');

-- Food
INSERT INTO `words` (`category_id`, `word`, `difficulty`) VALUES 
(2, 'Pizza', 'easy'),
(2, 'Sushi', 'easy'),
(2, 'Burger', 'easy'),
(2, 'Spaghetti', 'medium'),
(2, 'Ratatouille', 'hard');
