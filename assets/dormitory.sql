CREATE DATABASE IF NOT EXISTS obeda_dormitory
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE obeda_dormitory;

-- User accounts for applicants/residents and administrators.
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    role ENUM('applicant', 'resident', 'admin') NOT NULL DEFAULT 'applicant',
    account_status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Dormitory locations managed by the system.
CREATE TABLE dormitories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    location VARCHAR(150) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Rooms belong to a dormitory location.
CREATE TABLE rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dormitory_id INT UNSIGNED NOT NULL,
    room_number VARCHAR(30) NOT NULL,
    room_type ENUM('4_person', 'female_exclusive', 'other') NOT NULL DEFAULT '4_person',
    capacity TINYINT UNSIGNED NOT NULL DEFAULT 4,
    monthly_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    available_slots TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('available', 'full', 'maintenance', 'inactive') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rooms_dormitory
        FOREIGN KEY (dormitory_id) REFERENCES dormitories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_room_number_per_dormitory UNIQUE (dormitory_id, room_number)
) ENGINE=InnoDB;

-- A user's request for an in-person dormitory viewing.
CREATE TABLE viewing_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    dormitory_id INT UNSIGNED NOT NULL,
    preferred_date DATE NOT NULL,
    preferred_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_viewings_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_viewings_dormitory
        FOREIGN KEY (dormitory_id) REFERENCES dormitories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- A user's room rental application/reservation.
CREATE TABLE room_applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    room_id INT UNSIGNED NOT NULL,
    move_in_date DATE DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_applications_room
        FOREIGN KEY (room_id) REFERENCES rooms(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Useful starter dormitory locations based on the website content.
INSERT INTO dormitories (name, location, description) VALUES
('Obeda Dormitories - Daro', 'Daro', 'Obeda Dormitories location in Daro, Dumaguete City.'),
('Obeda Dormitories - Piapi', 'Piapi', 'Obeda Dormitories location in Piapi, Dumaguete City.'),
('Obeda Dormitories - Batinguel', 'Batinguel', 'Obeda Dormitories location in Batinguel, Dumaguete City.'),
('Obeda Dormitories - Cadawinonan', 'Cadawinonan', 'Obeda Dormitories location in Cadawinonan, Dumaguete City.'),
('Obeda Dormitories - Taclobo', 'Taclobo', 'Obeda Dormitories location in Taclobo, Dumaguete City.');
