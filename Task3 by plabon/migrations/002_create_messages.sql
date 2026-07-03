-- Migration: Create messages table for direct user-to-user chat
-- Adjust types for MySQL; adapt for Oracle if needed

CREATE TABLE IF NOT EXISTS messages (
  message_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT UNSIGNED NOT NULL,
  receiver_id BIGINT UNSIGNED NOT NULL,
  message_text VARCHAR(1000) NOT NULL,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_msg_pair (sender_id, receiver_id),
  INDEX idx_msg_sent_at (sent_at)
);

-- Enforce that reads must be guarded by an ACCEPTED connection request in application code.
