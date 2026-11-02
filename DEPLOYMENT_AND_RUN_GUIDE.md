# WWJ Dynamic Public HTML - Deployment & Database Operations Guide

This document contains all the commands and operational procedures for running, configuring, and deploying the **WWJ Multi-Journal Dynamic Platform** locally and in production.

---

## 1. Setting Database Credentials

All database configurations are centralized inside **`config.php`**. The application uses an isolated PDO connection for each journal based on the active selection.

Open `config.php` and locate the `$JOURNALS` array:

```php
// Local Development (XAMPP / Laragon / Local MySQL)
'gjra' => [
    'db' => [
        'host'    => '127.0.0.1',
        'user'    => 'root',
        'pass'    => '',
        'name'    => 'fulltext_wwjgjra',
        'charset' => 'utf8mb4'
    ],
    // ...
],

// Production (cPanel / Remote Linux MySQL)
'gjra' => [
    'db' => [
        'host'    => 'localhost',
        'user'    => 'cpanelUser_wwjgjra',
        'pass'    => 'YourProductionPasswordHere',
        'name'    => 'cpanelUser_wwjgjra',
        'charset' => 'utf8mb4'
    ],
    // ...
]
```

Repeat this configuration block for each journal you intend to serve:
- `gjra`
- `paripex`
- `ijsr`
- `ijar`

---

## 2. Running Locally (Development Mode)

### Method A: PHP Built-in Server (Fastest, zero configuration)
Run this command from inside the `dynamic-public-html` directory:

```cmd
cd D:\projects\wwj\dynamic-public-html
php -S localhost:8000
```

Open your browser and navigate to:
- **Default:** `http://localhost:8000`
- **GJRA:** `http://localhost:8000/?journal=gjra`
- **PARIPEX:** `http://localhost:8000/?journal=paripex`
- **IJSR:** `http://localhost:8000/?journal=ijsr`
- **IJAR:** `http://localhost:8000/?journal=ijar`

### Method B: Using XAMPP / Apache VirtualHost
To link directly to Apache's document root without moving files:

```cmd
mklink /D "C:\xampp\htdocs\wwj" "D:\projects\wwj\dynamic-public-html"
```
Access via: `http://localhost/wwj`

---

## 3. Production Deployment (cPanel / Live Server)

### Step 1: Uploading Files
Upload all files from `dynamic-public-html/` into your domain's web root (`public_html/` or a subdomain folder) using SFTP or cPanel File Manager.

### Step 2: Setting Directory Permissions
The application requires write access to store manuscript submissions and generated logs:

```bash
chmod -R 755 storage/
chmod -R 775 storage/uploads/
```

Ensure the web server user (`www-data`, `nobody`, or your cPanel user) has ownership:
```bash
chown -R cpaneluser:cpaneluser storage/
```

### Step 3: Hard-Locking the Journal for a Domain
By default, the platform allows switching journals via query parameters (for development/preview). In production, you must lock the site to a specific journal by writing its key into the `.journal_lock` file:

```cmd
:: Windows CMD
echo gjra > .journal_lock

:: Linux Shell
echo -n "gjra" > .journal_lock
```

Available lock identifiers: `gjra`, `paripex`, `ijsr`, `ijar`.

When `.journal_lock` is present, the application enforces that journal regardless of any URL parameters, preventing cross-journal tampering.

### Step 4: Web Server URL Rewriting (.htaccess)
Ensure Apache / LiteSpeed has `mod_rewrite` enabled. The bundled `.htaccess` routes requests smoothly to `index.php`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

---

## 4. Verification & Health Check

1. Open the homepage: confirm title, ISSN, and impact factor match the journal locked in `.journal_lock`.
2. Test a sample manuscript submission: confirm the `.doc` / `.docx` file lands inside `storage/uploads/<journal_code>/` and the database records the submission with the correct table prefix (`GJRA_`, `PIJR_`, etc.).
3. Verify admin panel accessibility: navigate to `/admin/` and ensure session security is operational.
