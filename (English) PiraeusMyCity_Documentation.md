# Piraeus MyCity App — Documentation

## Contents

1. Introduction .............................................................................................................. 4
2. User Manual .............................................................................................................. 5
   2.1 Installing & running the application .................................................................. 5
      2.1.1 Using XAMPP ............................................................................................. 5
      2.1.2 Using Docker .............................................................................................. 6
   2.2 Submitting an issue report (Interface 1) ............................................................ 8
   2.3 Viewing and searching reports (Interface 2) ...................................................... 9
   2.4 Viewing report details (Interface 3) .................................................................. 10
   2.5 Administrator Dashboard (Interface 4) ............................................................. 11
   2.6 Responsive design for mobile devices .............................................................. 12
   2.7 Security ............................................................................................................ 12
3. Future Improvements ................................................................................................ 13
   3.1 Security ............................................................................................................ 13
   3.2 Functionality ..................................................................................................... 13
   3.3 User Experience ............................................................................................... 13

---

## 1. Introduction

This document constitutes the documentation for the web-based issue reporting application, Piraeus MyCity. The application implements an issue reporting system for the city of Piraeus, through which citizens can log everyday problems such as: damage to road infrastructure, water supply network faults, or gaps in municipal lighting. Reports can be submitted either under the citizen's name or anonymously.

The implementation is based on HTML5, CSS3, JavaScript, and PHP for the frontend and backend respectively, with the Bootstrap 5 library used for the user interface (UI) design and Leaflet.js for displaying maps with OpenStreetMap data. Data storage is handled by a MySQL database, with PHP–MySQL communication implemented exclusively through the mysqli library using prepared statements, to ensure protection against SQL injection attacks.

The document is organized into the following sections: after this introduction, Section 2 presents the application's user manual in detail, with step-by-step instructions for both installing and running it, as well as for using each individual feature, accompanied by corresponding screenshots. Section 3 covers future improvements that could be made to the application in the areas of security, functionality, and user experience.

---

## 2. User Manual

### 2.1 Installing & running the application

#### 2.1.1 Using XAMPP

**Step 1: Install XAMPP**
Download and install XAMPP from the official website (apachefriends.org), selecting the Apache, MySQL, and PHP packages. After installation, open the XAMPP Control Panel and start the Apache and MySQL services.

**Step 2: Place the code**
Copy the project folder (mycity) into the `htdocs` folder of the XAMPP installation, so that the application is accessible via the address http://localhost/mycity/.

**Step 3: Create the database**
Open phpMyAdmin (http://localhost/phpmyadmin) and run the SQL script included in the `/db` folder, which creates the `mycity` database with the required tables (categories, issues, admins) and inserts the predefined issue categories.

**Step 4: Set permissions on the uploads/ folder (Unix-based systems only)**
The `uploads/` folder, where videos attached by citizens are stored, must have write permissions for the web server user. Also run the command `chmod 777 uploads` from the terminal, inside the project folder.

**Step 5: php.ini settings**
For geocoding to work correctly, the curl extension is required, which is enabled in the php.ini file (XAMPP/xamppfiles/etc/) by removing the semicolon from the line `extension=curl`. Additionally, to support larger video files, it is recommended to increase the values of `upload_max_filesize` and `post_max_size` to at least 25MB and 30MB respectively.

**Step 6: Create an administrator account**
Since public administrator registration is not supported, the administrator account is created directly in the database. The test account credentials are: username: **admin**, password: **Admin1234**. (see README.md)

**Step 7: Run the application**
Open a browser and navigate to http://localhost/mycity/, where the application's home page will appear.

#### 2.1.2 Using Docker

To avoid manual installation and environment configuration, the application also comes with Docker files that allow it to run on any system with a single command. On first startup, the `mycity` database is created automatically and the SQL script in the `/db` folder runs without user intervention, creating the tables and inserting the predefined categories. Permissions on the `uploads` folder are set automatically.

**Step 1: Install and run Docker**
Download Docker Desktop from the Docker website (docker.com/products/docker-desktop), run it, and leave it running in the background. Within the app, in the bottom-left corner of the window, the indicator *Engine running* should appear.

**Step 2: Run the application**
Open a terminal in the project folder and run the command:
