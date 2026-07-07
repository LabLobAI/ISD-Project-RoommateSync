# Task 4: Property Viewing Booking

## Frontend Link
After moving the full project folder into `C:\xampp\htdocs\`, open:

```text
http://localhost/RoommateSync_Taskwise_OneDatabase_Final/Task_4_Booking/public/booking.php
```

## Purpose
This task lets a tenant book a property viewing slot. It checks existing appointments first, prevents double booking, saves the appointment, and shows a success message to the user.

## Important Paths

```text
Task_4_Booking/
├── app/
│   ├── config.php              Database connection settings
│   ├── config.example.php      Sample database configuration
│   ├── db.php                  PDO MySQL connection file
│   └── helpers.php             Shared helper functions
│
└── public/
    ├── booking.php             Main frontend page + PHP booking backend
    └── assets/
        ├── css/
        │   └── style.css       Task design/style file
        └── js/
            └── booking.js      Slot loading, booking request, and success message logic
```

## Database Used
This task uses the common database:

```text
roommate_rental
```

Tables used:

```text
appointments
listings
users
```

## How It Works

1. The user opens `public/booking.php`.
2. The user chooses a listing, date, and time slot.
3. `booking.js` asks the backend which slots are already booked.
4. Booked slots are disabled on the page.
5. If the selected slot is clear, PHP inserts the booking into `appointments`.
6. The website shows: `Your viewing booking has been placed successfully.`

## Database Import Note
Do not import a separate database for this task. Import the common files only once:

```text
Database/schema.sql
Database/seed.sql
```
