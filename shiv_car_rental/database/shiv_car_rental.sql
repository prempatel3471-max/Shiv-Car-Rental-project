CREATE DATABASE IF NOT EXISTS shiv_car_rental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shiv_car_rental;

CREATE TABLE IF NOT EXISTS cars (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  category VARCHAR(50) NOT NULL,
  price_per_hour DECIMAL(10,2) NOT NULL,
  fuel VARCHAR(30) DEFAULT 'Petrol',
  transmission VARCHAR(30) DEFAULT 'Automatic',
  seats TINYINT UNSIGNED DEFAULT 5,
  image VARCHAR(500) DEFAULT NULL,
  status ENUM('available','maintenance','inactive') NOT NULL DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_code VARCHAR(40) NOT NULL UNIQUE,
  car_id INT UNSIGNED NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  pickup_location VARCHAR(150) NOT NULL,
  pickup_date DATE NOT NULL,
  return_date DATE NOT NULL,
  pickup_datetime DATETIME NOT NULL,
  return_datetime DATETIME NOT NULL,
  driver_option ENUM('self','driver') NOT NULL,
  hours INT UNSIGNED NOT NULL,
  base_amount DECIMAL(10,2) NOT NULL,
  driver_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(10,2) NOT NULL,
  booking_status ENUM('pending_payment','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending_payment',
  payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_car FOREIGN KEY (car_id) REFERENCES cars(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  payment_method ENUM('cash') NOT NULL,
  transaction_id VARCHAR(80) NOT NULL UNIQUE,
  amount DECIMAL(10,2) NOT NULL,
  payment_status ENUM('paid','failed','refunded') NOT NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payment_booking FOREIGN KEY (booking_id) REFERENCES bookings(id)
) ENGINE=InnoDB;

INSERT INTO cars (name,category,price_per_hour,fuel,transmission,seats) VALUES
('Hyundai i20','hatchback',70,'Petrol','Automatic',5),
('Mahindra Thar','suv',120,'Diesel','Manual',4),
('Honda City','sedan',95,'Petrol','Automatic',5),
('Tata Nexon','suv',85,'Petrol','Automatic',5),
('Kia Seltos','suv',110,'Diesel','Automatic',5),
('Toyota Fortuner','luxury',180,'Diesel','Automatic',7),
('Maruti Swift','hatchback',65,'Petrol','Manual',5),
('Hyundai Creta','suv',105,'Diesel','Automatic',5),
('MG Hector','luxury',125,'Petrol','Automatic',5)
ON DUPLICATE KEY UPDATE price_per_hour=VALUES(price_per_hour), category=VALUES(category), fuel=VALUES(fuel), transmission=VALUES(transmission), seats=VALUES(seats);

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admins (name,email,password_hash,status) VALUES
('Shiv Car Rental Admin','admin@shivcarrental.com','$2y$12$ttuN7fQWnQqjxcV09hg1hOtkomqw5VKTvpVW1nPmU/FquJg312pb6','active')
ON DUPLICATE KEY UPDATE name=VALUES(name), status='active';

-- For an existing database, run: ALTER TABLE bookings ADD COLUMN pickup_datetime DATETIME NULL, ADD COLUMN return_datetime DATETIME NULL; then populate these columns before enabling the new availability logic.
