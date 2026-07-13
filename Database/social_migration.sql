USE roommate_rental;

CREATE TABLE IF NOT EXISTS connection_requests (
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

CREATE TABLE IF NOT EXISTS user_reviews (
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

CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_message_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_message_lookup (sender_id, receiver_id, message_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO connection_requests (sender_id, receiver_id, status) VALUES
(1, 2, 'ACCEPTED'),
(2, 1, 'ACCEPTED');

INSERT IGNORE INTO user_reviews (review_id, reviewer_id, reviewee_id, cleanliness_score, communication_score, written_feedback) VALUES
(1, 1, 2, 5, 4, 'Reliable and easy to coordinate with.');

INSERT IGNORE INTO messages (message_id, sender_id, receiver_id, message_text) VALUES
(1, 1, 2, 'Hi Rakib, the room looks good. When can we talk?'),
(2, 2, 1, 'I am free this evening. Let us connect here.');
