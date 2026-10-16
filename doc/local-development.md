# Local Development Guide

This guide describes how to run the multi-organizer ticketing platform locally as an end-to-end working skeleton (Phase 1, Step 17).

---

## 1. System Architecture Overview

The local system comprises two repositories and shared backing services:

```text
  [Browser / Client]
         │
         ├─── Port 3000 (http://localhost:3000) ──────> [Next.js App (ticketfrontend)]
         │                                                      │
         │ (SPA Cookie Session & API Requests)                  │ (SSR Server Requests)
         │                                                      │
         └─── Port 8000 (http://localhost:8000) ──────> [Laravel API (ticketbackend)]
                                                                │
                                    ┌───────────────────────────┴───────────────────────────┐
                                    │                                                       │
                                [PostgreSQL]                                           [Background Tasks]
                         (Supabase Pooler SSL)                                  ┌───────────────────────────────┐
                                                                                │ - Queue Worker (queue:listen) │
                                                                                │ - Scheduler (schedule:work)   │
                                                                                │ - Daily Retention Purge (P1.5)│
                                                                                └───────────────────────────────┘
```

- **Backend (`ticketbackend`)**: Laravel 13 running on PHP 8.4 (Laravel Herd / CLI), serving `/api/v1` and Google OAuth web endpoints.
- **Frontend (`ticketfrontend`)**: Next.js 16 with Turbopack, App Router, and Tailwind CSS + shadcn/ui.
- **Database (`Supabase`)**: PostgreSQL 16 accessed via Supabase Session Connection Pooler (`aws-1-eu-central-1.pooler.supabase.com:5432`) over SSL (`sslmode=require`).
- **Auth**: Google OAuth 2.0 (Socialite) for platform admins, organizer staff, and event owners; password credentials for gate guards; Laravel Sanctum SPA session cookies for browser state.

---

## 2. Environment Variables Reference

> [!IMPORTANT]
> Never commit real credentials, database passwords, or secret keys to Git. All `.env` files are ignored in `.gitignore`.

### 2.1 Backend (`ticketbackend/.env`)

| Variable | Source / Description | Localhost Example | Tunnel Example |
| :--- | :--- | :--- | :--- |
| `APP_NAME` | Display name of the platform | `"Ticketa Platform"` | `"Ticketa Platform"` |
| `APP_ENV` | Environment type | `local` | `local` |
| `APP_KEY` | Laravel encryption key (`php artisan key:generate`) | `base64:...` | `base64:...` |
| `APP_DEBUG` | Enable debug backtraces | `true` | `true` |
| `APP_URL` | Base URL of the Laravel API | `http://localhost:8000` | `https://api.yourdomain.com` |
| `FRONTEND_URL` | Base URL of the Next.js frontend | `http://localhost:3000` | `https://app.yourdomain.com` |
| `DB_CONNECTION` | Database driver | `pgsql` | `pgsql` |
| `DB_HOST` | Supabase Pooler host (Project Settings > Database > Connection Pooling) | `aws-1-eu-central-1.pooler.supabase.com` | `aws-1-eu-central-1.pooler.supabase.com` |
| `DB_PORT` | Pooler port (Session mode) | `5432` | `5432` |
| `DB_DATABASE` | Database name | `postgres` | `postgres` |
| `DB_USERNAME` | Pooler user formatted as `postgres.<project_ref>` | `postgres.YOUR_PROJECT_REF` | `postgres.YOUR_PROJECT_REF` |
| `DB_PASSWORD` | Supabase project database password | `your_database_password` | `your_database_password` |
| `DB_SSLMODE` | Required SSL mode for Supabase pooler | `require` | `require` |
| `SUPABASE_DEV_PROJECT_REF` | Supabase development project reference | `YOUR_PROJECT_REF` | `YOUR_PROJECT_REF` |
| `SESSION_DRIVER` | Database-backed sessions for guards & Google users | `database` | `database` |
| `SESSION_LIFETIME` | Session duration in minutes | `120` | `120` |
| `SESSION_DOMAIN` | Wildcard domain for cookie sharing. Empty for localhost; `.domain.com` for tunnel/prod | *Empty* | `.yourdomain.com` |
| `SANCTUM_STATEFUL_DOMAINS` | Domains allowed to authenticate via SPA cookies | `localhost,localhost:3000,127.0.0.1:3000` | `app.yourdomain.com,api.yourdomain.com` |
| `CORS_ALLOWED_ORIGINS` | Permitted browser origins | `http://localhost:3000,http://127.0.0.1:3000` | `https://app.yourdomain.com` |
| `GOOGLE_CLIENT_ID` | Google Cloud Console OAuth 2.0 Client ID | `your-id.apps.googleusercontent.com` | `your-id.apps.googleusercontent.com` |
| `GOOGLE_CLIENT_SECRET` | Google Cloud Console OAuth 2.0 Client Secret | `your-google-client-secret` | `your-google-client-secret` |
| `GOOGLE_REDIRECT_URI` | Callback endpoint registered in Google Console | `http://localhost:8000/auth/google/callback` | `https://api.yourdomain.com/auth/google/callback` |
| `PESAPAL_ENV` | Gateway environment (`sandbox` or `production`) | `sandbox` | `sandbox` |
| `PESAPAL_CONSUMER_KEY` | Pesapal merchant key from developer portal | `your_consumer_key` | `your_consumer_key` |
| `PESAPAL_CONSUMER_SECRET` | Pesapal merchant secret | `your_consumer_secret` | `your_consumer_secret` |
| `PESAPAL_IPN_ID` | Registered IPN URL ID for payment webhooks | `your_ipn_id` | `your_ipn_id` |

