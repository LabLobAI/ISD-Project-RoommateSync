# Task 2: Rental Marketplace Search

## Frontend Link
After moving the full project folder into `C:\xampp\htdocs\`, open:

```text
http://localhost/RoommateSync_Taskwise_OneDatabase_Final/Task_2_Marketplace/public/listings.php
```

## Purpose
This task creates a rental marketplace interface where users can filter listings by maximum price, room type, and location without reloading the page.

## Important Paths

```text
Task_2_Marketplace/
├── app/
│   ├── config.php              Database connection settings
│   ├── config.example.php      Sample database configuration
│   ├── db.php                  PDO MySQL connection file
│   └── helpers.php             Shared helper functions
│
└── public/
    ├── listings.php            Main frontend page + PHP listing backend
    └── assets/
        ├── css/
        │   └── style.css       Task design/style file
        └── js/
            └── listings.js     Filter request and listing render logic
```

## Database Used
This task uses the common database:

```text
roommate_rental
```

Tables used:

```text
listings
users
```

## How It Works

1. The user opens `public/listings.php`.
2. `listings.js` listens for filter changes.
3. JavaScript sends filter values to `listings.php?api=listings`.
4. PHP builds a safe prepared MySQL query.
5. Filtered listing cards are returned and displayed.

## Database Import Note
Do not import a separate database for this task. Import the common files only once:

```text
Database/schema.sql
Database/seed.sql
```
