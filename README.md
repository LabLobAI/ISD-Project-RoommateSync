# RoommateSync Unified Project

This repository is organized as one RoommateSync workspace with a single root dashboard, shared runtime code, and feature modules under `modules/`.

## Layout

- `index.php` - root dashboard and launch point
- `Database/` - shared MySQL schema and seed data
- `core/` - shared bootstrap, database, and helper functions
- `assets/` - shared root styling for the dashboard
- `auth/` - login, register, and logout pages with cookie session support
- `modules/marketplace/` - rental listing search and filtering
- `modules/bill-split/` - proportional bill split calculator
- `modules/booking/` - property viewing booking flow
- `modules/listing-upload/` - landlord listing creation and image upload
- `modules/social/` - review, chat, and connect flows

## Module Map

| Module | Entry Point | Purpose | Main Tables |
| --- | --- | --- | --- |
| Marketplace | `modules/marketplace/public/listings.php` | Browse and filter listings without reloading | `listings`, `users` |
| Bill Split | `modules/bill-split/public/expenses.php` | Split bills by income and optionally save logs | `bill_logs`, `bill_log_roommates`, `users` |
| Booking | `modules/booking/public/booking.php` | Reserve property viewing slots with conflict checks | `appointments`, `listings`, `users` |
| Listing Upload | `modules/listing-upload/public/create_listing.php` | Create a landlord listing and upload an image | `listings`, `users` |
| Social: Review | `modules/social/frontend/review_form.php` | Submit and aggregate reviews | `user_reviews`, `connection_requests` |
| Social: Chat | `modules/social/frontend/chat.php` | Polling-based chat for connected users | `messages`, `connection_requests` |
| Social: Connect | `modules/social/frontend/connect.php` | Double opt-in connection requests | `connection_requests` |

## Shared Runtime

- `core/bootstrap.php` sets shared constants and session helpers.
- `core/database.php` exposes the shared PDO connection.
- `core/helpers.php` provides JSON, formatting, validation, and similarity helpers.
- `core/auth.php` handles login, registration, logout, and remember-me cookies.
- The project expects MySQL on port `3307` for XAMPP/phpMyAdmin.

## Authentication

- Sign in at `auth/login.php`.
- Create an account at `auth/register.php`.
- Sign out at `auth/logout.php`.
- Sessions are backed by PHP session cookies, and the remember-me option issues a persistent encrypted token stored in the `users` table.

## Ready

- Marketplace listing search and filtering
- Income-based bill splitting with database saving
- Booking flow with slot conflict checks
- Listing creation with file upload validation
- Social review, chat, and connect flows working under one module tree
- Root landing page and auth flow

## Still To Tighten

- Shared authentication and role-based access control across all module actions
- Unified page layout/header/footer across all entry points
- Centralized error handling, logging, and test coverage
- Cleaner URL aliases if you want shorter public paths

## Run

1. Import `Database/schema.sql`.
2. Import `Database/seed.sql`.
3. Open `index.php` from the project root in a PHP-enabled web server or XAMPP.
4. Use the dashboard to open each module entry point.

## Demo Accounts

- The seed data uses a shared demo password for the sample users.
- After running the updated seed, you can sign in with any seeded email address.
- Demo password: `Roommate123!`

## Social Seed

- Users 1 and 2 are seeded as an accepted connection.
- A sample review and a sample chat thread are included so the social pages have live data on first load.

## Notes

- The project now uses one canonical README at the root.
- Module folders are organized under `modules/` instead of task-style names.
- The shared database is `roommate_rental`.