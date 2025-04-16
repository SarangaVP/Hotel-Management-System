-- CREATE DATABASE hms_db;
USE hms_db;

CREATE TABLE guests (
    guest_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address TEXT,
    phone_number VARCHAR(15),
    email VARCHAR(100) UNIQUE,
    gov_id_number VARCHAR(50),
    password VARCHAR(255) NOT NULL 
);

CREATE TABLE rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(10) NOT NULL,
    room_type ENUM('Single', 'Double', 'Suite') NOT NULL,
    room_capacity INT NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    room_status ENUM('Available', 'Booked', 'Maintenance') DEFAULT 'Available',
    floor_number INT
);

-- Bookings table
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT,
    room_id INT,
    checkin_date DATE,
    checkin_time TIME,
    actual_checkin_date DATE,
    actual_checkin_time TIME,
    checkout_date DATE,
    checkout_time TIME,
    actual_checkout_date DATE,
    actual_checkout_time TIME,
    num_guests INT,
    guaranteed_booking ENUM('Yes', 'No') DEFAULT 'No',
    booking_status ENUM('Confirmed', 'Pending', 'Canceled', 'Completed') DEFAULT 'Pending',
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id)
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    guest_id INT,
    payment_received ENUM('Yes', 'No') DEFAULT 'No',
    payment_date DATE,
    payment_time TIME,
    payment_method VARCHAR(50),
    total_amount DECIMAL(10, 2),
    discount_applied ENUM('Yes', 'No') DEFAULT 'No',
    refund_processed ENUM('Yes', 'No') DEFAULT 'No',
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id)
);

CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('Receptionist', 'Manager', 'Administrator') NOT NULL,
    phone_number VARCHAR(15),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT,
    booking_id INT,
    feedback_date DATE,
    feedback_time TIME,
    feedback_rating INT CHECK (feedback_rating BETWEEN 1 AND 5),
    feedback_comments TEXT,
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

CREATE TABLE invoices (
    invoice_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    guest_id INT,
    invoice_date DATE,
    invoice_time TIME,
    total_amount_due DECIMAL(10, 2),
    amount_paid DECIMAL(10, 2),
    balance_due DECIMAL(10, 2),
    payment_status ENUM('Paid', 'Partially Paid', 'Unpaid') DEFAULT 'Unpaid',
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id)
);

INSERT INTO staff (first_name, last_name, role, email, password)
VALUES ('Admin', 'User', 'Administrator', 'admin@hms.com', 'admin123');