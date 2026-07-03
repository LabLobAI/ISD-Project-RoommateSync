-- Migration: Enforce unique connection pair and status domain
-- Adds a composite unique constraint and a status CHECK for allowed values

ALTER TABLE connection_requests
  ADD CONSTRAINT uc_connection_pair UNIQUE (sender_id, receiver_id);

ALTER TABLE connection_requests
  ADD CONSTRAINT chk_connection_status CHECK (status IN ('PENDING','ACCEPTED','REJECTED'));

-- Note: MySQL CHECK constraints are enforced on 8.0.16+. For older versions, enforce in application logic.
