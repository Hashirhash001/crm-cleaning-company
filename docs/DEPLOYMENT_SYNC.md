# Production Deployment & Server Sync Summary

**Application:** Cleaning CRM (Laravel)  
**Production URL:** https://crm.launchquests.com  
**Last reviewed:** October 6, 2026  

---

## VPS / Hostinger Details

| Item | Value |
|------|-------|
| Hostname | `srv1109676.hstgr.cloud` |
| IP | `168.231.103.4` |
| Plan | KVM 2 |
| SSH user | `root` |
| SSH port | `22` |
| App path | `/var/www/html/cleaning-crm` |
| Public root | `/var/www/html/cleaning-crm/public` |
| Nginx config | `/etc/nginx/sites-available/crm.launchquests.com` |

SSH and access credentials are stored in local `.env` only (gitignored). Do **not** commit credentials to the repository.

```env
VPS_HOST=168.231.103.4
VPS_SSH_PORT=22
VPS_SSH_USER=root
VPS_DOMAIN=crm.launchquests.com
```

---

## Production Database

### Connection

| Setting | Value |
|---------|-------|
| Driver | MySQL |
| Host | `127.0.0.1` (localhost on VPS) |
| Port | `3306` |
| Database | `cleaning_crm` |
| Username | `crm_user` |
| Password | Stored in server `.env` only — not documented here |

### Overview

| Metric | Count |
|--------|-------|
| Total tables | 28 |
| Migrations applied | 68 |
| Total users | 107 |
| Active users | 98 |
| Branches | 3 |

### Branches

| ID | Name | Code | Domain | Active |
|----|------|------|--------|--------|
| 1 | CTREE | CTREE | ctree.co.in | Yes |
| 2 | BAYLEAF | BAYLEAF | bayleafclean.com | Yes |
| 3 | GINOVA | GINOVA | — | Yes |

Website leads are routed to the correct branch via `BranchResolver` using the `domain` column (CTREE → ctree.co.in, BAYLEAF → bayleafclean.com).

### Users by Role

| Role | Count | Description |
|------|-------|-------------|
| `worker` | 56 | Field workers assigned to jobs |
| `supervisor` | 24 | Supervisors overseeing field staff |
| `telecallers` | 15 | Lead calling and follow-up workflow |
| `super_admin` | 4 | Full system access |
| `lead_manager` | 1 | Operational + user management access |

9 users are inactive (`is_active = 0`).

### Key Record Counts

| Entity | Records |
|--------|---------|
| Leads | 36,954 |
| Customers | 4,753 |
| Jobs (work orders) | 5,671 |
| Services | 27 |
| Lead approvals | 1,960 |
| Lead follow-ups | 495 |
| Lead notes | 169 |
| Lead imports | 6 |
| Recruitment candidates | 6 |
| Recruitment departments | 3 |
| Recruitment positions | 3 |

### All Tables

| Table | Approx. rows | Purpose |
|-------|-------------|---------|
| `branches` | 3 | Business branches (CTREE, BAYLEAF, GINOVA) |
| `users` | 107 | CRM users and staff |
| `leads` | 31,878 | Incoming and managed leads |
| `lead_sources` | 8 | Lead origin types (WhatsApp, Google Ads, etc.) |
| `lead_approvals` | 1,960 | Lead approval workflow records |
| `lead_calls` | 0 | Call logs on leads |
| `lead_followups` | 495 | Scheduled lead follow-ups |
| `lead_notes` | 169 | Notes attached to leads |
| `lead_service` | 31,485 | Lead ↔ service pivot |
| `lead_imports` | 6 | Bulk import batches |
| `customers` | 2,899 | Customer records |
| `customer_notes` | 4 | Notes on customers |
| `jobs` | 5,757 | Work orders |
| `job_calls` | 0 | Call logs on jobs |
| `job_followups` | 5 | Job follow-up schedules |
| `job_notes` | 8 | Notes on jobs |
| `job_ratings` | 0 | Job quality ratings |
| `job_service` | 7,089 | Job ↔ service pivot |
| `job_staff` | 6,361 | Staff assigned to jobs |
| `services` | 27 | Service catalogue |
| `recruitment_departments` | 2 | HR departments |
| `recruitment_positions` | 2 | Open job positions |
| `recruitment_candidates` | 6 | Job applicants |
| `settings` | 0 | App settings key-value store |
| `migrations` | 55 | Laravel migration history |
| `failed_jobs` | 0 | Failed queue jobs |
| `password_reset_tokens` | 0 | Password reset tokens |
| `personal_access_tokens` | 0 | API tokens (Sanctum) |

