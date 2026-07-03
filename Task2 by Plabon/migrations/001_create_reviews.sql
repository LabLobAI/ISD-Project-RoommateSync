-- Migration: Create users, connection_requests, and user_reviews tables
-- Adjust types/constraints for MySQL 8+; adapt as needed for other RDBMS

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS connection_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT UNSIGNED NOT NULL,
  receiver_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(32) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cr_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cr_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_cr_status (status),
  INDEX idx_cr_pair (sender_id, receiver_id)
);

CREATE TABLE IF NOT EXISTS user_reviews (
  review_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reviewer_id BIGINT UNSIGNED NOT NULL,
  reviewee_id BIGINT UNSIGNED NOT NULL,
  cleanliness_score TINYINT UNSIGNED NOT NULL,
  communication_score TINYINT UNSIGNED NOT NULL,
  written_feedback VARCHAR(1000),
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ur_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ur_reviewee FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE,
  CHECK (cleanliness_score BETWEEN 1 AND 5),
  CHECK (communication_score BETWEEN 1 AND 5),
  INDEX idx_ur_pair (reviewer_id, reviewee_id),
  INDEX idx_ur_reviewee (reviewee_id)
);

-- Notes:
-- MySQL supports CHECK constraints starting in 8.0.16 but enforcement may vary by engine/version.
-- For Oracle, adjust types (NUMBER, VARCHAR2) and sequence/trigger-based PKs accordingly.
