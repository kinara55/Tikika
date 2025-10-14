<?php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site timezone
$conf['site_timezone'] = 'Africa/Nairobi';

// Event Management Platform Information
$conf['site_name'] = 'Tikika';
$conf['site_tagline'] = 'A modern event management and ticketing platform';
$conf['site_url'] = 'https://localhost/Tikika/';
$conf['site_email'] = 'info@tikika.com';
$conf['site_phone'] = '+254 700 000 000';
$conf['site_address'] = 'Nairobi, Kenya';

$conf['site_lang'] = 'en';

// Stripe Configuration
$conf['stripe_secret_key'] = 'YOUR_STRIPE_SECRET_KEY_HERE';

// Event categories (matching tikika_schema.sql)
$conf['event_categories'] = [
    'Music' => 'Concerts and music shows',
    'Conference' => 'Business and professional conferences',
    'Meetup' => 'Community meetups and networking',
    'Sports' => 'Sports events and tournaments',
    'Workshop' => 'Educational workshops and training',
    'Exhibition' => 'Art and product exhibitions',
    'Festival' => 'Cultural and entertainment festivals',
    'Other' => 'Other types of events'
];

// Database constants (matching tikika_schema.sql)
$conf['DB_TYPE'] = 'mysqli';
$conf['DB_HOST'] = 'localhost';
$conf['DB_USER'] = 'root';
$conf['DB_PASS'] = 'YOUR_DATABASE_PASSWORD_HERE';
$conf['DB_NAME'] = 'tikika_db';

// Email configuration
$conf['mail_type'] = 'smtp'; // mail or smtp
$conf['smtp_host'] = 'YOUR_SMTP_HOST_HERE';
$conf['smtp_user'] = 'YOUR_SMTP_USERNAME_HERE';
$conf['smtp_pass'] = 'YOUR_SMTP_PASSWORD_HERE';
$conf['smtp_port'] = 587;
$conf['smtp_secure'] = 'tls'; // Encryption

// Valid email domains for registration
$conf['valid_domain'] = ['gmail.com', 'yahoo.com', 'outlook.com', 'strathmore.edu', 'hotmail.com'];

// Validation settings
$conf['valid_password_length'] = 8;
$conf['valid_phone_length'] = 10; // Minimum phone number length
$conf['valid_name_length'] = 2; // Minimum name length

// Event settings
$conf['max_event_capacity'] = 10000;
$conf['max_ticket_price'] = 50000.00; // Maximum ticket price in KSh
$conf['min_ticket_price'] = 0.00; // Minimum ticket price in KSh

// Security settings
$conf['session_timeout'] = 3600; // basically 1 hr
$conf['max_login_attempts'] = 5; //Maybe we can add locking mechanism for multiple attempts
$conf['lockout_duration'] = 900; // 15 minutes in seconds