### 2.2 Frontend (`ticketfrontend/.env`)

| Variable | Source / Description | Localhost Example | Tunnel Example |
| :--- | :--- | :--- | :--- |
| `NEXT_PUBLIC_APP_NAME` | Public brand title | `"Ticketa Platform"` | `"Ticketa Platform"` |
| `NEXT_PUBLIC_API_URL` | Base URL of the API used by the browser & SSR client | `http://localhost:8000` | `https://api.yourdomain.com` |
| `NEXT_PUBLIC_SITE_URL` | Base URL of the Next.js app | `http://localhost:3000` | `https://app.yourdomain.com` |
| `NEXT_PUBLIC_SUPABASE_URL` | Supabase API URL (`https://<project-ref>.supabase.co`) | `https://YOUR_PROJECT_REF.supabase.co` | `https://YOUR_PROJECT_REF.supabase.co` |
| `NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY` | Supabase new publishable API key | `sb_publishable_...` | `sb_publishable_...` |
| `NEXT_PUBLIC_SUPABASE_ANON_KEY` | Supabase legacy anonymous JWT key | `eyJhbGci...` | `eyJhbGci...` |
| `NEXT_PUBLIC_MOCK_API` | Toggles MSW OpenAPI mock mode (`true` or `false`) | `false` | `false` |

---

## 3. Starting the System

### 3.1 Backend Repository (`ticketbackend`)

A single command starts the API server, queue listener, and scheduler worker concurrently:

```bash
composer dev
```

*(Alternatively, run `php artisan dev`).*

This launches three concurrent background tasks managed by Laravel's dev process supervisor:
1. **API Server (`server`)**: `php artisan serve` listening on `http://127.0.0.1:8000`.
2. **Queue Worker (`queue`)**: `php artisan queue:listen --tries=1 --timeout=0` to process queued tasks (auditing, webhooks, notifications).
3. **Scheduler Worker (`scheduler`)**: `php artisan schedule:work` running scheduled tasks every minute, maintaining the `scheduler:last_run` heartbeat and dispatching the queue heartbeat.

### 3.2 Frontend Repository (`ticketfrontend`)

Start the Next.js development server with Turbopack:

```bash
pnpm dev
```

*(Or `npm run dev`).*

The app will be accessible at: `http://localhost:3000`.

---

## 4. System Health Check Endpoint

The API provides a health check endpoint at `/api/v1/health` that verifies database connectivity and reports the execution status of background infrastructure:

```bash
curl http://localhost:8000/api/v1/health
```

**Expected JSON Response (HTTP 200 OK):**

```json
{
  "status": "ok",
  "timestamp": "2026-10-16T14:20:00+00:00",
  "checks": {
    "database": "connected",
    "retention_job": {
      "run_at": "2026-10-16T00:00:00+00:00",
      "deleted": {
        "audit_logs": 0,
        "payment_webhook_events": 0,
        "reconciliation_runs": 0,
        "ticket_scans": 0,
        "outbox_messages": 0,
        "failed_jobs": 0
      },
      "total_deleted": 0
    },
    "scheduler": "2026-10-16T14:19:00+00:00",
    "queue": "2026-10-16T14:19:00+00:00"
  }
}
```

If the database is unreachable, the endpoint returns **HTTP 503 Service Unavailable** with `"status": "degraded"`.

---

## 5. Bootstrapping the First Platform Admin

