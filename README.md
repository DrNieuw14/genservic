# GENSERVIC Personnel Attendance System

## Overview
GENSERVIC is a PHP + MySQL attendance management system with role-based access, dashboard analytics, and downloadable reports.

## Updated Features
- Bootstrap 5 responsive dashboard with attendance summary cards.
- Attendance workflow with automatic `Present/Late` status, Time In/Time Out, and duplicate Time In prevention.
- Attendance history with date filter and personnel-name search.
- Prepared statements and input sanitation on key attendance and report pages.
- Session authentication middleware for protected pages.
- Report exports for PDF and Excel formats.
- Query performance support via recommended DB indexes (`config/performance_indexes.sql`).

## Folder Organization
- `/admin` - Admin pages (dashboard)
- `/personnel` - Personnel-facing route entry
- `/attendance` - Attendance module pages
- `/config` - DB/auth/layout configuration
- `/assets` - Shared CSS/JS assets
- `/reports` - Attendance report pages and downloads

## Setup
1. Clone repository.
2. Import your MySQL schema/data.
3. Update DB credentials in `config/database.php`.
4. (Optional but recommended) run indexes in `config/performance_indexes.sql`.
5. Serve project with Apache or PHP built-in server.