# Verified Peer Review & Household Feedback System (SCRUM-16)

This prototype implements the verification gatekeeper and basic review storage described in SCRUM-16.

Files added:
- `migrations/001_create_reviews.sql` — SQL to create `users`, `connection_requests`, and `user_reviews`.
- `api/config.php` — PDO config helper (edit with real credentials).
- `api/submit_review.php` — Verification controller and insert logic.
- `api/get_user_reviews.php` — Aggregate score endpoint.
- `frontend/review_form.html` — Minimal UI prototype to submit reviews.

Quick start:

1. Import the migration into your MySQL database (adjust for your RDBMS):

```sql
-- from project root
source migrations/001_create_reviews.sql;
```

2. Edit `api/config.php` with your DB credentials.
3. Deploy `api/` and `frontend/` under a PHP-enabled web server (or use PHP built-in server):

```bash
php -S 127.0.0.1:8000 -t frontend
# ensure api is reachable at ../api relative path in the frontend; you may need to serve from project root
```

4. Open `frontend/review_form.html` in a browser and test.

Next recommended steps:
- Add server-side CSRF/authentication checks (token-based)
- Add unit/integration tests for `submit_review.php` including negative cases
- Harden input sanitization and error logging
- Add pagination and single-review read endpoints

Chat Interface (SCRUM-15) files added:
- `migrations/002_create_messages.sql` — create `messages` table.
- `api/send_message.php` — guarded message send endpoint.
- `api/fetch_messages.php` — guarded message fetch endpoint.
- `frontend/chat.html` — simple polling-based chat UI prototype.

Quick chat test commands:

```bash
# import messages migration
mysql -u <user> -p <dbname> < migrations/002_create_messages.sql

# serve project root (so frontend can reach ../api)
php -S 127.0.0.1:8000 -t .
# open http://127.0.0.1:8000/frontend/chat.html
```

Double Opt-In Gateway (SCRUM-14) files added:
- `migrations/003_double_optin.sql` — enforce unique (sender,receiver) and status domain.
- `api/connect_request.php` — double opt-in gateway API implementing insert-pending and accept-on-reverse-request logic.
- `frontend/connect.html` — lightweight prototype for clicking "Connect" and seeing pending/accepted flows.

Quick connect test commands:

```bash
# import double opt-in migration
mysql -u <user> -p <dbname> < migrations/003_double_optin.sql

# serve project root so frontend can reach ../api
php -S 127.0.0.1:8000 -t .
# open http://127.0.0.1:8000/frontend/connect.html
```


