# Mitas Business System

Responsive PHP business management system for inventory, customers, purchases, sales, invoices, and payments. It uses SQLite for local development and PostgreSQL for persistent hosting such as Supabase.

## Local development

Use PHP 8.1+ with PDO SQLite installed. From PowerShell, set a strong one-time bootstrap password and start PHP's built-in web server:

```powershell
$env:MITAS_INITIAL_ADMIN_PASSWORD = "<generate a unique strong password>"
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000. The first local database creates the Admin username `admin`; sign in with the bootstrap password and choose a new password when prompted. Admin-assisted password recovery is available in Account management.

## Supabase and Vercel

1. Create a Supabase Postgres project. Copy the connection URI from **Connect → Session pooler**. This app uses PDO prepared statements; session mode supports those statements. Include `sslmode=require`.
2. Add the following environment variables to the Vercel project for Preview and Production as appropriate:
   - `DATABASE_URL`: the Supabase Postgres session-pooler URI.
   - `MITAS_INITIAL_ADMIN_PASSWORD`: a unique strong password (12+ characters with upper, lower, number, and symbol). The first request to an empty database creates username `admin` and requires a password change after sign-in.
3. Redeploy after setting the environment variables. Do not put database secrets in GitHub or chat.
4. Use Supabase's database backup and restore features for PostgreSQL. The in-app file backup endpoints remain available for local SQLite deployments.

The app initializes its PostgreSQL tables on its first request. Vercel has ephemeral function storage, so `DATABASE_URL` must be configured before using the deployed app; it will not silently use a local SQLite file.

## Importing existing SQLite data

The SQLite database is not stored in this repository. To preserve existing Mitas accounts and business records, first obtain a copy of the current SQLite file and keep it outside the repository. Run the import utility from a local copy of this branch with PHP PDO SQLite and PDO PostgreSQL enabled:

```powershell
$env:DATABASE_URL = "<Supabase session-pooler URI>"
$env:MITAS_SQLITE_PATH = "C:\path\outside\the\repo\mitas_system.sqlite"
php .\scripts\migrate_sqlite_to_postgres.php
```

Run the import against a fresh, empty Supabase `public` schema before the first Mitas web request. The utility copies table definitions, indexes, and rows, upgrades the account schema, and omits old sessions and reset tokens. It refuses to run when the target schema already contains tables. User accounts and business records are copied; session data and any old email recovery tokens are not.

Without that SQLite file, the app can create a new Supabase database, but the old accounts and business records cannot be carried over.

## Password recovery without email

Forgotten passwords are reset by an Admin. In Account management, set a strong temporary password for the account. The user must change it at the next sign-in. A username alone never authorizes a password reset.

If the only Admin is locked out, an operator with direct database access can reset it from a trusted machine:

```powershell
$env:DATABASE_URL = "<Supabase session-pooler URI>"
$env:MITAS_ADMIN_RECOVERY_PASSWORD = "<unique strong temporary password>"
php .\scripts\reset_admin_password.php admin
Remove-Item Env:MITAS_ADMIN_RECOVERY_PASSWORD
```

The command-line recovery script runs only in CLI mode, accepts only an active Admin account, invalidates existing sessions, and requires a password change at next sign-in. Never put the recovery password in Git or chat.

## PHP syntax helper

On Windows with PHP installed, run the repository's lint helper:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\check-php.ps1
```
