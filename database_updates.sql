-- Phase 1 Database Updates for Rholance PMS

-- 1. Updates to custom_orders for the new appointment/welder workflow
ALTER TABLE `custom_orders`
    ADD COLUMN `assigned_welder_id` INT(11) DEFAULT NULL,
    ADD COLUMN `welder_visit_date` DATE DEFAULT NULL,
    ADD COLUMN `welder_visit_time` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN `quoted_price` DECIMAL(10,2) DEFAULT NULL,
    ADD COLUMN `quoted_deadline` DATE DEFAULT NULL,
    ADD COLUMN `quoted_breakdown` TEXT DEFAULT NULL,
    ADD COLUMN `quote_status` ENUM('Pending Review', 'Approved', 'Rejected') DEFAULT 'Pending Review',
    ADD COLUMN `payment_status` ENUM('Unpaid', 'Pending Verification', 'Partially Paid', 'Paid') DEFAULT 'Unpaid',
    ADD COLUMN `payment_receipt` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `progress_percent` INT(11) DEFAULT 0,
    ADD COLUMN `progress_status` ENUM('Pending Approval', 'Approved') DEFAULT 'Approved',
    ADD COLUMN `customer_sketch` VARCHAR(255) DEFAULT NULL;

-- 2. Create item_variants table (for Inventory Variants)
CREATE TABLE IF NOT EXISTS `item_variants` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `item_id` INT(11) NOT NULL,
    `variant_name` VARCHAR(100) NOT NULL,
    `variant_value` VARCHAR(100) NOT NULL,
    `additional_price` DECIMAL(10,2) DEFAULT 0.00,
    `sku` VARCHAR(100) DEFAULT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Note: The `items` table already has an `image` column, so we just use that for item images instead of icons.

-- 3. Create cities table for Cavite and Laguna Address Restriction
CREATE TABLE IF NOT EXISTS `allowed_cities` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `province` VARCHAR(50) NOT NULL,
    `city_name` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `allowed_cities` (`province`, `city_name`) VALUES 
('Cavite', 'Alfonso'), ('Cavite', 'Amadeo'), ('Cavite', 'Bacoor'), ('Cavite', 'Carmona'), 
('Cavite', 'Cavite City'), ('Cavite', 'Dasmariñas'), ('Cavite', 'General Emilio Aguinaldo'), 
('Cavite', 'General Mariano Alvarez'), ('Cavite', 'General Trias'), ('Cavite', 'Imus'), 
('Cavite', 'Indang'), ('Cavite', 'Kawit'), ('Cavite', 'Magallanes'), ('Cavite', 'Maragondon'), 
('Cavite', 'Mendez'), ('Cavite', 'Naic'), ('Cavite', 'Noveleta'), ('Cavite', 'Rosario'), 
('Cavite', 'Silang'), ('Cavite', 'Tagaytay'), ('Cavite', 'Tanza'), ('Cavite', 'Ternate'), ('Cavite', 'Trece Martires'),
('Laguna', 'Alaminos'), ('Laguna', 'Bay'), ('Laguna', 'Biñan'), ('Laguna', 'Cabuyao'), 
('Laguna', 'Calamba'), ('Laguna', 'Calauan'), ('Laguna', 'Cavinti'), ('Laguna', 'Famy'), 
('Laguna', 'Kalayaan'), ('Laguna', 'Liliw'), ('Laguna', 'Los Baños'), ('Laguna', 'Luisiana'), 
('Laguna', 'Lumban'), ('Laguna', 'Mabitac'), ('Laguna', 'Magdalena'), ('Laguna', 'Majayjay'), 
('Laguna', 'Nagcarlan'), ('Laguna', 'Paete'), ('Laguna', 'Pagsanjan'), ('Laguna', 'Pakil'), 
('Laguna', 'Pangil'), ('Laguna', 'Pila'), ('Laguna', 'Rizal'), ('Laguna', 'San Pablo'), 
('Laguna', 'San Pedro'), ('Laguna', 'Santa Cruz'), ('Laguna', 'Santa Maria'), ('Laguna', 'Santa Rosa'), 
('Laguna', 'Siniloan'), ('Laguna', 'Victoria');

-- 4. Create custom_product_variants if it doesn't exist
CREATE TABLE IF NOT EXISTS `custom_product_variants` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_name` VARCHAR(150) NOT NULL,
    `variant_name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
