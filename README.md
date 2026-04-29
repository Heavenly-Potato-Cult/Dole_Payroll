# DOLE Payroll System

A comprehensive payroll management system for **DOLE Regional Office 9**, built with Laravel 11. This system handles regular payroll computation, special payroll processing, travel expense vouchers (TEV), government remittance reports, and employee management with role-based access control.

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Installation](#installation)
  - [Docker Setup](#docker-setup)
  - [XAMPP Setup](#xampp-setup)
- [HRIS Integration](#hris-integration)
- [Database Management](#database-management)
- [User Roles & Permissions](#user-roles--permissions)
- [Known Issues & Gaps](#known-issues--gaps)
- [Project Structure](#project-structure)
- [Documentation](#documentation)

---

## ✨ Features

### Core Modules

- **Employee Management**
  - Employee records with salary grade and step increments
  - Division/department management
  - Employee promotion history tracking
  - Deduction enrollment (GSIS, PhilHealth, Pag-IBIG, loans, etc.)

- **Payroll Processing**
  - Regular payroll batch creation and computation
  - Automated deduction calculations (government contributions, loans, union dues)
  - Withholding tax computation based on BIR TRAIN Law
  - Payroll workflow: Draft → Computed → Pending Accountant → Certified → Approved → Locked
  - Individual payslip generation (PDF)

- **Special Payroll**
  - Newly hired payroll (prorated salary)
  - Salary differential processing
  - NOSI/NOSA (Not on Station/Not on Account) processing

- **Travel Expense Voucher (TEV)**
  - TEV request creation and approval workflow
  - Itinerary planning and per-diem computation
  - Liquidation submission and approval
  - TEV report generation (Itinerary, Travel Completed, Annex-A)

- **Government Remittance Reports**
  - GSIS detailed and summary reports
  - Pag-IBIG (HDMF) remittance reports (P1, P2, MPL, CAL, Housing)
  - PhilHealth CSV export
  - SSS voluntary contribution reports
  - LBP loan amortization
  - CARESS union dues and mortuary contributions
  - BTR refund reports

- **Office Orders**
  - Office order creation and approval
  - Integration with TEV system

- **User Management**
  - Role-based access control (7 roles)
  - User role assignment and activation
  - Signatory management per role

---

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 11 (PHP 8.2)
- **Database**: MySQL
- **Authentication**: Laravel Sanctum + JWT (for HRIS SSO)
- **Authorization**: Spatie Laravel Permission

### Key Packages
- `barryvdh/laravel-dompdf` - PDF generation
- `maatwebsite/excel` - Excel exports
- `spatie/laravel-backup` - Database backups
- `spatie/laravel-permission` - Role-based permissions
- `livewire/livewire` - Dynamic UI components
- `firebase/php-jwt` - JWT token handling
- `knuckleswtf/scribe` - API documentation

### Frontend
- Blade templates with Tailwind CSS
- Livewire for interactive components

---

## 🏗 Architecture

The system follows a standard Laravel MVC architecture with a service layer:

```
Routes (web.php)
  └── Controllers (app/Http/Controllers/)
        ├── FormRequests (app/Http/Requests/)     — Input validation
        ├── Resources    (app/Http/Resources/)    — API output shaping
        └── Services     (app/Services/)          — Business logic
              └── Models (app/Models/)            — Eloquent ORM
```

### Key Services
- `PayrollComputationService` - Core payroll calculation logic
- `DeductionService` - Government contribution and loan computations
- `TevComputationService` - TEV per-diem and expense calculations
- `SalaryDifferentialService` - Salary differential processing
- `NewlyHiredPayrollService` - Prorated payroll for new hires
- `AttendanceService` - HRIS attendance integration
- `HrisApiService` - External HRIS API client
- `ReportService` - Report data aggregation (stub - needs implementation)

---

## 📦 Installation

### Docker Setup (Recommended for isolated environment)

**Requirements:**
- Docker Desktop (Windows, macOS, or Linux)
- Docker Desktop must be running before starting the system

**Quick Start:**

```powershell
# Clone the repository
git clone <repository-url>
cd Dole_Payroll

# Initial setup (Windows)
.\initial_start.bat
```

This script will:
- Build and start Docker containers
- Install PHP dependencies
- Generate application key
- Run database migrations
- Start the Laravel development server

**Subsequent starts:**
```powershell
docker compose up
```

The application will be available at `http://localhost:8000`

---

### XAMPP Setup (Alternative for local development)

**Requirements:**
- XAMPP installed and running
- PHP 8.2+
- Composer

**Quick Start:**

```bash
# Clone the repository
git clone <repository-url>
cd Dole_Payroll

# Install dependencies
composer install
npm install

# Automated XAMPP setup (Windows)
.\setup-xampp.bat
```

This script will:
- Auto-detect XAMPP installation
- Start Apache and MySQL services
- Create the `dole_payroll` database
- Run Laravel migrations
- Import initial data from `dole_payroll.sql` (if available)

**Manual Setup (if automation fails):**

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure .env for XAMPP
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=dole_payroll
# DB_USERNAME=root
# DB_PASSWORD=

# Create database in phpMyAdmin
# CREATE DATABASE dole_payroll;

# Run migrations
php artisan migrate

# Start Laravel
php artisan serve
```

Access at: `http://localhost:8000`

---

## 🔗 HRIS Integration

The system integrates with an external HRIS (Human Resource Information System) for:
- Employee Single Sign-On (SSO) via JWT tokens
- Attendance data for payroll computation
- Employee data synchronization

### HRIS Simulation Server (Development)

A simulation HRIS server is included in the `HRIS/` directory for development:

```bash
cd HRIS
npm install
npm start
```

The HRIS server runs on `http://localhost:3001`

**Features:**
- Employee login with Employee ID (EMP001-EMP082) and password (pass123)
- JWT token generation for SSO
- Navigation links to Payroll and TEV systems
- Dummy data for 82 employees with attendance records

**JWT Configuration:**
- Secret: `dole-hris-payroll-shared-secret-2024`
- Expiration: 1 hour
- Issuer: `hris-system`
- Audience: `payroll-system`

**Laravel Integration:**
- Payroll: `http://localhost:8000/hris-auth?token={jwt}`
- TEV: `http://localhost:8000/tev-hris-auth?token={jwt}`

---

## 💾 Database Management

### Google Drive Integration

The project includes scripts for database backup and sharing via Google Drive using rclone:

**Setup (one-time per team member):**
```bash
# Download rclone
.\get-rclone.bat

# Configure rclone
rclone config
# Follow prompts: name=gdrive, storage=18, scope=1, auto config=y
```

**Usage:**
```bash
# Pull latest database from Google Drive
.\pull-from-gdrive.bat

# Push database to Google Drive
.\push-to-gdrive.bat

# List Google Drive backups
.\gdrive-list.bat

# Download specific backup
.\gdrive-download.bat <file-id>
```

**Scheduled Backup:**
```bash
gdrive-backup.bat
# Can be set up in Windows Task Scheduler for automated backups
```

### Local Backup/Restore

```bash
# Backup database
.\backup-data.bat

# Restore database
.\restore-data.bat
```

---

## 👥 User Roles & Permissions

The system uses role-based access control with the following roles:

| Role | Description |
|------|-------------|
| `payroll_officer` | Full payroll management, employee records, deduction types |
| `hrmo` | Employee management, payroll review, TEV approval |
| `accountant` | Payroll certification, TEV approval, reports |
| `ard` (Assistant Regional Director) | Payroll approval, TEV approval |
| `chief_admin_officer` | High-level approvals, reports |
| `cashier` | Payroll processing, TEV liquidation approval |
| `budget_officer` | Office order approval, TEV approval |
| `super_admin` | Full system access, user management |

**Role Groups:**
- **Payroll Group**: payroll_officer, hrmo, accountant, ard, chief_admin_officer, cashier, budget_officer
- **Special Payroll Group**: payroll_officer, hrmo, accountant, ard, chief_admin_officer
- **TEV Group**: All roles (for viewing and submitting requests)
- **TEV Approval Group**: hrmo, accountant, budget_officer, ard, cashier, chief_admin_officer

---

## ⚠️ Known Issues & Gaps

A comprehensive gap analysis has been documented in [`SYSTEM-GAP-ANALYSIS.md`](SYSTEM-GAP-ANALYSIS.md). Key critical gaps include:

### Critical Issues (Tier 1)
- **G-01**: `ReportController` missing 16 methods - causes 500 errors on report pages
- **G-02**: HRIS attendance integration stubbed - all payroll shows zero attendance deductions
- **G-03**: YTD gross not tracked - withholding tax under-computed in later periods

### High Impact (Tier 2)
- **G-04**: 7 government export classes empty - cannot generate official remittance reports
- **G-05**: Zero automated test suite - no regression safety net
- **G-06**: `EmployeePolicy` and `TevPolicy` empty - authorization not formalized

### Medium Impact (Tier 3)
- **G-07**: 10 standard report views are stubs
- **G-08**: Payslip and detail views are stubs
- **G-09**: 3 UI components (alert, status-badge, approval-timeline) are empty
- **G-10**: FormRequest validation rules missing for approval and special payroll

See [`SYSTEM-GAP-ANALYSIS.md`](SYSTEM-GAP-ANALYSIS.md) for complete details, implementation roadmap, and risk register.

---

## 📁 Project Structure

```
Dole_Payroll/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # All controllers (18 controllers)
│   │   ├── Requests/            # Form request validation
│   │   ├── Middleware/          # Custom middleware (JWT, role checks)
│   │   └── Resources/           # API resource transformers
│   ├── Models/                  # Eloquent models (21 models)
│   ├── Services/                # Business logic layer (9 services)
│   ├── Policies/                # Authorization policies
│   ├── Exports/                 # Excel export classes (15 exports)
│   └── Traits/                  # Reusable traits
├── database/
│   ├── migrations/              # Database migrations
│   ├── seeders/                 # Database seeders
│   └── factories/               # Model factories for testing
├── resources/
│   ├── views/                   # Blade templates
│   │   ├── components/          # Reusable Blade components
│   │   ├── layouts/             # Main layout templates
│   │   ├── auth/                # Authentication views
│   │   ├── dashboard/           # Dashboard views
│   │   ├── employees/           # Employee management views
│   │   ├── payroll/             # Payroll views
│   │   ├── tev/                 # TEV views
│   │   ├── reports/             # Report views
│   │   └── payslip/             # Payslip PDF templates
│   └── lang/                    # Language files
├── routes/
│   ├── web.php                  # Web routes
│   └── api.php                  # API routes
├── HRIS/                        # HRIS simulation server (Node.js)
│   ├── server.js
│   ├── config.js
│   └── package.json
├── config/                      # Configuration files
├── public/                      # Public assets
├── storage/                     # Application storage
├── tests/                       # Test files (minimal coverage)
├── .env.example                 # Environment template
├── composer.json                # PHP dependencies
├── package.json                 # Node dependencies
├── docker-compose.yaml          # Docker configuration
├── Dockerfile                   # Docker image definition
├── setup-xampp.bat              # XAMPP automated setup
├── initial_start.bat            # Docker initial setup
├── pull-from-gdrive.bat         # Google Drive pull script
├── push-to-gdrive.bat           # Google Drive push script
├── README.md                    # This file
├── README-SETUP.md              # Detailed XAMPP setup guide
├── TEAM-SETUP.md                # Team collaboration setup
└── SYSTEM-GAP-ANALYSIS.md       # Comprehensive gap analysis
```

---

## 📚 Documentation

- [`README-SETUP.md`](README-SETUP.md) - Detailed XAMPP setup and troubleshooting
- [`TEAM-SETUP.md`](TEAM-SETUP.md) - Team collaboration and Google Drive setup
- [`SYSTEM-GAP-ANALYSIS.md`](SYSTEM-GAP-ANALYSIS.md) - Complete gap analysis and implementation roadmap
- [`HRIS/README.md`](HRIS/README.md) - HRIS simulation server documentation

---

## 🚀 Quick Start Commands

```bash
# Docker
docker compose up
docker compose down

# XAMPP
php artisan serve

# Database
php artisan migrate
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Testing
php artisan test

# Code quality
./vendor/bin/pint
php artisan ide-helper:generate
```

---

## 📝 License

This project is proprietary software for DOLE Regional Office 9.

---

## 👨‍💻 Development Team

DOLE Regional Office 9 - Payroll Management System

---

**Last Updated**: April 2026