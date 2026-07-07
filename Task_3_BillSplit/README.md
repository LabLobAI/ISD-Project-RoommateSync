# Task 3: Proportional Bill Split Calculator

## Frontend Link
After moving the full project folder into `C:\xampp\htdocs\`, open:

```text
http://localhost/RoommateSync_Taskwise_OneDatabase_Final/Task_3_BillSplit/public/expenses.php
```

## Purpose
This task calculates a fair bill split between roommates based on their individual incomes instead of splitting equally.

## Important Paths

```text
Task_3_BillSplit/
├── app/
│   ├── config.php              Database connection settings
│   ├── config.example.php      Sample database configuration
│   ├── db.php                  PDO MySQL connection file
│   └── helpers.php             Shared helper functions
│
└── public/
    ├── expenses.php            Main frontend page + PHP bill calculation backend
    └── assets/
        ├── css/
        │   └── style.css       Task design/style file
        └── js/
            └── expenses.js     Dynamic roommate rows and calculation logic
```

## Database Used
This task uses the common database:

```text
roommate_rental
```

Tables used:

```text
bill_logs
bill_log_roommates
users
```

## Formula Used

```text
Individual Share = (Individual Income / Combined Income) × Total Bill
```

## How It Works

1. The user opens `public/expenses.php`.
2. The user enters total bill and roommate incomes.
3. `expenses.js` calculates the split dynamically.
4. When submitted, PHP verifies the calculation on the backend.
5. The bill log can be saved into the database.

## Database Import Note
Do not import a separate database for this task. Import the common files only once:

```text
Database/schema.sql
Database/seed.sql
```
