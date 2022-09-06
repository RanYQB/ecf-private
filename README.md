
# FITNESS CLUB APP

L'application web Fitness Club est un outil de gestion en ligne. Elle est destinée à être utilisée par une marque de salle de sport (dans mon exemple il s'agit de la marque "Fitness Club", mais le nom reste à titre indicatif).
Cet outil permettra à l'équipe technique (gestionnaires de la marque) de :
1. se connecter à l'application
2. enrichir la base de données en créant de nouveaux partenaires (franchises) et de nouvelles structures (salles de sport, affiliées à un partenaire précis)
3. désactiver et activer à sa guise les comptes des partenaires et des structures
4. octroyer diverses permissions aux partenaires et aux structures (ex: envoi d'une Newsletter, gestion des plannings des cours etc.), tout en gardant une fléxibilité sur leur attribution grâce à une personnalisation spécifique à chaque structure indépendamment de son partenaire.
5. envoyer des emails de notification aux partenaires et aux structures à chaque modification
6. abandonner une action si elle n'est pas confirmée
7. effectuer des recherches dynamiques sans rechargement des pages pour plus de fluidité

L'objectif de cette application web est le suivant : **Définir et modifier les permissions des partenaires et des structures afin de configurer les modules auxquels ils auront accès dans une application tiers (proposée aussi par la même marque et connectée à la même base de données que la présente application web).**

Sur l'application web Fitness Club, les partenaires et les structures pourront également se connecter et consulter en lecture seule leurs informations personnelles et les permissions qui leur sont octroyées.


## **Installation en local**
------

### **1. Cloner le projet et installation des dépendances**


Clonez le projet dans un répertoire de votre choix

```bash
  git clone https://github.com/RanYQB/ecf-private.git
```

Rendez-vous dans le répertoire

```bash
  cd ecf-private
```

Installez les dépendances Symfony

```bash
  composer install
```

Installez les dépendances Webpack Encore - Node JS

```bash
  npm install
```

Générez le répertoire build

```bash
  npm run watch
```

### **2. Configuration d'apache**

Sur votre disque local, rendez-vous dans votre répertoire xampp/apache/conf/extra.
Ouvrez le fichier **httpd-vhosts.conf** et saisissez ce bloc en fin de fichier en prenant soin d'écrire la bonne **route** là où le mot est présent.

```
<VirtualHost *:80>
    ServerName symfony.localhost
    DocumentRoot "c:/route/ecf-private/public"
    DirectoryIndex index.php
		    
    <Directory "c:/route/ecf-private/public">
	  Require all granted
	  FallbackResource /index.php
    </Directory>
</VirtualHost>
```

### **3. Définition des variables d'environnement**

Copiez le document **.env** et collez le à la racine du projet en le renommant **.env.local**
Les variables que vous allez définir devront toutes être décommentées.

`APP_ENV=dev`

`APP_SECRET=Saisissez ici une chaine de caractère secrète`

Saisissez vos identifiants PhpMyAdmin et définissez le nom de votre base de données dans la variable suivante :

`DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"`

Pour en savoir plus :
[Documentation Symfony - Installation d'une base de données ](https://symfony.com/doc/current/doctrine.html#installing-doctrine)

Configurez un DSN SMTP :

`MAILER_DSN=smtp://localhost:port`

Si vous utilisez SendGrid :

`MAILER_DSN=sendgrid+smtp://KEY@default`

En dernier, créez la variable JWT Secret :

`JWT_SECRET='Saisissez ici votre chaine de caractère secrète'`

### **4. Création de la base de données et exécution des fichiers migrations**

Créez la base de données avec le terminal du projet
```
$ php bin/console doctrine:database:create
```

Exécutez les migrations
```
$ php bin/console doctrine:migrations:migrate
```
Vous pouvez à présent vous rendre sur localhost depuis votre navigateur.

## **Déploiement en ligne avec Heroku**
------

### **1. Création du projet sur Heroku**

Vérifiez dans un premier temps que vous disposez bien du Heroku CLI.

Pour l'installer : [Heroku CLI](https://devcenter.heroku.com/articles/heroku-cli)

Depuis le terminal du projet, connectez-vous à Heroku

```bash
  heroku login
```
Créez un nouveau projet Heroku

```
heroku create nom-projet
```
Reliez l'application web à votre dépôt Heroku

```
heroku git:remote -a nom-projet
```


### **2. Ajouter une base de données sur Heroku**

Ajoutez une base de données au projet sur Heroku en installant un add-on.

A titre d'exemple : [JawsDB MySQL](https://elements.heroku.com/addons/jawsdb)

### **3. Définition des variables d'environnement**

Depuis le terminal du projet :

Configurez l'environnement en environnement de production
```
heroku config:set APP_ENV=prod 
```
Configurez votre base de données en reprenant les informations fournies dans l'étape 2
```
heroku config:set DATABASE_URL="mysql://..."
```
De la même manière, définissez les trois variables suivantes

`APP_SECRET=`

`MAILER_DSN=sendgrid+smtp://KEY@default`

`JWT_SECRET='Saisissez ici votre chaine de caractère secrète'`


### **4. Ajout du buildpack Node JS**

L'application web utilise Webpack Encore. Ajoutez le buildpack correspondant

```
heroku buildpacks :add heroku/nodejs
```

### **5. Déployer l'application**

Exécutez les commandes suivantes :
```
git push heroku main

heroku run php bin/console doctrine:migrations:migrate
```

En cas d'erreur, vérifiez vos logs avec :
```
heroku logs --tail
```