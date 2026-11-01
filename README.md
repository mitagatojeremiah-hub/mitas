# Mitas Company System

A full-stack inventory, purchasing, sales, and invoicing system with:
- real-time stock overview
- low-stock alerts
- product management with quantity, grade, manufacturer, buy/sell price, and location
- order and invoicing workflow foundation
- English/Swahili language switcher
- light/dark theme support

## Run locally

```bash
npm install
npm run dev
```

Then open http://localhost:3000

## API

- GET /api/health
- GET /api/products
- POST /api/products
- GET /api/alerts

### PHP lint (syntax check)

If you want to run quick PHP syntax checks for the main files, you can use the bundled helper script. On Windows with PowerShell run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\check-php.ps1
```

The script will try to find `php.exe` on your PATH or in common locations such as `C:\xampp\php\php.exe`. If PHP is not found, run the lint directly with the full path to your PHP executable, for example:

```
C:\xampp\php\php.exe -l index.php
C:\xampp\php\php.exe -l dashboard_api.php
```
# Mitas Company System

A full-stack inventory, purchasing, sales, and invoicing system with:
- real-time stock overview
- low-stock alerts
- product management with quantity, grade, manufacturer, buy/sell price, and location
- order and invoicing workflow foundation
- English/Swahili language switcher
- light/dark theme support

## Run locally

```bash
npm install
npm run dev
```

Then open http://localhost:3000

## API

- GET /api/health
- GET /api/products
- POST /api/products
- GET /api/alerts
