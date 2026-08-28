# Piraeus MyCity App — Documentation

## Table of Contents

1. [Introduction](#1-introduction)
2. [User Manual](#2-user-manual)
   - [2.1 Installing & running the application](#21-installing--running-the-application)
     - [2.1.1 Using XAMPP](#211-using-xampp)
     - [2.1.2 Using Docker](#212-using-docker)
   - [2.2 Submitting an issue report (Interface 1)](#22-submitting-an-issue-report-interface-1)
   - [2.3 Viewing and searching reports (Interface 2)](#23-viewing-and-searching-reports-interface-2)
   - [2.4 Viewing report details (Interface 3)](#24-viewing-report-details-interface-3)
   - [2.5 Administrator Dashboard (Interface 4)](#25-administrator-dashboard-interface-4)
   - [2.6 Responsive design for mobile devices](#26-responsive-design-for-mobile-devices)
   - [2.7 Security](#27-security)
3. [Future Improvements](#3-future-improvements)
   - [3.1 Security](#31-security)
   - [3.2 Functionality](#32-functionality)
   - [3.3 User Experience](#33-user-experience)

---

## 1. Introduction

This document constitutes the documentation for the web-based issue reporting application, Piraeus MyCity. The application implements an issue reporting system for the city of Piraeus, through which citizens can log everyday problems such as: damage to road infrastructure, water supply network faults, or gaps in municipal lighting. Reports can be submitted either under the citizen's name or anonymously.

The implementation is based on HTML5, CSS3, JavaScript, and PHP for the frontend and backend respectively, with the Bootstrap 5 library used for the user interface (UI) design and Leaflet.js for displaying maps with OpenStreetMap data. Data storage is handled by a MySQL database, with PHP–MySQL communication implemented exclusively through the mysqli library using prepared statements, to ensure protection against SQL injection attacks.

The document is organized into the following sections: after this introduction, Section 2 presents the application's user manual in detail, with step-by-step instructions for both installing and running it, as well as for using each individual feature. Section 3 covers future improvements that could be made to the application in the areas of security, functionality, and user experience.

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
Download Docker Desktop from the Docker website (docker.com/products/docker-desktop), run it, and leave it running in the background. Within the app, in the bottom-left corner of the window, the indicator *Engine running* should appear once it's ready.

**Step 2: Run the application**
Open a terminal in the project folder and run the command:
`docker compose up --build`


After completion, the application is accessible at: http://localhost:8080, and phpMyAdmin at: http://localhost:8081

Useful information about shutting down the application and/or deleting its contents can be found in the `README.md` file in the `code/` folder.

For the admin account, see the note in subsection 2.1.1, step 6.

---

### 2.2 Submitting an issue report (Interface 1)

The application's home page (index.php) is the entry point for citizens, who can submit a new issue report. The page includes an intro section with a brief three-step description of the process, followed by the submission form.

The form includes fields for the issue title, category (selected from a dropdown menu dynamically populated from the database), the address, a detailed description, the submission type (named or anonymous), and an optional video attachment.

The submit button is initially disabled and becomes active dynamically, via JavaScript, only once all required fields are correctly filled in. As the form is filled out, each field is validated in real time, and in case of an error, a corresponding message appears below the field with a red highlight.

Upon submission, the application converts the address into geographic coordinates via the Nominatim API, automatically calculates the report's priority level based on the category and submission time, and generates a unique tracking code in the format CR-XXXXX. It is also stored in a browser cookie, so that a history of reports submitted from that specific device is maintained.

---

### 2.3 Viewing and searching reports (Interface 2)

The `browse.php` page displays all submitted reports in card format, with filtering and search capability.

Each card displays the title, category, status (color-coded), submission date, and the details of the citizen who submitted it. Clicking on any card takes the user to the detail page for that report.

The page offers four search filters: category, status, date, and username. The filters can be freely combined, with the selected criteria reflected in the page URL.

Additionally, there is a quick search field based on username, as well as a search field for the unique Ticket ID, which leads directly to the detail page of the corresponding report. If no results match the selected criteria, a relevant informational message is displayed.

---

### 2.4 Viewing report details (Interface 3)

The `detail.php` page displays all the information for a specific report: title, tracking code, category, status, full description, address, and the details of the citizen who submitted it.

At the bottom of the page, an interactive map is embedded, implemented with the Leaflet.js library and OpenStreetMap data, showing a pin at the report's location, based on the coordinates obtained from geocoding at submission time.

If the report includes an attached video, it is displayed embedded in the page, with immediate playback capability. If a report is searched for using a non-existent identifier, an appropriate message is shown to inform the user.

---

### 2.5 Administrator Dashboard (Interface 4)

Before logging in, the shared navigation menu shows the "Administrator Login" option. Selecting it takes the user to the login form (login.php). If incorrect credentials are entered, a corresponding error message is displayed.

After a successful login, the navigation menu updates dynamically, showing the "Dashboard" and "Logout" options.

The administrator dashboard (admin.php) presents all reports in table format, with an additional priority column compared to the public viewing page, as well as the ability to filter based on it. For each report, the administrator can change its processing status (Submitted, In Progress, Resolved) via a dropdown list, as well as delete it — an action accompanied by a confirmation message to prevent accidental deletions. After each action, a corresponding success message is displayed.

Access to the dashboard is protected and permitted exclusively to logged-in administrators. Any attempt to access the `admin.php` address directly without a prior login automatically redirects to the login page.

---

### 2.6 Responsive design for mobile devices

All pages of the application are designed to adapt dynamically to the user's screen dimensions. On tablet and mobile screens, the navigation menu collapses into a "hamburger" icon, which, when clicked, expands into the full menu.

Similarly, the shared footer, which appears in a two-column layout on desktop screens, adapts to a single column on smaller screens.

---

### 2.7 Security

During the implementation of the application, protective measures were taken against common web application vulnerabilities. Specifically:

- Communication with the database is carried out exclusively through prepared statements, to prevent SQL injection attacks at every point where user-supplied data is inserted.
- Every piece of user-supplied data passes through the `htmlspecialchars()` function, to prevent Cross-Site Scripting.
- The administrator's password is stored in the database in encrypted (hashed) form, via the `password_hash()` function.
- Access to administration pages is controlled via a session mechanism, with a check performed on every request before any content is displayed.
- When uploading video files, both the file extension and the actual content type (MIME type) are validated.

---

## 3. Future Improvements

### 3.1 Security

- The connection to the database is made through XAMPP's default `root` user, without a password. It is necessary to create a dedicated MySQL user with restricted privileges, limited exclusively to the `mycity` database; otherwise there will be unrestricted access to the database.
- It would also be prudent to integrate the HTTPS protocol into the application for secure data transmission.

### 3.2 Functionality

- A "My Reports" page could be added, which would make use of the cookie storing the citizen's saved Ticket IDs to display their report history without them needing to remember the codes.
- Priority calculation could be done dynamically at display time instead of at submission time, so that it changes automatically as time passes.
- Support for multiple file attachments per report.
- Sending an email notification to the citizen when their report's status changes.
- Adding pagination to the report list for efficient handling of large volumes of data.

### 3.3 User Experience

- Adding the ability to select the issue's location directly by clicking on the map, as an alternative to typing an address.
- Adding a consolidated map on the viewing page, showing all active reports simultaneously.
- Adding summary statistics to the administrator dashboard, such as the number of reports per category or status.
