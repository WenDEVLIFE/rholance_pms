-- ============================================================
-- Rholance PMS — Safe Schema Update Script
-- Run this on phpMyAdmin (u467106394_rholance database)
-- Uses IF NOT EXISTS so safe to re-run even if columns exist
-- MariaDB 10.5+ / 11.x supported
-- ============================================================

ALTER TABLE `custom_orders`
    ADD COLUMN IF NOT EXISTS `quote_status`       VARCHAR(50)    DEFAULT 'Pending',
    ADD COLUMN IF NOT EXISTS `payment_status`     VARCHAR(50)    DEFAULT 'Unpaid',
    ADD COLUMN IF NOT EXISTS `quoted_price`       DECIMAL(10,2)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `quoted_deadline`    DATE           DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `quoted_breakdown`   TEXT           DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `assigned_welder_id` INT            DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `welder_visit_date`  DATE           DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `welder_visit_time`  VARCHAR(50)    DEFAULT NULL;

-- Verify result (optional, run separately to check):
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_SCHEMA = 'u467106394_rholance' AND TABLE_NAME = 'custom_orders'
-- ORDER BY ORDINAL_POSITION;
