# secg4-project-health
54314 - Oudahya Younes
<br>
56149 - El Mahsini Aimen
<br>
54637 - Zidi Billal

# Project Summary

## Context
The goal of this project was to develop a secure client/server system for handling patient's medical records, similar to those found in hospitals. The main focus was on implementing robust security measures throughout the system, considering both the security policy and data storage aspects. We had the freedom to choose appropriate protocols, languages, and techniques. The project emphasized the importance of using effective security techniques, regardless of whether they were covered in class or not.

## Functionality
The system operates on a client/server architecture, involving two main actors: the server and the clients driven by users. The server provides several key functionalities, including user registration, user login, and the ability for authenticated users to edit and share medical records when appropriate. The exact implementation details were left to our discretion, allowing us to identify critical security points and employ appropriate measures accordingly.

Here are the main functionalities of the system:

## User Registration and Authentication:

New users can register to the system, providing necessary information and generating authentication credentials (passwords, cryptographic keys, etc.).
User authentication is required to access the system's features, ensuring secure communication between clients and the server.
Different types of users (administrators, doctors, patients) have specific rights and privileges within the system.

## Medical Record Management:

Patients have their own medical records, organized as directories containing various files.
Users can securely access and view their own medical records and for doctors, the records of patients assigned to them.
The system ensures the confidentiality and integrity of medical record files, protecting sensitive information within the records.

## Adding and Deleting Doctors:

Patients can add or remove doctors from their list of appointed doctors.
Approval from the patient is required when doctors initiate the addition process.

## File Management:

Patients can upload files to their medical records, edit the content of existing files, and delete files permanently.
Doctors, with patient approval, can perform file management actions on behalf of the patient.

# Windows Requirements
[OpenSSL](https://www.openssl.org/source/)
<br>
[Xampp](https://www.apachefriends.org/fr/download.html)
<br>
[Composer](https://getcomposer.org/download/)
<br>
[GitBash](https://git-scm.com/downloads)

# How setup project 
Open GitBash and navigate to the `secg4-project-health/health` directory :
```sh
Cd secg4-project-health/health
composer install
php artisan key:generate
```
Now, choose a location to store the private keys. In the `.env` file, 
set `PATH_TO_PRIVATE_KEY` to the desired directory where the private keys will be generated.

You also need to populate the database with this command:

```sh
php artisan migrate:fresh --seed
```


## Setup Xampp
Now, we will use and set up Xampp to run the project with SSL certificates, enabling HTTPS. To do this, follow these steps:

Place the "certif" folder in the `xampp/apache/conf` directory.

Add the following code to `xampp/apache/conf/extra/httpd-vhosts.conf`:

```sh

    <VirtualHost *:443>
        ServerName localhost
        DocumentRoot "/path/to/your/project/public"   
    
        SSLEngine on
        SSLCertificateFile "/path/to/your/certificat.crt"
        SSLCertificateKeyFile "/path/to/your/key_certificat.key"           	

    
        <Directory "/path/to/your/project/public">       
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>

```
Please make sure to replace `"/path/to/your/project/"` with the actual path to your project directory and `"path/to/your/certificat"` with the actual paths to your SSL certificate files.(in "`certif`" folder)

# Running the Project
Open Xampp and start the Apache server.
Now, open a web browser and navigate to `https://localhost`.

# Home page 
Arrived at this page you can connect as an administrator/patient/doctor<br>
We have already created an admin, a patient and a doctor for easy testing.<br>
-> php artisan migrate:fresh --seed<br>

An administrator is required to deploy the site, otherwise you won't be able to make changes to users.<br>
(Passwords have been deliberately kept simple for ease of testing, but you can change them if you wish in the following files : 
database/seeders/UsersTableSeeder.php)<br>
NB: it should be noted that these passwords are not at all secure and that you must have a strong password.(Just used for tests)

Administrator :<br> 
email : admin@gmail.com<br>
password : admin<br>

Patient : <br>
email : test@gmail.com<br>
password : test<br>

Doctors : <br>
email : drwho@gmail.com<br>
password : who<br>

email : drmaboul@gmail.com<br>
password : maboul<br>

## Login/Registration
To create an account you need to fill the fields.<br>
To login, you just need to put your email and password.

## Patient/Doctor/Admin
You can login as differents types of user with differents actions<br>
### Patient
- The patient can add/remove a doctor
- The patient can add/remove a file from his record
- The patient handles notification request : Accept/Reject requests from doctors
### Doctor
- The doctor can request the adding/removing of a patient record
- The doctor can remove a patient from his lists of patient
### Admin
- The admin can remove a user
- The admin can change the roles of a user