> Row counts are from MySQL `information_schema` at time of audit and may be approximate for large InnoDB tables.

---

## Production Health Check (Read-Only Audit)

All checks were performed via SSH/HTTPS with **no changes** to the running server.

| Check | Status |
|-------|--------|
| Site reachable | ✅ Login page loads (HTTP 200) |
| DNS | ✅ `crm.launchquests.com` → `168.231.103.4` |
| SSL | ✅ Let's Encrypt (expires Dec 10, 2026) |
| Web server | ✅ nginx 1.18.0 (Ubuntu) |
| PHP | ✅ 8.1.33 + php8.1-fpm active |
| MySQL | ✅ Active |
| Laravel | ✅ 10.49.1 |
| Migrations | ✅ 68 ran, 0 pending |
| Response time | ~120–140 ms |

### Server Resources

| Resource | Usage |
|----------|-------|
| Disk | 4.8 GB / 97 GB (5%) |
| RAM | ~626 MB / 7.8 GB used |
| Load | ~0.15 (low) |
| Uptime | 6+ days at time of audit |
| App size | ~173 MB |

### Production App Config (non-secret)

| Setting | Value |
|---------|-------|
| `APP_NAME` | Cleaning CRM |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `http://168.231.103.4` *(should ideally be `https://crm.launchquests.com`)* |
| `CACHE_DRIVER` | `file` |
| `QUEUE_CONNECTION` | `sync` |
| `SESSION_DRIVER` | `file` |

---

## Server vs Local — What Was Missing

Before sync, production had **68 migrations** while the local repo had **60**. The server included features not present locally:

1. **Recruitment module** — departments, positions, candidates, reports
2. **BranchResolver service** — domain-based branch detection for website leads
3. **Notification bell** — real-time lead alerts in the top bar
4. **Branch domain column** — maps website domains to branches (CTREE, Bayleaf)
5. **Sidebar reorder** — Sales → Recruitment → Users & Analytics → Settings

Production code is deployed by **copy/FTP** (no git repo on the server).

---

## Files Pulled from Production

All files were downloaded with **read-only SCP**. Production was not modified.

### Recruitment Module (new)

| Type | Path |
|------|------|
| Controllers | `app/Http/Controllers/Recruitment/CandidateController.php` |
| | `app/Http/Controllers/Recruitment/DepartmentController.php` |
| | `app/Http/Controllers/Recruitment/PositionController.php` |
| | `app/Http/Controllers/Recruitment/ReportController.php` |
| Models | `app/Models/RecruitmentCandidate.php` |
| | `app/Models/RecruitmentDepartment.php` |
| | `app/Models/RecruitmentPosition.php` |
| Views | `resources/views/recruitment/` (11 blade files) |
| Migrations | `2026_08_15_170000_create_recruitment_departments_table.php` |
| | `2026_08_15_170001_create_recruitment_positions_table.php` |
| | `2026_08_15_170002_create_recruitment_candidates_table.php` |
| | `2026_08_15_173000_change_interviewer_to_text_in_recruitment_candidates.php` |
| | `2026_08_20_100000_change_sqft_column_type_in_leads_table.php` |
| | `2026_08_20_200000_add_place_and_district_to_recruitment_candidates_table.php` |
| | `2026_08_22_130000_add_driving_skill_and_joined_date_to_recruitment_candidates_table.php` |
| | `2026_09_15_000000_add_domain_to_branches_table.php` |

