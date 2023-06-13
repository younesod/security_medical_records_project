# secg4-project-health
54314 - Oudahya Younes
# script
Lancer le script pour installer les dépendances
```sh
chmod +x script.sh
./script.sh
```


# how setup project 

Installer [xampp](https://www.apachefriends.org/fr/download.html)
```sh
Cd secg4-project-health
composer install
```
Dans le .env, `PATH_TO_PRIVATE_KEY`= xxxx (xxx dossier ou vont etre generer les clées privées)


# How setup xampp

## Windows

Placer le dossier "certif" dans le dossier ` "xampp/apache/conf" `

Placer le code ci-dessous dans ` xampp/apache/conf/extra/httpd-vhosts.conf`


```sh

    <VirtualHost *:443>
        ServerName localhost
        DocumentRoot "/chemin/vers/votre/projet/public"   
    
        SSLEngine on
        SSLCertificateFile "/chemin/vers/votre/certificat.crt"
        SSLCertificateKeyFile "/chemin/vers/votre/cle_certificat.key"           	

    
        <Directory "/chemin/vers/votre/projet/public">       
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>

```

## Linux
Taper :
```sh
sudo nano /opt/lampp/etc/httpd.conf
```
Placer le dossier certif dans ` /opt/lampp/etc`

Recherchez la ligne contenant `#Include etc/extra/httpd-vhosts.conf` et supprimez le signe dièse (#) au début de la ligne pour activer l'inclusion du fichier de configuration des virtual hosts.

Ensuite taper : 
```sh
sudo nano /opt/lampp/etc/extra/httpd-vhosts.conf 
```
et ajouter ceci 
```sh
<VirtualHost *:443>
    ServerName localhost
    DocumentRoot "/chemin/vers/votre/projet"

    SSLEngine on
    SSLCertificateFile "/chemin/vers/votre/certificat.crt"
    SSLCertificateKeyFile "/chemin/vers/votre/cle_certificat.key"

    <Directory "/chemin/vers/votre/projet">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Changer les droits pour accéder au projet 

Ensuite changer les droits pour le projet avec 
```sh
chmod 777 -R  /chemin du projet
```
Restart le serveur
```sh
sudo /opt/lampp/lampp restart     
```
Ensuite taper sur un navigateur https://localhost


Home page 
Arrived at this page you can connect as an administrator/patient/doctor
We have already created an admin, a patient and a doctor for easy testing.
-> php artisan migrate:fresh --seed
An administrator is required to deploy the site, otherwise you won't be able to make changes to users.
(Passwords have been deliberately kept simple for ease of testing, but you can change them if you wish in the following files : 
database/seeders/UsersTableSeeder.php)
Administrator : 
email : admin@gmail.com
password : admin

Patient : 
email : test@gmail.com
password : test

Doctors : 
email : drwho@gmail.com
password : who

email : drmaboul@gmail.com
password : maboul



