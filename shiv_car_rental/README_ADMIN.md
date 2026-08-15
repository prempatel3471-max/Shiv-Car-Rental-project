# Shiv Car Rental Admin Panel

## Login
Open `/admin/` after starting XAMPP.

Demo credentials:
- Email: `admin@shivcarrental.com`
- Password: `Admin@123`

## Features
- Dashboard statistics and recent bookings
- Add/edit/deactivate cars
- Change car availability/status
- View and update booking status
- View payment history
- Session-protected admin API

## Setup
1. Import `database/shiv_car_rental.sql` into MySQL/phpMyAdmin.
2. Make sure `backend/config/database.php` has the correct MySQL credentials.
3. Put the project under `htdocs` and start Apache + MySQL.
4. Visit `http://localhost/shiv_car_rental/admin/`.

For production, change the demo admin password and use HTTPS. Real payment verification should be handled by a payment gateway webhook before marking bookings as paid.
