# Complete Operations Guide: WWJ Multi-Journal Dynamic Platform

This repository is a unified, multi-tenant academic journal publishing platform that can run any of the 4 journals dynamically on any domain or locally, backed directly by Supabase Cloud Database.

---

## 1. What This System Does

Instead of maintaining 4 separate website codebases, this single folder powers any of the 4 journals:

1. **GJRA** &mdash; Global Journal for Research Analysis (ISSN: `2277-8160`, Prefix: `GJRA_`)
2. **PARIPEX** &mdash; Paripex - Indian Journal of Research (ISSN: `2250-1991`, Prefix: `PIJR_`)
3. **IJSR** &mdash; International Journal of Scientific Research (ISSN: `2277-8179`, Prefix: `IJSR_`)
4. **IJAR** &mdash; Indian Journal of Applied Research (ISSN: `2249-555X`, Prefix: `IJAR_`)

When you activate or lock a journal, the platform automatically:
* Dynamically loads that journal's **Form, Banners, Logos, Guidelines, and Subjects**.
* Saves uploaded Word documents (`.doc` / `.docx`) into that journal's dedicated local storage partition.
* Inserts all author submissions, tracking IDs, and co-authors into **Supabase Cloud Database** in real-time.

---

## 2. How to Run Locally

You can run the entire platform locally on your computer with a single command:

```powershell
C:\tools\php\php.exe -S 127.0.0.1:8080 -t D:\projects\wwj\dynamic-public-html
```

### URLs to Access in Your Browser:
* **Main Journal Portal:** `http://127.0.0.1:8080/`
* **Admin Lock & Switch Gate:** `http://127.0.0.1:8080/admin/setup.php`

*(To stop the local server anytime, press `Ctrl + C` in your terminal).*

---

## 3. How to Deploy to Shared Hosting / cPanel

### Step 1: Upload Files
Upload all files and folders from `dynamic-public-html/` into your domain's `public_html/` folder using FTP or cPanel File Manager.

### Step 2: Zero Database Setup Needed on cPanel!
* **No MySQL database setup is needed** on cPanel.
* The system connects to **Supabase Cloud Database** automatically over secure HTTPS using the pre-configured credentials in `config.php`.

---

## 4. How to Lock, Unlock & Switch Journals

### Master Admin Password:
🔑 **`wwj@MasterLock2026`**

### Method A: Web Admin Setup Gate (Recommended)
1. Open your browser and navigate to:
   ```
   https://yourdomain.com/admin/setup.php
   ```
   *(or `http://127.0.0.1:8080/admin/setup.php` locally)*

2. **To Lock to a Journal:**
   * Select the target journal from the dropdown list.
   * Enter the master password: `wwj@MasterLock2026`
   * Click **[Lock Domain to Selected Journal]**.
   * The domain is now locked and will strictly serve that journal.

3. **To Unlock / Change Journal:**
   * Enter the master password: `wwj@MasterLock2026`
   * Click **[Unlock System / Release Domain]**.
   * Pick a new journal from the dropdown and lock it again.

### Method B: Via `.journal_lock` File (Direct File Edit)
In the root directory of your website (`public_html/`), you will find a file named `.journal_lock`.
* Write one of these 4 lowercase keys to switch immediately:
  * `gjra`
  * `paripex`
  * `ijsr`
  * `ijar`
* Delete `.journal_lock` to release/unlock the domain.

---

## 5. Active Routes & Navigation

* **Submission Portal / Home:**
  `https://yourdomain.com/` or `https://yourdomain.com/page/u/upload-your-article`
* **Author Guidelines:**
  `https://yourdomain.com/page/p/author-guidelines`
* **Downloads (Copyright Form & Templates):**
  `https://yourdomain.com/page/p/downloads`
* **Admin Setup Gate:**
  `https://yourdomain.com/admin/setup.php`

---

## 6. Supabase Database Architecture

All 4 tables live in your Supabase project (`wzgzgqtegiqwagxvhxku.supabase.co`):

| Table | Purpose |
| :--- | :--- |
| `journals` | Multi-tenant registry of journals, ISSNs, prefixes, and subjects |
| `userarticle` | Submissions, author contacts, manuscript titles, file paths, and tracking codes |
| `co_authors` | Co-authors linked to submissions via `article_id` |
| `contact_queries` | Messages and queries submitted from the editorial desk |

---

## 7. Security Hardening Built-in (OWASP ASVS 4.0)

* **Deep Byte-Level File Inspection:** Uploads are strictly validated against genuine Microsoft Word binary magic headers (`PK\x03\x04` for docx, `\xD0\xCF\x11\xE0` for doc). Script execution inside upload directories is completely blocked via `.htaccess`.
* **Universal HTTPS REST Ingestion:** No database connection credentials or open SQL ports are exposed on the host. Submissions run over encrypted TLS to Supabase.
* **Master Lock Protection:** Prevents unauthorized domain switching.
