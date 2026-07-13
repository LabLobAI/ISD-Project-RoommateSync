USE roommate_rental;

INSERT INTO users (full_name, email, city, password_hash, role) VALUES
('Ayesha Rahman', 'ayesha@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq', 'tenant'),
('Rakib Hasan', 'rakib@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq', 'landlord'),
('Nusrat Karim', 'nusrat@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq', 'tenant'),
('Sajid Ahmed', 'sajid@example.com', 'Chittagong', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq', 'landlord'),
('Tania Akter', 'tania@example.com', 'Dhaka', '$2y$12$2GFFMyyshhiF/YrthnbLq.DP0cV0ZUuxI0/3ywuQYUxyDo78w0uOq', 'tenant');

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
