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
(2, 'Private Room near Dhanmondi Lake', 'Clean private room with Wi-Fi and attached balcony. Walking distance to the lake.', 'Dhanmondi, Dhaka', 15000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'),
(2, 'Cozy Studio in Dhanmondi', 'Fully furnished studio apartment with kitchenette and study desk.', 'Dhanmondi, Dhaka', 18000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267'),
(2, 'Shared Room near JU Campus', 'Affordable shared room perfect for students. 10 min walk to Jahangirnagar University.', 'Savar, Dhaka', 6500, 'shared', 2, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'),
(3, 'Shared Room in Bashundhara', 'Budget-friendly shared room, close to university area. WiFi included.', 'Bashundhara R/A, Dhaka', 8500, 'shared', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1484154218962-a197022b5858'),
(3, 'Private Room in Uttara', 'Modern private room in a 3-bedroom apartment. Near Airport Road.', 'Uttara, Dhaka', 13000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb'),
(3, 'Furnished Room in Mirpur', 'Fully furnished room with AC, wardrobe, and desk. Close to Mirpur 10 roundabout.', 'Mirpur, Dhaka', 14000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'),
(5, 'Private Room in Mirpur DOHS', 'Peaceful room suitable for students and professionals. Gated community.', 'Mirpur DOHS, Dhaka', 12000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'),
(5, 'Spacious Room in Banani', 'Large room with attached bathroom and balcony. Near Banani DOHS.', 'Banani, Dhaka', 20000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267'),
(1, 'Shared Apartment in Banani', 'Shared apartment with kitchen access and common lounge. Great for young professionals.', 'Banani, Dhaka', 18000, 'shared', 2, 2.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1484154218962-a197022b5858'),
(1, 'Room in Gulshan Apartment', 'Premium room in a well-maintained apartment. Near Gulshan 1 circle.', 'Gulshan, Dhaka', 22000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb'),
(4, 'Private Room in Agrabad', 'Good location in Chittagong, near commercial area. Ideal for working professionals.', 'Agrabad, Chittagong', 11000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'),
(4, 'Shared Flat in GEC Circle', 'Shared flat near GEC Circle. Walking distance to markets and restaurants.', 'GEC Circle, Chittagong', 7500, 'shared', 2, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267'),
(2, 'Room in Mohammadpur', 'Affordable private room in a quiet neighborhood. Near Mohammadpur bus stand.', 'Mohammadpur, Dhaka', 9000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'),
(3, 'Student Room in Shyamoli', 'Budget room perfect for students. Close to Shyamoli Square and colleges.', 'Shyamoli, Dhaka', 7000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1484154218962-a197022b5858'),
(5, 'Luxury Room in Dhanmondi', 'Premium room with modern furnishings, AC, and city view.', 'Dhanmondi, Dhaka', 25000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb'),
(2, 'Shared Room in Tejgaon', 'Shared room in a commercial area. Great for factory/office workers.', 'Tejgaon, Dhaka', 6000, 'shared', 3, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'),
(3, 'Room in Lalmatia', 'Quiet room in a residential area. Near Lalmatia Coloni and cafes.', 'Lalmatia, Dhaka', 11000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267'),
(5, 'Private Room in Badda', 'Spacious room with natural light. Close to Badda Link Road.', 'Badda, Dhaka', 10000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'),
(1, 'Shared Room in Rampura', 'Shared room near Rampura Banasree. Good transport links.', 'Rampura, Dhaka', 7500, 'shared', 2, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1484154218962-a197022b5858'),
(4, 'Room in Nasirabad, Chittagong', 'Private room in a peaceful area. Near Nasirabad Housing Society.', 'Nasirabad, Chittagong', 9500, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb'),
(2, 'Furnished Studio inmotijheel', 'Compact furnished studio in the business district. Perfect for professionals.', 'Motijheel, Dhaka', 16000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2'),
(3, 'Shared Room in Farmgate', 'Affordable shared room near Farmgate intersection. Metro rail accessible.', 'Farmgate, Dhaka', 8000, 'shared', 2, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267'),
(5, 'Room in Elephant Road', 'Private room near New Market and Dhaka University. Great for students.', 'Elephant Road, Dhaka', 10500, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'),
(2, 'Shared Flat in Khilgaon', 'Shared flat with 2 rooms. Common kitchen and drawing room.', 'Khilgaon, Dhaka', 9000, 'shared', 2, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1484154218962-a197022b5858'),
(4, 'Room in Chandgaon, Chittagong', 'Quiet room near Chittagong University area. Student-friendly.', 'Chandgaon, Chittagong', 8000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb'),
(3, 'Premium Room in Baridhara', 'High-end room in Baridhara J Block. Diplomatic area with security.', 'Baridhara, Dhaka', 28000, 'private', 1, 1.0, 'AVAILABLE', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2');

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
