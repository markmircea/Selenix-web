# Selenix Web Server Deployment Guide

## Files to Upload to Your Web Server

Upload these files from `C:\projects\Selenix-web\` to your web server's root directory:

### Core Files:
- `index.html` (updated with new download links)
- `download.php` (email registration + download system)
- `admin.php` (admin panel for viewing statistics)
- `setup-database.php` (run once to create database table)
- `license-generator.php` (backup/standalone license generator)

### Required Files:
- `styles.css`
- `script.js`
- All folders: `components/`, `css/`, `docs/`, `js/`, `product/`
- **`Selenix-win-unpackedBETA.zip`** (your actual app file)

## Deployment Steps:

### 1. Upload Files
Upload all files to your web server's public directory

### 2. Set Up Database
Visit: `https://yoursite.com/setup-database.php`
- This will create the `downloads` table in your `aibrainl_selenix` database
- Run this only once

### 3. Upload Your App
Make sure `Selenix-win-unpackedBETA.zip` is in the same directory as `download.php`

### 4. Test the System
- **Download page**: `https://yoursite.com/download.php`
- **Admin panel**: `https://yoursite.com/admin.php` (password: selenix2024)
- **Main website**: `https://yoursite.com/`

## Database Configuration:
- **Database**: aibrainl_selenix
- **Username**: aibrainl_selenix  
- **Password**: She-wolf11
- **Table**: downloads

## Security Notes:
- Change the admin password in `admin.php` (line 6: `$ADMIN_PASSWORD`) selenix2024
- The encryption keys are already generated and secure
- Keep database credentials secure

## What Happens When Users Download:
1. User enters email on download page
2. System generates encrypted 1-year license
3. Email + license stored in database
4. User downloads ZIP containing:
   - Your `Selenix-win-unpackedBETA.zip`
   - `license.txt` with encrypted key

## Admin Panel Features:
- View total downloads and unique users
- See download statistics and charts
- Monitor recent downloads with email addresses
- Track download trends over time

## Troubleshooting:
- If database connection fails, verify credentials in cPanel
- If download fails, check that ZIP file exists and has proper permissions
- If admin panel won't load, verify MySQL service is running