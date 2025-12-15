-- Create returns table for order returns/refunds
CREATE TABLE IF NOT EXISTS `returns` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `status` ENUM('pending', 'approved', 'rejected', 'refunded') DEFAULT 'pending',
  `refund_amount` DECIMAL(10,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
