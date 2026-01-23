# Adminer Database Management Guide

## What is Adminer?

Adminer is a lightweight database management tool (similar to phpMyAdmin) contained in a single PHP file. It provides a complete interface for managing your MySQL database.

## Accessing Adminer

**IMPORTANT:** Only users with **ROLE_ADMIN** can access Adminer. Regular users will be denied access.

### Production (Railway)
1. Go to: `https://tripplanner-prod.up.railway.app/adminer`
2. You'll be redirected to login if not authenticated
3. Login with an **admin account** (must have ROLE_ADMIN)
4. You'll then see the Adminer database management interface

### Local Development
1. Go to: `http://localhost:8081/adminer`
2. Login required (admin account)

## How to Promote a User to Admin

### On Railway (Production)
You need to run a command via Railway CLI:

```bash
# Install Railway CLI if not installed
npm i -g @railway/cli

# Login to Railway
railway login

# Link to your project
railway link

# Promote user to admin (replace with actual email)
railway run php bin/console app:user:promote user@example.com
```

### Local Development
```bash
docker-compose exec php bin/console app:user:promote user@example.com
```

You'll see a success message:
```
[OK] User "user@example.com" has been promoted to admin!
```

## Connecting to Database

When you see the Adminer login screen, use these credentials:

### Production (Railway)
```
System: MySQL
Server: shortline.proxy.rlwy.net:51561
Username: root
Password: LLOUlImzUcPUrOsndtBcHjZOYwUfUcdk
Database: railway
```

**Note:** These credentials are from your `DATABASE_URL` environment variable in Railway.

### Local Development
```
System: MySQL
Server: database
Username: tripplanner
Password: tripplanner
Database: tripplanner
```

## Features You Can Use

Once connected, you can:

### 1. Browse Tables
- Click on any table name to see its data
- View all rows, columns, and relationships

### 2. Edit Data
- Click on any row to edit it
- Add new rows with "New item"
- Delete rows with the checkbox + "Delete" button

### 3. Run SQL Queries
- Click "SQL command" at the top
- Write and execute custom SQL queries
- Export query results

### 4. Database Structure
- View table structures
- See indexes and foreign keys
- Understand relationships between tables

### 5. Import/Export
- **Export**: Click "Export" to download database backup
- **Import**: Click "Import" to restore or add data

### 6. Search
- Use "Search data" to find specific records across tables
- Search by column values

## Common Tasks

### View All Users
1. Click on "user" table
2. See all registered users
3. View emails, roles, creation dates

### Add a Test User Manually
1. Go to "user" table
2. Click "New item"
3. Fill in:
   - email: test@example.com
   - roles: `["ROLE_USER"]`
   - password: (use hashed password from another user or register normally)

### Backup Database
1. Click "Export" in the left menu
2. Select tables to export (or keep "All" selected)
3. Choose format: SQL (recommended)
4. Click "Export"
5. Save the .sql file locally

### Restore Database
1. Click "Import"
2. Choose your .sql backup file
3. Click "Execute"

## Security Notes

⚠️ **IMPORTANT:**
- Adminer is protected by Symfony authentication (**ROLE_ADMIN required**)
- Only admin users can access it in production
- Regular users will receive "Access Denied" error
- Keep your database credentials secure
- Only promote trusted users to admin
- Consider removing Adminer from production if not needed long-term

## Troubleshooting

### "Access Denied" Error
- Check your database credentials match the environment variables
- Verify Railway MySQL service is running

### "Login Required" on Production
- This is normal! You must login to TripPlanner first
- Go to `/login`, then access `/adminer.php`

### Can't See Any Tables
- Make sure you entered the correct database name ("railway" for production)
- Check that migrations have run successfully

## Removing Adminer

If you want to remove Adminer later:

1. Delete the file: `public/adminer.php`
2. Remove the access control from `config/packages/security.yaml`:
   ```yaml
   - { path: ^/adminer.php, roles: ROLE_USER }
   ```
3. Commit and deploy

## Alternative: Railway CLI

You can also access the database via Railway CLI:
```bash
railway login
railway link
railway run mysql -u root -p railway
```

Then enter the password when prompted.