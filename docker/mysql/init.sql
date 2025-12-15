-- MySQL initialization script for Premier League Simulation (Development)

-- Set default charset and collation
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Create development user with necessary privileges
CREATE USER IF NOT EXISTS 'premier_league_user'@'%' IDENTIFIED BY 'premier_league_password';
GRANT ALL PRIVILEGES ON premier_league.* TO 'premier_league_user'@'%';

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS premier_league 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE premier_league;

-- Optimize MySQL settings for development
SET GLOBAL innodb_buffer_pool_size = 128 * 1024 * 1024; -- 128MB
SET GLOBAL max_connections = 100;
SET GLOBAL query_cache_type = 1;
SET GLOBAL query_cache_size = 32 * 1024 * 1024; -- 32MB

-- Flush privileges
FLUSH PRIVILEGES;

-- Show databases to confirm
SHOW DATABASES; 