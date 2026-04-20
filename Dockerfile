# Utilise une image PHP 8.2 officielle avec le serveur web Apache.
FROM php:8.2-apache

# Installe les dépendances système nécessaires pour les extensions PHP.
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Installe les extensions PHP requises :
# - pdo & pdo_mysql pour la connexion à la base de données.
# - curl pour les appels API vers Paystack.
RUN docker-php-ext-install pdo pdo_mysql curl

# Active le module de réécriture d'URL d'Apache (utile pour les "clean URLs").
RUN a2enmod rewrite

# Copie les fichiers de votre application dans le répertoire web du conteneur.
COPY . /var/www/html/

# Attribue la propriété des fichiers au serveur web pour éviter les problèmes de permissions.
RUN chown -R www-data:www-data /var/www/html