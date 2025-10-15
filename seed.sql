INSERT IGNORE INTO `roles` (`name`, `description`) VALUES
('admin', 'Full administrative access'),
('organizer', 'Can create and manage events'),
('attendee', 'Buy tickets and attend events');


#Sample organizer user
INSERT IGNORE INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`)
VALUES (1, 2, 'Sample Organizer', 'organizer@example.com', 'hashed_password_here', '0712345678');


#Sample category
INSERT IGNORE INTO `categories` (`id`, `name`, `description`)
VALUES (1, 'Music', 'Concerts and music shows');


#Sample events (matching buy_ticket.php hardcoded data)
INSERT IGNORE INTO `events` (`id`, `organizer_id`, `title`, `description`, `venue`, `start_datetime`, `end_datetime`, `status`, `capacity`, `category_id`)
VALUES 
(1, 1, 'AfroBeats Night', 'Get ready for a night of amazing AfroBeats music with top artists from across Africa', 'Nairobi Arena', '2025-10-15 19:00:00', '2025-10-15 23:00:00', 'published', 1000, 1),
(2, 1, 'Jazz Festival', 'Experience smooth jazz under the stars with renowned performers from around the world', 'Uhuru Gardens', '2025-11-02 18:00:00', '2025-11-02 22:00:00', 'published', 800, 1),
(3, 1, 'Bambika na 3 men Army', 'Ready to crack your ribssssssss!', 'Beer District', '2025-12-10 20:00:00', '2025-12-10 23:30:00', 'published', 500, 1);


#Sample ticket types for each event
INSERT IGNORE INTO `tickets` (`event_id`, `type`, `price`, `quantity`, `sold`)
VALUES 
(1, 'Regular', 1500.00, 500, 0),
(1, 'VIP', 3000.00, 200, 0),
(1, 'VVIP', 4500.00, 100, 0),
(2, 'Regular', 2000.00, 400, 0),
(2, 'VIP', 4000.00, 150, 0),
(2, 'VVIP', 6000.00, 50, 0),
(3, 'Regular', 1000.00, 300, 0),
(3, 'VIP', 2000.00, 100, 0),
(3, 'VVIP', 3000.00, 50, 0);
```


