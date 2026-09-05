# VolNet

VolNet is a web-based volunteer network and community engagement platform built with PHP and Supabase (PostgreSQL) / MySQL. It empowers volunteers, organizations, and administrators with event discovery, volunteer applications, attendance tracking, automated certificates, blog publishing, and community ratings.

---

## 🚀 Quick Start

### On Linux / macOS
```bash
./start.sh
```

### On Windows
* **Option 1 (Double-Click):** Double-click `start.bat`.
* **Option 2 (Command Prompt):**
  ```cmd
  start.bat
  ```
* **Option 3 (PowerShell):**
  ```powershell
  .\start.ps1
  ```

The server will automatically boot and open at **[http://localhost:8000](http://localhost:8000)**.

---

## 📁 Project Structure

```
volnet14/
├── admin/                         # Admin portal (dashboard, volunteers, organizations, complaints)
├── auth/                          # Authentication (login, register, logout, validation)
├── organization/                  # Organization portal (dashboard, events, applicants, attendance)
├── pages/                         # Public pages (homepage, search, projects, blogs, contact, events)
├── volunteer/                     # Volunteer portal (dashboard, profile, applications, certificates, reviews)
│
├── assets/                        # css, js, images, fonts
├── config/                        # Universal Supabase PostgreSQL & MySQL database layer
├── database/                      # MySQL and Supabase PostgreSQL schema scripts
├── includes/                      # auth.php, helpers.php, db.php
├── uploads/                       # User-uploaded files storage
│
├── index.php                      # Root entry point & automatic role router
├── start.sh                       # Linux launcher
├── start.bat                      # Windows Batch launcher
├── start.ps1                      # Windows PowerShell launcher
├── .env                           # Active environment configuration
└── .env.example                   # Environment configuration template
```

---

## ⚙️ Configuration

Copy `.env.example` to `.env` and configure your database credentials:

```ini
# Supabase PostgreSQL (Default)
DB_DRIVER=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=6543
DB_NAME=postgres
DB_USER=postgres.ibuonolujhesqmdatmyh
DB_PASS=your_supabase_password
```
