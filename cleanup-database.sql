-- ============================================================
-- NORSU Clinic Database Cleanup SQL Script
-- ============================================================
-- IMPORTANT: BACKUP YOUR DATABASE BEFORE RUNNING THIS SCRIPT!
-- ============================================================

-- This script drops unused tables from the NORSU Clinic database
-- Review each section carefully before executing

-- ============================================================
-- SECTION 1: CONFIRMED UNUSED TABLES
-- ============================================================

-- Reviews System (Feature is commented out in menu)
DROP TABLE IF EXISTS `reviews`;

-- WebSockets Statistics (If not using WebSocket dashboard)
DROP TABLE IF EXISTS `websockets_statistics_entries`;

-- ============================================================
-- SECTION 2: REQUIRED TABLES - DO NOT REMOVE
-- ============================================================

-- These tables are actively used by the clinic system
-- DO NOT uncomment or execute these DROP commands

-- Campus/College/Course System - REQUIRED for patient academic tracking
-- DO NOT DROP: year_levels, courses, colleges, campuses

-- Guest System - REQUIRED for visitor management
-- DO NOT DROP: guests

-- Office System - REQUIRED for office assignments
-- DO NOT DROP: offices

-- Department System - REQUIRED for department management
-- DO NOT DROP: departments

-- Vaccination System - REQUIRED for patient vaccination records
-- DO NOT DROP: vaccinations

-- Payment Gateway System - REQUIRED for online payment processing
-- DO NOT DROP: payment_gateways

-- ============================================================
-- VERIFICATION QUERIES
-- ============================================================
-- Run these to check if tables have any data before dropping

-- Check Reviews (Safe to check before removal)
-- SELECT COUNT(*) as review_count FROM reviews;

-- Check Required Tables (Informational only - DO NOT REMOVE THESE)
-- SELECT COUNT(*) as campus_count FROM campuses;
-- SELECT COUNT(*) as college_count FROM colleges;
-- SELECT COUNT(*) as course_count FROM courses;
-- SELECT COUNT(*) as year_level_count FROM year_levels;
-- SELECT COUNT(*) as guest_count FROM guests;
-- SELECT COUNT(*) as office_count FROM offices;
-- SELECT COUNT(*) as department_count FROM departments;
-- SELECT COUNT(*) as vaccination_count FROM vaccinations;
-- SELECT COUNT(*) as payment_gateway_count FROM payment_gateways;

-- ============================================================
-- POST-CLEANUP: OPTIMIZE DATABASE
-- ============================================================

-- Optimize all tables after cleanup
-- OPTIMIZE TABLE users;
-- OPTIMIZE TABLE patients;
-- OPTIMIZE TABLE doctors;
-- OPTIMIZE TABLE appointments;
-- OPTIMIZE TABLE patient_queues;

-- ============================================================
-- VERIFY CLEANUP
-- ============================================================

-- List all tables to verify removals
SHOW TABLES;

-- Check database size
SELECT 
    table_schema AS 'Database',
    SUM(data_length + index_length) / 1024 / 1024 AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
GROUP BY table_schema;
