-- database.sql
-- Struktura tabel dla aplikacji Optivio

-- Użytkownicy
CREATE TABLE IF NOT EXISTS uzytkownicy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nazwa VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    haslo VARCHAR(255) NOT NULL,
    zdjecie_profilowe VARCHAR(255),
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Taski
CREATE TABLE IF NOT EXISTS taski (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tytul VARCHAR(255) NOT NULL,
    opis TEXT,
    status ENUM('do_zrobienia', 'w_trakcie', 'wykonane') DEFAULT 'do_zrobienia',
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uzytkownik_id INT,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);

-- Cele
CREATE TABLE IF NOT EXISTS cele (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tytul VARCHAR(255) NOT NULL,
    opis TEXT,
    status BOOLEAN DEFAULT 0,
    task_id INT,
    FOREIGN KEY (task_id) REFERENCES taski(id) ON DELETE CASCADE
);

-- Powiadomienia
CREATE TABLE IF NOT EXISTS powiadomienia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tresc TEXT NOT NULL,
    uzytkownik_id INT,
    przeczytane BOOLEAN DEFAULT 0,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);

-- Notatki
CREATE TABLE IF NOT EXISTS notatki (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tytul VARCHAR(255),
    tresc TEXT,
    uzytkownik_id INT,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);

-- Galeria
CREATE TABLE IF NOT EXISTS galeria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nazwa_pliku VARCHAR(255) NOT NULL,
    uzytkownik_id INT,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);

-- Kalendarz
CREATE TABLE IF NOT EXISTS kalendarz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tytul VARCHAR(255) NOT NULL,
    opis TEXT,
    data_start DATETIME,
    data_end DATETIME,
    uzytkownik_id INT,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);

-- Portfel
CREATE TABLE IF NOT EXISTS portfel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nazwa VARCHAR(255) NOT NULL,
    kwota DECIMAL(10,2) NOT NULL,
    typ ENUM('przychod','wydatek') NOT NULL,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uzytkownik_id INT,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);

-- Tablica
CREATE TABLE IF NOT EXISTS tablica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tytul VARCHAR(255),
    tresc TEXT,
    uzytkownik_id INT,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uzytkownik_id) REFERENCES uzytkownicy(id) ON DELETE CASCADE
);