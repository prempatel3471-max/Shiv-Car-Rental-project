# Shiv Car Rental — PHP + MySQL Backend

This backend is built around the existing booking flow in the supplied Shiv Car Rental frontend.

## Stack
- HTML/CSS/JavaScript frontend
- PHP 8+
- MySQL 8+
- PDO prepared statements

## Setup with XAMPP
1. Copy this whole folder to `C:\xampp\htdocs\shiv-car-rental\`.
2. Start Apache and MySQL.
3. Open phpMyAdmin and import `database/shiv_car_rental.sql`.
4. Confirm MySQL credentials in `backend/config/database.php`.
5. Open `http://localhost/shiv_car_rental/`.
6. Test backend at `http://localhost/shiv_car_rental/backend/`.

## Booking/payment API
- `POST backend/api/bookings.php` creates a `pending_payment` booking.
- `POST backend/api/payment.php` records a payment and changes the booking to `confirmed`.
- `GET backend/api/booking.php?id=BOOKING_ID` returns booking details.
- `GET backend/api/cars.php` returns available cars.

## Important
The included payment endpoint is a **demo payment recorder**. It does not charge a real card/UPI account. For production, replace the demo payment call with a server-side Razorpay/Stripe integration and verify gateway signatures/webhooks before marking a booking paid.

## Admin Panel
Open `/admin/` to manage the rental system. Demo credentials are in `README_ADMIN.md`.

## Payment mode

The customer checkout is configured for **cash at pickup only**. Online payment methods (UPI, card, and net banking) are disabled. A booking is confirmed with payment status `pending`, and the cash amount is collected when the customer picks up the car.


## Customer-site changes
- User login removed from the public site.
- Subscription section and subscription links removed.
- Customers choose rental hours (1 to 168) and pickup date/time.
- Backend checks overlapping pending/confirmed bookings before reserving a car.
- A booked time slot is unavailable to another customer.
- Payment remains Cash at Pickup; admin controls payment status.
