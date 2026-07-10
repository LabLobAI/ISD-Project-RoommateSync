# Landlord Room Creation Flow & Asset Upload

## Frontend link

If the folder is placed inside XAMPP `htdocs` as:

`C:\xampp\htdocs\Task_Landlord_Room_Creation_Upload`

Open:

`http://localhost/RoommateSync_Taskwise_OneDatabase_Final/Task_Landlord_Room_Creation_Upload/public/create_listing.php`

## Purpose

This task allows a landlord or host to create a rental listing and upload a room/property image.

## Important files

- `public/create_listing.php` — frontend form and backend upload/database logic
- `public/assets/css/style.css` — page styling
- `public/assets/js/create_listing.js` — client-side image preview and validation
- `public/uploads/` — uploaded room images are stored here
- `app/config.php` — MySQL database configuration
- `app/db.php` — PDO database connection
- `app/helpers.php` — helper functions

## Database used

This task uses the common MySQL database:

`roommate_rental`

Required table:

- `listings`
- `users`

The uploaded image is not stored as a BLOB. Only the browser-accessible image path is stored in the `listings.image_url` column.

## Upload validation

The backend validates:

- file upload success using `UPLOAD_ERR_OK`
- accepted MIME types: `image/jpeg`, `image/png`
- maximum file size: 5 MB
- unique filename using `uniqid('room_', true)`
- secure upload movement using `move_uploaded_file()`

## Notes

This task is implemented for the PHP + MySQL version of the RoommateSync project. It uses PDO prepared statements for database insertion.
