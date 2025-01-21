# On clone le dépot !
git clone https://github.com/StephaneBouret/numerology.git

# On se déplace dans le dossier
cd numerology

# On installe les dépendances !
composer install

# On crée un compte mailtrap !
https://mailtrap.io
Une fois connecté, on arrive sur le tableau de bord et, normalement et on clique sur My Inbox
On clique sur l'onglet SMTP Settings, puis dans le menu déroulant Integration, on cherche PHP et Symfony 5+. Enfin, il suffit juste de copier la ligne "MAILER_DSN" et la coller dans le fichier .env

# On modifie le fichier .env
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"

# On créé la base de données
php bin/console doctrine:database:create

# On exécute les migrations
php bin/console doctrine:migrations:migrate

# On exécute la fixture
php bin/console doctrine:fixtures:load --no-interaction

# On lance le serveur
php bin/console server:run ou symfony serve
