# Dependency log

Every runtime, package and service this codebase depends on, and what each one is for.

**Keep it current.** Any change that adds, removes or upgrades a dependency updates this file in the same commit and adds a line to the [changelog](#7-changelog).

**Versions.** *Declared* is the constraint in `composer.json` or `package.json`. *Locked* is the version that `composer.lock` or `pnpm-lock.yaml` actually installed.

Last checked: 2026-10-16.

---

## 1. Runtimes and tools

| Tool | Version | Used for |
| --- | --- | --- |
| PHP | ^8.3 declared · 8.4.21 local (Laravel Herd) | Runs the Laravel API |
| Composer | 2.9.5 | PHP package manager for `ticketbackend` |
| Node.js | 24.14.1 | Runs the Next.js dev server, production build and frontend tests |
| pnpm | 12.11.2 (pinned in `packageManager`) | Package manager for `ticketfrontend` |
| PostgreSQL | 16 (local container & CI service container) · Supabase (remote) | The database. Tests run against PostgreSQL, never SQLite (CLAUDE.md rule 4) |
| Supabase Agent Skills | `supabase`, `supabase-postgres-best-practices` | Postgres best practices and Supabase workflow guidelines installed in `.agents/skills` |

## 2. Infrastructure and external services

| Service | Version / source | Used for |
| --- | --- | --- |
| `postgres` container | `postgres:16-alpine` in `docker-compose.yml` & CI workflow | Local database `ticket_platform` and isolated CI test runner service container (`ticket_platform_test`) |
| Supabase | Hosted PostgreSQL 16 (`aws-1-eu-central-1.pooler.supabase.com:5432`, ref: `qglaepourqlklgsfvkai`) | Development and production database via session pooler with `sslmode=require` |
| Supabase MCP Server | `https://mcp.supabase.com/mcp` | IDE MCP server for remote schema inspection, logs, migrations, and keys |
| GitHub Actions | `ubuntu-latest` | CI workflows for backend (Pint, Larastan, Pest) and frontend (lint, type-check, build, test) |
| Pesapal API 3.0 | External API | M-Pesa and card payments: hosted checkout, IPN notifications, transaction status checks |
| Google OAuth | External API | Sign-in for admins, organizer staff and event owners |
| cPanel | Hosting | Runs the API; cron drives the scheduler and queue |
| Vercel | Hosting | Runs the Next.js frontend |

---

## 3. Backend: `ticketbackend` (Composer)

### Production (`require`)

| Package | Declared → locked | What it's for |
| --- | --- | --- |
| `laravel/framework` | ^13.17 → v13.36.0 | The Laravel framework: routing, Eloquent ORM, migrations, validation, policies, queues, scheduler and mail. It also brings in `guzzlehttp/guzzle` 8.2.0, the HTTP client behind Laravel's `Http` facade that we'll use to call Pesapal, and `nesbot/carbon` 3.14.2 for dates and times |
| `laravel/sanctum` | ^4.0 → v4.3.3 | Cookie-based SPA session authentication for frontend requests (Next.js browser and SSR requests) |
| `laravel/socialite` | ^5.31 → v5.31.0 | Google OAuth 2.0 social sign-in provider |
| `laravel/tinker` | ^3.0 → v3.0.2 | Interactive console (`php artisan tinker`) for inspecting models and data during development |

### Development (`require-dev`)

| Package | Declared → locked | What it's for |
| --- | --- | --- |
| `pestphp/pest` | ^4.7 → v4.7.8 | The test framework the suite is written in (`php artisan test`) |
| `pestphp/pest-plugin-laravel` | ^4.1 → v4.1.0 | Laravel helpers for Pest: HTTP test requests, `actingAs`, database assertions |
| `phpunit/phpunit` | ^12.5.12 → 12.5.33 | The test engine Pest runs on |
| `mockery/mockery` | ^1.6 → 1.6.15 | Mock objects in tests, for example to stand in for the Pesapal client |
| `fakerphp/faker` | ^1.23 → v1.24.1 | Fake names, emails and other data for model factories and seeders |
| `larastan/larastan` | ^3.13 → v3.13.0 | Static analysis: PHPStan with Laravel support, configured in `phpstan.neon`. This is the backend type check |
| `laravel/pint` | ^1.27 → v1.32.1 | Code formatter and style linter |
| `symfony/yaml` | ^8.1 → v8.1.9 | YAML parser used by tests to parse and validate openapi.yaml against registered routes |
| `nunomaduro/collision` | ^8.6 → v8.9.5 | Readable error output in the console and test runner |
| `laravel/pail` | ^1.2.5 → v1.2.7 | Follows the application log from the terminal (`php artisan pail`) |
| `laravel/pao` | ^1.0.6 → v1.1.5 | Compact, agent-friendly output for the test and analysis tools. Ships with the Laravel 13 skeleton |

### Node packages (`package.json`): skeleton defaults, not installed

The Laravel skeleton includes these packages for building Blade views. The API serves no HTML, because every screen lives in `ticketfrontend`. `node_modules` has never been installed here. See [§6](#6-cleanup-candidates).

| Package | Declared | What it's for |
| --- | --- | --- |
| `vite` | ^8.0.0 | Bundles CSS and JS for Blade views |
| `laravel-vite-plugin` | ^3.1 | Connects Vite to Laravel |
| `tailwindcss` | ^4.0.0 | Styles the default welcome page |
| `@tailwindcss/vite` | ^4.0.0 | Tailwind plugin for Vite |
| `concurrently` | ^10.0.3 | Runs several dev processes in one terminal |
| `@laravel/multiplex` (optional) | ^0.4.1 | Used by `php artisan dev` to start the server, queue, log viewer and Vite together |

---

## 4. Frontend: `ticketfrontend` (pnpm)

### `dependencies`

| Package | Declared → locked | What it's for |
| --- | --- | --- |
| `next` | 16.4.1 (exact) | The React framework: App Router, server-side rendering and the routes for every role area |
| `react` | 19.3.0 | The UI library |
| `react-dom` | 19.3.0 | Renders React to the browser DOM |
| `@base-ui/react` | ^1.9.0 → 1.9.0 | Headless, accessible primitives underneath shadcn's `base-nova` style |
| `react-hook-form` | ^7.90.0 → 7.90.0 | Performant, flexible forms state and validation management |
| `zod` | ^4.6.7 → 4.6.7 | TypeScript-first schema validation |
| `@hookform/resolvers` | ^5.9.1 → 5.9.1 | Zod validation resolver for React Hook Form |
| `sonner` | ^2.0.8 → 2.0.8 | Toast notifications component |
| `next-themes` | ^0.4.6 → 0.4.6 | Light/dark theme management |
| `class-variance-authority` | ^0.7.1 → 0.7.1 | Defines component variants such as size and colour |
| `clsx` | ^2.1.1 → 2.1.1 | Builds `className` strings conditionally. Used by `cn()` in `src/lib/utils.ts` |
| `tailwind-merge` | ^3.7.0 → 3.7.0 | Resolves conflicting Tailwind classes. Used by `cn()` in `src/lib/utils.ts` |
| `lucide-react` | ^1.57.0 → 1.57.0 | Icon set, configured as shadcn's icon library in `components.json` |
| `shadcn` | ^4.21.4 → 4.21.4 | The shadcn/ui CLI, which copies component source into `src/components/ui` |
| `tw-animate-css` | ^1.4.0 → 1.4.0 | Tailwind v4 enter and exit animations that shadcn components use |
| `cn` | ^0.4.0 → 0.4.0 | A class-name merge utility. **Not imported anywhere**: it duplicates our own `cn()` helper. See [§6](#6-cleanup-candidates) |

### `devDependencies`

| Package | Declared → locked | What it's for |
| --- | --- | --- |
| `typescript` | ^5 → 5.9.3 | Type checking (`pnpm type-check`) |
| `@types/node` | ^20 → 20.19.43 | Type definitions for Node.js |
| `@types/react` | ^19 → 19.3.0 | Type definitions for React |
| `@types/react-dom` | ^19 → 19.3.0 | Type definitions for React DOM |
| `tailwindcss` | ^4 → 4.3.3 | Utility-first CSS framework |
| `@tailwindcss/turbopack` | ^4 → 4.3.3 | Lets Next.js's Turbopack bundler process Tailwind |
| `eslint` | ^9 → 9.39.5 | Linter (`pnpm lint`) |
| `eslint-config-next` | 16.4.1 | Next.js lint rules |
| `vitest` | ^5.0.4 → 5.0.4 | Unit and component test runner (`pnpm test`) |
| `@vitejs/plugin-react` | ^6.1.2 → 6.1.2 | Lets Vitest compile JSX and TSX |
| `jsdom` | ^30.1.2 → 30.1.2 | Simulated browser DOM for component tests |
| `@testing-library/react` | ^16.3.3 → 16.3.3 | Renders components in tests and queries them the way a user would |
| `@testing-library/jest-dom` | ^7.0.1 → 7.0.1 | DOM assertions such as `toBeInTheDocument` |
| `openapi-typescript` | ^7.13.0 → 7.13.0 | Generates TypeScript definitions (`src/lib/api-schema.ts`) from `openapi.yaml` |
| `msw` | ^3.0.3 → 3.0.3 | Mock Service Worker for client and server mock API mode (`src/mocks/`) |

### shadcn/ui components

These are not packages. The shadcn CLI copies each component's source into the repository, where we own it.

| Component | File |
| --- | --- |
| Button | `src/components/ui/button.tsx` |
| Input | `src/components/ui/input.tsx` |
| Label | `src/components/ui/label.tsx` |
| Form | `src/components/ui/form.tsx` |
| Card | `src/components/ui/card.tsx` |
| Dialog | `src/components/ui/dialog.tsx` |
| Table | `src/components/ui/table.tsx` |
| Toast & Sonner | `src/components/ui/toast.tsx`, `src/components/ui/sonner.tsx` |
| Dropdown Menu | `src/components/ui/dropdown-menu.tsx` |
| Badge | `src/components/ui/badge.tsx` |
| Select | `src/components/ui/select.tsx` |
| Tabs | `src/components/ui/tabs.tsx` |

---

## 5. Planned: named in the specification, not installed yet

| Package or tool | Repo | Purpose | When |
| --- | --- | --- | --- |
| k6 | tooling | Concurrent-request tests (no overselling, no double redemption) and performance tests | Phase 3 and Phase 8 (implementation-plan.md §12) |
| Playwright | frontend | End-to-end tests in a real browser | Phase 5 (implementation-plan.md §12) |
| `intervention/image` | backend | Decodes, resizes and re-encodes event images as JPEGs under 500 KB, on PHP's GD extension | Phase 2, P2.1 (decision #46) |
| `league/flysystem-aws-s3-v3` | backend | Laravel's `s3` disk, pointed at Supabase Storage's S3-compatible endpoint, for event images | Phase 2, P2.1 (decision #46) |
| Supabase Storage | infrastructure | Public bucket for event images; Vercel's image optimizer resizes them for each screen | Phase 2, P2.1 and P2.7 (decision #46) |

Packages for QR code images, guest-list export and the guard scanner's camera haven't been chosen. They'll be logged here when they're added.

---

## 6. Cleanup candidates

Nothing here has been changed. Each item needs a decision first.

1. **`cn` (frontend).** It's never imported, and `src/lib/utils.ts` already defines `cn()` using `clsx` and `tailwind-merge`. Remove it with `pnpm remove cn`.
2. **Backend Node packages.** An API-only backend doesn't use them. Removing them means deleting `package.json`, `vite.config.js`, `resources/css`, `resources/js`, the welcome view, and the `npm install` and `npm run build` steps in the `composer setup` script.
3. **`tw-animate-css` (frontend).** It's installed but not imported. Add `@import "tw-animate-css";` to `globals.css` when the first animated component (dialog, dropdown) is added.
4. **`shadcn` (frontend).** The CLI is only needed while adding components, so it could move to `devDependencies`. It should stay in `dependencies` if `globals.css` later imports `shadcn/tailwind.css`.

### CI Actions (GitHub Actions)

| Action | Version | Used for |
| --- | --- | --- |
| `actions/checkout` | v4 | Checks out the repository in CI runner |
| `shivammathur/setup-php` | v2 | Installs PHP 8.3 with required PostgreSQL extensions in backend CI |
| `actions/cache` | v4 | Caches Composer dependencies across CI runs |
| `pnpm/action-setup` | v4 | Installs pinned `pnpm` (12.11.2) in frontend CI |
| `actions/setup-node` | v4 | Installs Node.js 20 with pnpm cache in frontend CI |

---

## 7. Changelog

| Date | Change |
| --- | --- |
| 2026-10-16 | Completed Phase 1 Step 17 working skeleton (P1.8): configured `/api/v1/health` verifying database connectivity, scheduler heartbeat, queue heartbeat, and retention purge reporting; added concurrent background process supervisors for backend (`composer dev` starting API server, queue listener, and scheduler) and frontend (`pnpm dev`); documented Cloudflare Tunnel architecture for two HTTPS hostnames sharing parent cookie domain, environment switching templates (`.env.tunnel.example`), platform admin bootstrap flow (`platform:bootstrap-admin`), and database reset procedures in `doc/local-development.md` |
| 2026-10-16 | Implemented frontend foundation (implementation-plan.md Phase 1 steps 14–16, P1.7): added 12 shadcn/ui components (`button`, `input`, `label`, `form`, `card`, `dialog`, `table`, `toast`/`sonner`, `dropdown-menu`, `badge`, `select`, `tabs`); typed API client generated from `openapi.yaml` with server cookie/Origin forwarding and browser CSRF `/sanctum/csrf-cookie` handling; OpenAPI-driven MSW mock mode for browser and SSR; public site layout, header, footer, home page, Google sign-in, guard email/password sign-in with first-sign-in password change screen, invitation acceptance flow (`/invite/[token]`), `error=no_access` message (Decision #7), logout (`/logout`); role-protected app shell with sidebar and multi-organizer switcher (`/admin`, `/organizer`, `/event-owner`, `/guard`); component tests for all auth, guard, password change, invitation, and no-access flows. Added dependencies: `react-hook-form`, `zod`, `@hookform/resolvers`, `sonner`, `openapi-typescript`, `msw` |
| 2026-10-16 | Added OpenAPI 3.1.0 contract (`/api/docs/openapi.yaml` and `ticketbackend/docs/openapi.yaml`) covering all MVP endpoints (v1.md §5); implemented unified JSON error format (`ApiResponse::error`), standard pagination envelope (`ApiResponse::paginated`), request correlation ID (`CorrelationIdMiddleware` accepting/returning `X-Request-Id` and logging safely without PII per Decisions #35, #36); implemented guest order checkout token protection (Hurdle H11); added `HealthController` (`/api/v1/health`); added `OpenApiContractTest` and `ApiConventionsTest` (P1.6) |
| 2026-10-16 | Added audit retention database role migration (`audit_retention`), retention purge service and daily scheduler (`platform:purge-retention`) for logs past retention periods (Decision #35); hardened AuditLogger against buyer/attendee PII (Decision #36); implemented multi-organizer tenancy, model policies, and authorization safeguards (P1.5) |
| 2026-10-16 | Planned `intervention/image`, `league/flysystem-aws-s3-v3` and Supabase Storage for event images (decision #46) |
| 2026-10-16 | Added `laravel/socialite` (v5.31.0) and `laravel/sanctum` (v4.3.3) for Google OAuth 2.0 social sign-in and SPA cookie sessions |
| 2026-10-15 | Added GitHub Actions CI workflows, Supabase project pooler details (`qglaepourqlklgsfvkai`), Supabase MCP server, and Supabase agent skills (`supabase`, `supabase-postgres-best-practices`) |
| 2026-10-15 | Log created from `composer.json`, `composer.lock`, both `package.json` files, `pnpm-lock.yaml` and `docker-compose.yml` |


