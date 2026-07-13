-- RoommateSync consolidated database import for phpMyAdmin/XAMPP
-- Import this file into MySQL running on port 3307.

CREATE DATABASE IF NOT EXISTS roommate_rental
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE roommate_rental;

DROP TABLE IF EXISTS bill_log_roommates;
DROP TABLE IF EXISTS bill_logs;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS user_reviews;
DROP TABLE IF EXISTS connection_requests;
DROP TABLE IF EXISTS listings;
DROP TABLE IF EXISTS user_profile_tags;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    city VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL DEFAULT '',
    remember_token_hash VARCHAR(200) DEFAULT NULL,
    remember_token_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_profiles (
    user_id INT PRIMARY KEY,
    cleanliness TINYINT NOT NULL CHECK (cleanliness BETWEEN 1 AND 5),
    sleep_start TIME NOT NULL,
    sleep_end TIME NOT NULL,
    wfh_status ENUM('yes', 'no', 'hybrid') NOT NULL DEFAULT 'no',
    smoking_ok TINYINT(1) NOT NULL DEFAULT 0,
    pets_ok TINYINT(1) NOT NULL DEFAULT 0,
    budget_min DECIMAL(10,2) DEFAULT 0.00,
    budget_max DECIMAL(10,2) DEFAULT 99999.00,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_profile_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tag VARCHAR(80) NOT NULL,
    CONSTRAINT fk_tags_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_tag (user_id, tag)
) ENGINE=InnoDB;

CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    landlord_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT,
    location_text VARCHAR(180) NOT NULL,
    rent DECIMAL(10,2) NOT NULL,
    room_type ENUM('private', 'shared') NOT NULL,
    bedrooms TINYINT NOT NULL DEFAULT 1,
    bathrooms DECIMAL(3,1) NOT NULL DEFAULT 1.0,
    status ENUM('AVAILABLE', 'BOOKED', 'HIDDEN') NOT NULL DEFAULT 'AVAILABLE',
    image_url VARCHAR(500),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_listings_landlord FOREIGN KEY (landlord_id) REFERENCES users(id),
    INDEX idx_listing_search (status, rent, room_type),
    INDEX idx_listing_location (location_text)
) ENGINE=InnoDB;

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    tenant_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    booking_status ENUM('PENDING', 'CONFIRMED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointments_listing FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    CONSTRAINT fk_appointments_tenant FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    CHECK (end_time > start_time),
    INDEX idx_appointment_lookup (listing_id, booking_status, start_time, end_time),
    INDEX idx_appointment_tenant (tenant_id, start_time)
) ENGINE=InnoDB;

CREATE TABLE bill_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    bill_name VARCHAR(120) NOT NULL DEFAULT 'Shared Bill',
    total_bill DECIMAL(10,2) NOT NULL,
    combined_income DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bill_creator FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE bill_log_roommates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_log_id INT NOT NULL,
    roommate_name VARCHAR(120) NOT NULL,
    income DECIMAL(10,2) NOT NULL,
    contribution DECIMAL(10,2) NOT NULL,
    percentage_share DECIMAL(5,2) NOT NULL,
    CONSTRAINT fk_bill_roommates_bill FOREIGN KEY (bill_log_id) REFERENCES bill_logs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE connection_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('PENDING', 'ACCEPTED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_connection_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_connection_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_connection_pair (sender_id, receiver_id),
    INDEX idx_connection_lookup (receiver_id, status),
    INDEX idx_connection_sender (sender_id, status)
) ENGINE=InnoDB;

CREATE TABLE user_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    cleanliness_score TINYINT NOT NULL CHECK (cleanliness_score BETWEEN 1 AND 5),
    communication_score TINYINT NOT NULL CHECK (communication_score BETWEEN 1 AND 5),
    written_feedback TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_reviewee FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_review_reviewee (reviewee_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_message_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_message_lookup (sender_id, receiver_id, message_id)
) ENGINE=InnoDB;

INSERT INTO users (full_name, email, city, password_hash) VALUES
('Ayesha Rahman', 'ayesha@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq'),
('Rakib Hasan', 'rakib@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq'),
('Nusrat Karim', 'nusrat@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq'),
('Sajid Ahmed', 'sajid@example.com', 'Chittagong', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq'),
('Tania Akter', 'tania@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq');

INSERT INTO user_profiles (user_id, cleanliness, sleep_start, sleep_end, wfh_status, smoking_ok, pets_ok, budget_min, budget_max) VALUES
(1, 5, '23:00:00', '07:00:00', 'hybrid', 0, 1, 8000, 18000),
(2, 4, '23:30:00', '07:30:00', 'yes', 0, 1, 9000, 17000),
(3, 2, '02:00:00', '10:00:00', 'no', 1, 0, 6000, 12000),
(4, 3, '00:00:00', '08:00:00', 'hybrid', 0, 0, 7000, 14000),
(5, 5, '22:30:00', '06:30:00', 'hybrid', 0, 1, 10000, 20000);

INSERT INTO user_profile_tags (user_id, tag) VALUES
(1, 'reading'), (1, 'cooking'), (1, 'gym'), (1, 'quiet'),
(2, 'reading'), (2, 'gym'), (2, 'movies'), (2, 'quiet'),
(3, 'gaming'), (3, 'late-night'), (3, 'movies'),
(4, 'football'), (4, 'cooking'), (4, 'music'),
(5, 'reading'), (5, 'cooking'), (5, 'quiet'), (5, 'plants');

INSERT INTO listings (landlord_id, title, description, location_text, rent, room_type, bedrooms, bathrooms, status, image_url) VALUES
(2, 'Private Room near Dhanmondi Lake', 'Clean private room with Wi-Fi and attached balcony.', 'Dhanmondi, Dhaka', 15000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'),
(3, 'Shared Room in Bashundhara', 'Budget-friendly shared room, close to university area.', 'Bashundhara R/A, Dhaka', 8500, 'shared', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267'),
(5, 'Private Room in Mirpur DOHS', 'Peaceful room suitable for students and professionals.', 'Mirpur, Dhaka', 12000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'),
(1, 'Shared Apartment in Banani', 'Shared apartment with kitchen access and common lounge.', 'Banani, Dhaka', 18000, 'shared', 2, 2.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1484154218962-a197022b5858'),
(4, 'Private Room in Agrabad', 'Good location in Chittagong, near commercial area.', 'Agrabad, Chittagong', 11000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb');

INSERT INTO appointments (listing_id, tenant_id, start_time, end_time, booking_status) VALUES
(1, 1, '2026-07-05 10:00:00', '2026-07-05 10:30:00', 'CONFIRMED'),
(1, 3, '2026-07-05 14:00:00', '2026-07-05 14:30:00', 'PENDING');

INSERT INTO connection_requests (sender_id, receiver_id, status) VALUES
(1, 2, 'ACCEPTED'),
(2, 1, 'ACCEPTED');

INSERT INTO user_reviews (reviewer_id, reviewee_id, cleanliness_score, communication_score, written_feedback) VALUES
(1, 2, 5, 4, 'Reliable and easy to coordinate with.');

INSERT INTO messages (sender_id, receiver_id, message_text) VALUES
(1, 2, 'Hi Rakib, the room looks good. When can we talk?'),
(2, 1, 'I am free this evening. Let us connect here.');
