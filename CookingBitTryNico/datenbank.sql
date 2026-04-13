CREATE DATABASE IF NOT EXISTS rezeptedatenbank;
USE rezeptedatenbank;

CREATE TABLE recipes (
    recipe_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_name VARCHAR(255) NOT NULL,
    recipe_description TEXT,
    portions INT,
    author_id INT NOT NULL
);

CREATE TABLE ingredients (
    ingredients_id INT AUTO_INCREMENT PRIMARY KEY,
    ingredients_name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE recipes_ingredients (
    recipe_id INT,
    ingredients_id INT,
    amount VARCHAR(50), 
    unit VARCHAR(50), 
    PRIMARY KEY (recipe_id, ingredients_id),
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredients_id) REFERENCES ingredients(ingredients_id) ON DELETE CASCADE
);

CREATE TABLE category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE recipe_categories(
    recipe_id INT,
    category_id INT,
    PRIMARY KEY (recipe_id, category_id),
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

CREATE TABLE steps (
    step_id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    stepnumber INT NOT NULL,
    step_description TEXT NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(recipe_id) ON DELETE CASCADE,
    UNIQUE (recipe_id, stepnumber)
);

CREATE TABLE author(
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    author_name VARCHAR(255) NOT NULL UNIQUE
);