### Website Lead Routing (new + updated)

| File | Change |
|------|--------|
| `app/Services/BranchResolver.php` | **New** — resolves branch from domain, referer, or payload |
| `app/Http/Controllers/WebsiteLeadController.php` | **Updated** — uses `BranchResolver` |
| `app/Models/Branch.php` | **Updated** — added `code`, `domain` to fillable |

### Notification Bell (updated from server)

| File | Change |
|------|--------|
| `resources/views/layouts/app.blade.php` | Notification bell dropdown, polling JS, sidebar reorder |
| `app/Http/Controllers/LeadController.php` | `checkNotifications()` method |
| `app/Http/Controllers/SettingsController.php` | `updateNotificationSetting()` method |
| `resources/views/settings/index.blade.php` | Popup notification toggle in Settings |
| `routes/web.php` | Notification + recruitment routes |

### New Routes Added

```php
// Notification settings
POST /settings/notification-popup  → settings.updateNotification

// Notification polling (bell icon)
GET  /api/notifications/check      → notifications.check

// Recruitment (super_admin, lead_manager only)
GET|POST /recruitment/candidates/*
GET|POST /recruitment/departments/*
GET|POST /recruitment/positions/*
GET      /recruitment/reports
```

---

## Notification System — How It Works

1. Bell icon in the top bar polls `/api/notifications/check` every few seconds.
2. New leads trigger a toast popup and optional sound alert.
3. Unread count badge appears on the bell.
4. "Clear All" marks notifications as read (stored in browser `localStorage`).
5. Popup alerts can be disabled in **Settings → Real-Time Popup Notifications**.

---

## Local Setup After Sync

```bash
# 1. Copy env and fill in your local + VPS credentials
cp .env.example .env

# 2. Generate app key (if not set)
php artisan key:generate

# 3. Configure local DB in .env, then run migrations
php artisan migrate

# 4. Start local server
php artisan serve
```

Local migration count should now be **68** (matches production).

---

## Production Observations (No Action Taken)

| Item | Notes |
|------|-------|
| `APP_URL` | Set to IP instead of domain — affects generated URLs |
| No git on server | Deployments are manual copy; version tracking is harder |
| No Laravel scheduler cron | Scheduled tasks may not run unless configured elsewhere |
| Large log file | `storage/logs/laravel.log` ~24 MB — consider rotation |
| `bootstrap/cache` permissions | Owned by `root` with `777` — works but not ideal |
| Past log error | `Undefined variable $fieldstaff` in jobs index (Apr 2026) — monitor |
| SSL handshake errors in nginx log | External bot/scanner noise — harmless |
| GINOVA branch | No domain mapped yet — website leads fall back to default branch |

---

## Recommended Future Maintenance

These were **not** applied to production during audit (to avoid disrupting live users):

- [ ] Fix `APP_URL` to `https://crm.launchquests.com`
- [ ] Set up Laravel scheduler cron: `* * * * * php /var/www/html/cleaning-crm/artisan schedule:run`
- [ ] Configure log rotation for `laravel.log`
- [ ] Use non-root SSH user for deployments
- [ ] Initialize git on server or use a proper deploy pipeline
- [ ] Fix `$fieldstaff` undefined variable if still occurring
- [ ] Set domain for GINOVA branch if website leads are expected

---

## Sync Timeline

| Step | Description |
|------|-------------|
| 1 | Verified SSH access to Hostinger VPS |
| 2 | Read-only production health audit |
| 3 | Created local `.env` with VPS access config |
| 4 | Pulled 26 new files (Recruitment + BranchResolver + migrations) |
| 5 | Wired routes, Branch model, sidebar nav locally |
| 6 | Synced notification bell + related controllers/views/routes |
| 7 | Local repo now matches production (68 migrations, same file structure) |

**Production status after all operations:** ✅ Running normally (HTTP 200)
