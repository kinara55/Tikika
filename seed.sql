INSERT INTO `roles` (`name`, `description`) VALUES
('admin', 'Full administrative access'),
('organizer', 'Can create and manage events'),
('attendee', 'Buy tickets and attend events');


#Sample organizer user
INSERT INTO `users` (`role_id`, `full_name`, `email`, `password_hash`, `phone`)
VALUES (2, 'Sample Organizer', 'organizer@example.com', 'hashed_password_here', '0712345678');


#Sample venue
INSERT INTO `venues` (`name`, `address`, `city`, `capacity`)
VALUES ('Nairobi Concert Hall', '123 Main Street', 'Nairobi', 5000);


#Sample category
INSERT INTO `categories` (`name`, `description`)
VALUES ('Music', 'Concerts and music shows');


#Sample event
INSERT INTO `events` (`organizer_id`, `venue_id`, `title`, `description`, `start_datetime`, `end_datetime`, `status`, `capacity`)
VALUES (1, 1, 'Sample Concert', 'An amazing live concert', '2025-12-01 18:00:00', '2025-12-01 23:00:00', 'published', 2000);


#Link event to category
INSERT INTO `event_categories` (`event_id`, `category_id`) VALUES (1, 1);


#Sample ticket type
INSERT INTO `tickets` (`event_id`, `type`, `price`, `quantity`, `sold`)
VALUES (1, 'General Admission', 1000.00, 500, 0);
```


