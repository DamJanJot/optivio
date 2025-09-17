# Optivio App - README.md

## Project Description

Optivio is a comprehensive web application containing multiple mini-apps, all accessible from the main `nav.php` file.  
This project serves as a portfolio, showcasing and allowing testing of various features such as a notebook, tasks, gallery, terminal, calendar, wallet, drawing tool, and a visual board.

## Installation Guide

1. Copy the project to a local server (XAMPP, Laragon, MAMP, etc.).
2. Create a database and import the `database.sql` file.
3. Copy `.env.example` to `.env` and fill in your environment variables:

```
DB_HOST=localhost
DB_NAME=database_name
DB_USER=username
DB_PASS=password
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_FROM=your_email
MAIL_FROM_NAME=YourName
```

4. Make sure all folders have the proper permissions (e.g., `uploads/`).
5. Open `nav.php` in your browser.

## Project Structure

```
/core/                  # Core files (db, config, auth, includes)
    includes/           # header.php, footer.php, nav.php
/assets/
    css/                # styles, main.css
    js/                 # main.js, utils.js
    img/                # images and icons
/modules/               # Application modules
    notatnik/           # Notebook
    todo/               # ToDo / Tasks
    portfel/            # Wallet
    dysk/               # Drive
    rysunki/            # Drawings
    galeria/            # Gallery
    kalendarz/          # Calendar
/views/
    nav.php             # Application navigation
    profil.php          # User profile 

/uploads/               # Files uploaded by users
.env                    # Sensitive data (excluded from the repository)
.env.example            # Example .env file for GitHub
index.php               # Main entry point to run the app
```

## Modules and Features

### Notebook
* Create, edit, and delete notes.
* Data stored in the `notatki` table.

### ToDo / Tasks
* Tasks divided into categories: to-do, in progress, done.
* Goals within tasks, with checkboxes and a progress bar.
* Notifications for goals assigned to a user.
* Tables: `taski`, `cele`, `powiadomienia`.

### Gallery
* View and add photos.
* File upload support.
* Table: `galeria`.

### Terminal
* Simulated terminal in the browser.

### Calendar
* Display events.
* Add, edit, and delete events.
* Table: `kalendarz`.

### Board
* Create and edit visual notes in a board format.
* Table: `tablica`.

### Wallet
* Manage user finances.
* Table: `portfel`.

### Drawings / Painting
* In-browser drawing editor.
* Ability to save drawings to the gallery.

## Navigation
* All modules are accessible from `nav.php`.
* Top and bottom navigation bars allow switching between modules.
* Modules share core files from `/core/` and styles from `/assets/`.

## SQL File (database.sql)
* Contains tables:  
  `uzytkownicy`, `taski`, `cele`, `powiadomienia`,  
  `notatki`, `galeria`, `kalendarz`, `portfel`, `tablica`.
* All relationships and primary keys are properly defined.

## License
* MIT License

# optivio-app
