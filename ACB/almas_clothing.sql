CREATE DATABASE IF NOT EXISTS `almas_clothing` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `almas_clothing`;



CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` VALUES 
("1","admin@almas.com","$2y$10$HTsFrwtqFkpE1nH3g0Gqt.NuZXeOQFv7RSLLKXUMEhxHs8AVAF9kW");


CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` VALUES 
("4","Accessories"),
("3","Kids"),
("1","Men"),
("2","Women");


CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `chat_messages` VALUES 
("1","1","user","Hi, I have a question about my order.","0","2026-02-07 01:10:56"),
("2","1","admin","Hello! Sure, what is your order number?","0","2026-02-07 01:10:56"),
("3","1","user","It is #1.","0","2026-02-07 01:10:56");


CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_percent` int(11) NOT NULL,
  `valid_until` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `coupons` VALUES 
("1","WELCOME10","10","2025-12-31","1"),
("2","SUMMER20","20","2025-08-31","1"),
("3","EXPIRED50","50","2023-01-01","0");


CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `idx_order_items_product_id` (`product_id`),
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` VALUES 
("1","1","6","High-Waist Jeans","55.00","3","165.00"),
("2","1","1","Classic Denim Jacket","59.99","3","179.97"),
("3","1","1","Classic Denim Jacket","59.99","2","119.98"),
("4","2","6","High-Waist Jeans","55.00","1","55.00"),
("5","3","4","Floral Summer Dress","49.99","1","49.99"),
("6","3","4","Floral Summer Dress","49.99","3","149.97"),
("7","4","7","Kids Graphic Hoodie","29.99","2","59.98"),
("8","5","12","Canvas Tote Bag","12.99","2","25.98"),
("9","5","7","Kids Graphic Hoodie","29.99","3","89.97"),
("10","6","8","Comfort Sneakers","35.00","1","35.00"),
("11","7","5","Elegant Silk Blouse","65.00","3","195.00"),
("12","7","5","Elegant Silk Blouse","65.00","2","130.00"),
("13","7","11","Aviator Sunglasses","15.00","3","45.00"),
("14","7","5","Elegant Silk Blouse","65.00","1","65.00"),
("15","8","4","Floral Summer Dress","49.99","2","99.98"),
("16","8","6","High-Waist Jeans","55.00","2","110.00"),
("17","8","3","Formal Oxford Shirt","45.00","3","135.00"),
("18","9","5","Elegant Silk Blouse","65.00","3","195.00"),
("19","9","3","Formal Oxford Shirt","45.00","1","45.00"),
("20","9","7","Kids Graphic Hoodie","29.99","1","29.99"),
("21","9","8","Comfort Sneakers","35.00","2","70.00"),
("22","10","6","High-Waist Jeans","55.00","1","55.00"),
("23","10","8","Comfort Sneakers","35.00","3","105.00"),
("24","10","4","Floral Summer Dress","49.99","3","149.97"),
("25","10","10","Leather Belt","24.99","1","24.99");


CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(120) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(40) NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_orders_user_id` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` VALUES 
("1","2","Jane Smith","jane@example.com","555-0102","456 Oak Ave, Los Angeles, CA","New York","464.95","0.00","464.95","Cancelled","2026-01-30 21:10:56"),
("2","1","John Doe","john@example.com","555-0101","123 Main St, New York, NY","New York","55.00","0.00","55.00","Pending","2026-01-08 21:10:56"),
("3","2","Jane Smith","jane@example.com","555-0102","456 Oak Ave, Los Angeles, CA","New York","199.96","0.00","199.96","Cancelled","2026-01-09 21:10:56"),
("4","2","Jane Smith","jane@example.com","555-0102","456 Oak Ave, Los Angeles, CA","New York","59.98","0.00","59.98","Processing","2026-01-08 21:10:56"),
("5","1","John Doe","john@example.com","555-0101","123 Main St, New York, NY","New York","115.95","0.00","115.95","Processing","2026-01-12 21:10:56"),
("6","4","Sarah Williams","sarah@example.com","555-0104","321 Elm St, Houston, TX","New York","35.00","0.00","35.00","Shipped","2026-01-16 21:10:56"),
("7","2","Jane Smith","jane@example.com","555-0102","456 Oak Ave, Los Angeles, CA","New York","435.00","0.00","435.00","Cancelled","2026-01-28 21:10:56"),
("8","1","John Doe","john@example.com","555-0101","123 Main St, New York, NY","New York","344.98","0.00","344.98","Delivered","2026-01-28 21:10:56"),
("9","4","Sarah Williams","sarah@example.com","555-0104","321 Elm St, Houston, TX","New York","339.99","0.00","339.99","Shipped","2026-01-26 21:10:56"),
("10","4","Sarah Williams","sarah@example.com","555-0104","321 Elm St, Houston, TX","New York","334.96","0.00","334.96","Delivered","2026-02-04 21:10:56");


CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(300) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_products_category_id` (`category_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products` VALUES 
("1","1","Classic Denim Jacket","A timeless denim jacket with a comfortable fit.","59.99","50","assets/images/mens-jeans.svg","1","1","2026-02-07 01:10:56"),
("2","1","Casual Cotton T-Shirt","Soft and breathable cotton t-shirt for everyday wear.","19.99","100","assets/images/mens-tshirt.svg","1","0","2026-02-07 01:10:56"),
("3","1","Formal Oxford Shirt","Crisp oxford shirt perfect for office and formal events.","45.00","30","assets/images/cat-men.svg","1","1","2026-02-07 01:10:56"),
("4","2","Floral Summer Dress","Lightweight and airy dress with a beautiful floral print.","49.99","40","assets/images/women-dress.svg","1","1","2026-02-07 01:10:56"),
("5","2","Elegant Silk Blouse","Luxurious silk blouse that adds a touch of sophistication.","65.00","25","assets/images/women-blouse.svg","1","0","2026-02-07 01:10:56"),
("6","2","High-Waist Jeans","Trendy high-waist jeans with a flattering fit.","55.00","60","assets/images/cat-women.svg","1","1","2026-02-07 01:10:56"),
("7","3","Kids Graphic Hoodie","Fun and colorful hoodie with cool graphics.","29.99","45","assets/images/kids-hoodie.svg","1","1","2026-02-07 01:10:56"),
("8","3","Comfort Sneakers","Durable sneakers designed for active kids.","35.00","50","assets/images/kids-sneakers.svg","1","0","2026-02-07 01:10:56"),
("9","3","Denim Overalls","Cute and practical overalls for playtime.","32.00","35","assets/images/cat-kids.svg","1","0","2026-02-07 01:10:56"),
("10","4","Leather Belt","Genuine leather belt with a classic buckle.","24.99","80","assets/images/leather-belt.svg","1","0","2026-02-07 01:10:56"),
("11","4","Aviator Sunglasses","Stylish sunglasses with UV protection.","15.00","100","assets/images/sunglasses.svg","1","1","2026-02-07 01:10:56"),
("12","4","Canvas Tote Bag","Spacious tote bag for all your essentials.","12.99","150","assets/images/cat-accessories.svg","1","0","2026-02-07 01:10:56");


CREATE TABLE `review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_review_user_id` (`user_id`),
  KEY `idx_review_product_id` (`product_id`),
  CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `review` VALUES 
("1","2","12","5","Loved it.","0","2026-02-07 01:10:56"),
("2","1","7","5","Great product!","1","2026-02-07 01:10:56"),
("3","2","5","3","Okay, but could be better.","1","2026-02-07 01:10:56"),
("4","2","11","3","Excellent!","0","2026-02-07 01:10:56"),
("5","5","2","3","Good quality.","1","2026-02-07 01:10:56"),
("6","4","6","4","Fast shipping.","1","2026-02-07 01:10:56"),
("7","5","8","4","Excellent!","0","2026-02-07 01:10:56"),
("8","4","9","3","Loved it.","0","2026-02-07 01:10:56"),
("9","2","12","3","Good quality.","1","2026-02-07 01:10:56"),
("10","1","10","4","Okay, but could be better.","0","2026-02-07 01:10:56"),
("11","2","10","5","Fast shipping.","0","2026-02-07 01:10:56"),
("12","1","11","5","Okay, but could be better.","0","2026-02-07 01:10:56"),
("13","1","11","3","Good quality.","1","2026-02-07 01:10:56"),
("14","5","5","3","Okay, but could be better.","0","2026-02-07 01:10:56"),
("15","2","12","4","Excellent!","1","2026-02-07 01:10:56");


CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES 
("1","John Doe","john@example.com","$2y$10$HTsFrwtqFkpE1nH3g0Gqt.NuZXeOQFv7RSLLKXUMEhxHs8AVAF9kW","123 Main St, New York, NY","555-0101","","Active","2026-02-07 01:10:56"),
("2","Jane Smith","jane@example.com","$2y$10$HTsFrwtqFkpE1nH3g0Gqt.NuZXeOQFv7RSLLKXUMEhxHs8AVAF9kW","456 Oak Ave, Los Angeles, CA","555-0102","","Active","2026-02-07 01:10:56"),
("3","Mike Johnson","mike@example.com","$2y$10$HTsFrwtqFkpE1nH3g0Gqt.NuZXeOQFv7RSLLKXUMEhxHs8AVAF9kW","789 Pine Rd, Chicago, IL","555-0103","","Active","2026-02-07 01:10:56"),
("4","Sarah Williams","sarah@example.com","$2y$10$HTsFrwtqFkpE1nH3g0Gqt.NuZXeOQFv7RSLLKXUMEhxHs8AVAF9kW","321 Elm St, Houston, TX","555-0104","","Active","2026-02-07 01:10:56"),
("5","David Brown","david@example.com","$2y$10$HTsFrwtqFkpE1nH3g0Gqt.NuZXeOQFv7RSLLKXUMEhxHs8AVAF9kW","654 Maple Dr, Phoenix, AZ","555-0105","","Active","2026-02-07 01:10:56");


CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_wishlist_user` (`user_id`),
  KEY `idx_wishlist_product` (`product_id`),
  CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