Access to Ticketa is **strictly by invitation only** (Decision #7); there is no public self-registration or organizer application.

To create the very first platform admin, use the P1.4a bootstrap command:

```bash
php artisan platform:bootstrap-admin admin@example.com
```

### Flow:
1. The command validates that no platform admin exists yet in the database. (If an admin already exists, it terminates with an error per Decision #30).
2. It generates a single-use 64-character token with a 7-day expiration and prints the invitation URL:
   ```text
   Bootstrap invitation link for [admin@example.com]:
   http://localhost:3000/invite/4a7b9c...
   ```
3. Open this link in your browser.
4. Click **Sign in with Google to Accept**.
5. Sign in with the Google account for `admin@example.com`.
6. The invitation is accepted, granting `platform_role = admin`, and redirects to `/admin`.
7. All subsequent platform admins and organizers are invited through the authenticated admin dashboard.

---

## 6. Resetting the Development Database

To completely wipe and recreate the development schema from fresh migrations:

```bash
php artisan migrate:fresh
```

After wiping the database:
1. Re-run the bootstrap admin command:
   ```bash
   php artisan platform:bootstrap-admin your-google-email@example.com
   ```
2. Accept the invitation link in your browser to re-establish your platform admin session.

---

## 7. Cloudflare Tunnel (Two HTTPS Hostnames on One Domain)

### 7.1 Why Cloudflare Tunnel Was Chosen

For local development to accurately mirror production across subdomains (e.g. `app.yourdomain.com` and `api.yourdomain.com`), the browser must share session cookies across origins.

- **Wildcard Cookie Requirement**: Laravel Sanctum's cookie-based authentication requires `SESSION_DOMAIN=.yourdomain.com` so that the `laravel_session` cookie set by `api.yourdomain.com` is automatically sent with requests made from `app.yourdomain.com`.
- **The Public Suffix Problem with Free Tunnels**: Free services like ngrok (`*.ngrok-free.app`) or localtunnel (`*.localtunnel.me`) reside on public suffix lists. Modern browsers **block** setting cookies on public suffixes (`.ngrok-free.app`), causing session authentication to fail completely.
- **Cloudflare Tunnel (`cloudflared`)**:
  - Entirely free.
  - Supports multiple custom subdomains (`api.yourdomain.com` and `app.yourdomain.com`) on your own apex domain on a single tunnel.
  - Automatically provisions trusted SSL certificates.

### 7.2 Prerequisites Needed From You

To set up the tunnel, you need:
1. **A Domain**: An active domain name whose DNS nameservers are pointed to Cloudflare.
2. **A Free Cloudflare Account**: Access to Cloudflare Zero Trust (available on the free tier).
3. **The `cloudflared` CLI Tool**: Installed locally:
   - On Windows: `winget install --id Cloudflare.cloudflared` or `choco install cloudflared`
   - On macOS: `brew install cloudflared`
   - On Linux: `sudo apt install cloudflared`

### 7.3 Setup Instructions

1. **Log in to Cloudflare**:
   ```bash
   cloudflared tunnel login
   ```
   Select your domain in the opened browser window to authorize `cloudflared`.

2. **Create the Tunnel**:
   ```bash
   cloudflared tunnel create ticket-dev
   ```
   Note the generated Tunnel ID (UUID).

3. **Configure DNS Routes**:
   Route both subdomains to your tunnel:
   ```bash
   cloudflared tunnel route dns ticket-dev api.yourdomain.com
   cloudflared tunnel route dns ticket-dev app.yourdomain.com
   ```

4. **Create the Configuration File (`~/.cloudflared/config.yml`)**:
   ```yaml
   tunnel: <TUNNEL_UUID>
   credentials-file: C:\Users\<USERNAME>\.cloudflared\<TUNNEL_UUID>.json

   ingress:
     - hostname: api.yourdomain.com
       service: http://localhost:8000
     - hostname: app.yourdomain.com
       service: http://localhost:3000
     - service: http_status:404
   ```

5. **Run the Tunnel**:
   ```bash
   cloudflared tunnel run ticket-dev
   ```

### 7.4 Switching Between Localhost and Tunnel Mode

We provide pre-configured `.env.tunnel.example` files in both repositories:

1. **Backend**:
   - Copy `ticketbackend/.env.tunnel.example` into `ticketbackend/.env` (or update host values).
   - Key settings changed:
     ```ini
     APP_URL=https://api.yourdomain.com
     FRONTEND_URL=https://app.yourdomain.com
     SESSION_DOMAIN=.yourdomain.com
     SANCTUM_STATEFUL_DOMAINS="app.yourdomain.com,api.yourdomain.com"
     CORS_ALLOWED_ORIGINS="https://app.yourdomain.com"
     GOOGLE_REDIRECT_URI="https://api.yourdomain.com/auth/google/callback"
     ```
2. **Frontend**:
   - Copy `ticketfrontend/.env.tunnel.example` into `ticketfrontend/.env`.
   - Key settings changed:
     ```ini
     NEXT_PUBLIC_API_URL="https://api.yourdomain.com"
     NEXT_PUBLIC_SITE_URL="https://app.yourdomain.com"
     ```
3. **Google Cloud Console**:
   - Go to Google Cloud Console > **APIs & Services** > **Credentials**.
   - Edit your OAuth 2.0 Client ID.
   - Under **Authorized redirect URIs**, ensure both URIs are present:
     - `http://localhost:8000/auth/google/callback` (Localhost mode)
     - `https://api.yourdomain.com/auth/google/callback` (Tunnel mode)
   - Under **Authorized JavaScript origins**, add:
     - `http://localhost:3000`
     - `https://app.yourdomain.com`
