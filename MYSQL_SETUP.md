# MySQL Database Setup

The Laravel backend has been configured to use MySQL database. Follow these steps to complete the setup:

## Step 1: Update Database Credentials

Edit the `.env` file and update the MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=almajd_certificates
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

**Important:** Replace `your_mysql_username` and `your_mysql_password` with your actual MySQL credentials.

## Step 2: Create the Database

You can create the database in one of the following ways:

### Option A: Using MySQL Command Line

```bash
mysql -u root -p
```

Then in MySQL prompt:
```sql
CREATE DATABASE almajd_certificates CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Option B: Using phpMyAdmin or MySQL Workbench

1. Open phpMyAdmin or MySQL Workbench
2. Create a new database named `almajd_certificates`
3. Set collation to `utf8mb4_unicode_ci`

### Option C: Using the Setup Script

If your MySQL credentials are already correct in `.env`, you can run:

```bash
./setup-mysql.sh
```

## Step 3: Run Migrations

Once the database is created and credentials are correct, run:

```bash
php artisan migrate
```

Or if you want to force it:

```bash
php artisan migrate --force
```

## Step 4: Verify

After running migrations, you should see:

- ✓ `migrations` table created
- ✓ `certificates` table created with all columns

## Troubleshooting

### Access Denied Error

If you get "Access denied" error:

1. Check your MySQL username and password in `.env`
2. Make sure MySQL server is running: `mysql.server start` (macOS) or `sudo systemctl start mysql` (Linux)
3. Verify MySQL user has CREATE DATABASE privileges

### Database Already Exists

If the database already exists, migrations will still work. The `IF NOT EXISTS` clause prevents errors.

### Connection Refused

If you get "Connection refused":

1. Check if MySQL is running
2. Verify `DB_HOST` and `DB_PORT` in `.env`
3. Try `127.0.0.1` instead of `localhost` for `DB_HOST`

## Current Configuration

The `.env` file is currently set to:

- **Database Name:** `almajd_certificates`
- **Host:** `127.0.0.1`
- **Port:** `3306`
- **Username:** `root`
- **Password:** (empty - update if needed)

**Please update the username and password according to your MySQL setup.**

