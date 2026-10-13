# AI Build Prompts: Event Ticketing Platform

Ready-to-paste prompts for an AI coding assistant (such as Claude Code or Cursor) to build the platform in the order set out in [implementation-plan.md](implementation-plan.md).

## How to use these prompts

1. **Finish Phase 0's human tasks first.** Settle the decision log (implementation-plan.md §11), submit the Pesapal merchant application and run the hosting check. Several prompts depend on those answers.
2. **Give the assistant the project context.** Paste [the context block](#project-context) at the start of every new session. Alternatively, save it in your tool's project instructions file (`CLAUDE.md` for Claude Code) so it loads automatically.
3. **Run one prompt at a time, in order.** Review the changes and the test results. Don't move on until the prompt's **Done when** checks pass.
4. **Commit after every prompt**, so any step can be undone on its own.
5. **Run the [phase exit check](#phase-exit-check) at the end of every phase** before starting the next.

Prompt IDs follow the plan: **P3.2** is Phase 3, prompt 2.

---

## Project context

Paste this once per session, or save it as `CLAUDE.md`.

```text
You are building a multi-organizer event ticketing platform. Organizers create events and sell tickets through M-Pesa and card payments (both through Pesapal); buyers receive QR tickets by email; guards scan tickets at event gates.

SOURCE DOCUMENTS (they are the specification — read the relevant parts before each task):
- implementation-plan.md — phases, steps, exit criteria and the decision log (§11). Follow it.
- doc/baseline-spec.md — final names, tables, roles and state machines (created in P0.1). Wins over older documents.
- v1.md — requirements, permissions matrix (§3), schema (§4), state models (§4.6), API (§5).
- techstack.md — architecture and stack.
- sow.md — contractual scope. Never build anything listed in §9 "Out of scope".

STACK:
- /api — Laravel (latest stable release), Sanctum SPA cookie authentication, PostgreSQL on Supabase.
- /web — Next.js + TypeScript: public event site, checkout, ticket retrieval.
- /manage — React + Vite + TypeScript: organizer dashboard, platform admin, guard scanner.
- /packages/ui — shared shadcn/ui components and theme, Tailwind CSS.
- Payments — Pesapal API 3.0: buyers pay by M-Pesa or card on Pesapal's hosted checkout; Pesapal sends IPN notifications; results are confirmed with Pesapal's transaction status endpoint.
- API hosted on cPanel: scheduler and queue run from cron.

NON-NEGOTIABLE RULES:
1. Tenant isolation: every organizer-owned resource is authorized through a Laravel policy that checks the authenticated user's organizer membership. Never trust role, organizer_id, event_id or gate_id from the request without checking it.
2. Money is stored as integer minor units plus a currency code. Never floats.
3. Inventory holds, payment confirmation, ticket issuance and ticket redemption use database transactions and atomic conditional updates. Never read-then-write checks.
4. Tests run against PostgreSQL, never SQLite.
5. Every new resource gets a tenant-isolation test.
6. Sensitive actions write to the audit log.
7. Never store raw card data, plaintext QR tokens or plaintext retrieval tokens. Never commit secrets.
8. The backend calculates all prices and decides all statuses. Never trust values from the browser or a payment redirect.

HOW TO WORK:
- Before coding, restate the task as a short plan and name the files you'll change.
- If the task depends on a decision that is still open in implementation-plan.md §11, stop and ask me.
- Stay within the scope of the current prompt. Note anything else you notice, but don't build it.
- Keep the OpenAPI contract (/api/docs/openapi.yaml) up to date whenever endpoints change.
- When finished: run the full test suite, linters and type checks; show the results; summarize what changed; list any deviation from the specification and why.
```

---

## Phase exit check

Run this at the end of every phase.

```text
Check Phase <N> of implementation-plan.md against the current code.
For each exit criterion, report: PASS, FAIL or NOT TESTED; the test or file that proves it; and what's missing.
Then run the full test suite and report the results.
Don't fix anything yet — just report.
```

---

## Phase 0 — Discovery and specification

### P0.1 — Baseline specification

```text
Read v1.md, v2.md, techstack.md, sow.md and implementation-plan.md. Create doc/baseline-spec.md as the single source of truth for implementation. Don't write code yet.

Include:
1. A naming table that resolves every conflict listed in implementation-plan.md Phase 0 step 1. Use the recommendation there unless §11 records a different decision.
2. The final list of tables with their columns: v1.md §4 plus the gaps listed in implementation-plan.md Phase 1 step 1 (reserved count on offerings, QR token storage that allows the QR to be shown again, seat hold expiry, owner/staff role values).
3. The final roles and what each may do, based on v1.md §3 with the organizer owner/staff split.
4. State machines for orders, payments and tickets, listing every allowed transition, including the states v1.md mentions only in prose (cancelled, expired, refunded, partially refunded, revoked).
5. A list of anything still ambiguous, written as questions for me.
```

**Done when:** the naming, tables, roles and state machines are written down, and you've answered the open questions.

### P0.2 — Hosting check script

```text
Write a single-file PHP script, tools/hosting-check.php, that I can upload to the cPanel host to check the requirements in implementation-plan.md Phase 0 step 4.

It must check and report as a PASS/FAIL table:
- PHP version against the minimum for the latest stable Laravel release (state which version you're checking against).
- Extensions: pdo_pgsql, mbstring, openssl, bcmath, intl, fileinfo, gd.
- Whether functions Laravel's scheduler and queue rely on (such as proc_open) are disabled.
- Whether the storage and cache directories would be writable.
- A PostgreSQL connection to Supabase with SSL required, trying both the direct host and the pooler host, reporting which works, the connection time, and whether the server has IPv6.

Security: read credentials from environment variables or a POST form, never print them, require a secret token in the URL to run at all, and show a warning to delete the file after use.
```

**Done when:** the script runs on the host and the go/no-go result is recorded in implementation-plan.md's hosting check.

---

## Phase 1 — Design and foundation

### P1.1 — Repository scaffold

```text
Set up the repository as described in implementation-plan.md Phase 1 step 6:
- /api: a new Laravel project (latest stable) configured for PostgreSQL, with Pest for tests.
- /web: Next.js with TypeScript, the App Router and Tailwind CSS.
- /manage: React + Vite + TypeScript, Tailwind CSS and a router.
- /packages/ui: a shared shadcn/ui setup (use the shadcn CLI's monorepo support) with the theme defined as CSS variables, used by both /web and /manage.
- pnpm workspaces for the JavaScript packages.
- docker-compose.yml with PostgreSQL for local development and tests.
- Code quality: Laravel Pint and Larastan for /api; ESLint and strict TypeScript for /web and /manage.
- A root README explaining how to run each app, plus .env.example files with no real secrets.

Check that every app starts and its default tests pass.
```

**Done when:** all three apps run locally and their default tests pass.

### P1.2 — Continuous integration

```text
Add a CI workflow (GitHub Actions unless I say otherwise). On every push and pull request:
- /api: install dependencies, run the Pint check, run Larastan, and run the tests against a PostgreSQL service container.
- /web and /manage: install, lint, type-check, build and run unit tests.
Any failure must fail the build.
```

**Done when:** a test pull request shows every check passing, and a deliberately broken test makes the build fail.

### P1.3 — Database schema and migrations

```text
Using doc/baseline-spec.md, write Laravel migrations for the full MVP schema, including seating, so that later phases need no structural rework.

Requirements:
- UUID primary keys for records referenced from outside the system.
- A foreign key for every ownership relationship. Choose delete behavior deliberately: never cascade-delete orders, payments, tickets or audit logs.
- Unique constraints: user email; event slug per organizer; ticket_number; qr_token_hash; (provider, provider_reference) on payments, where the reference is Pesapal's order tracking ID; idempotency_key; and one active ticket per seat per event (a partial unique index). Pesapal notifications carry no unique event ID, so don't add a unique key on webhook events; idempotency comes from the payment and order checks in P4.2.
- CHECK constraints where they're cheap: non-negative counts and amounts, and capacity - sold_count - reserved_count >= 0.
- Money as bigint minor units plus a char(3) currency column.
- Status columns as strings backed by PHP enums, one enum per state machine in the baseline spec.

Also create Eloquent models with relationships and enum casts, a factory for every model, and a Mermaid ERD of the final schema at doc/diagrams/erd.mmd.
Run the migrations from scratch against local PostgreSQL and show the output.
```

**Done when:** `migrate:fresh` succeeds, every model has a factory, and the ERD matches the baseline spec.

### P1.4 — Authentication

```text
Implement authentication in /api as described in implementation-plan.md Phase 1 step 9, using Sanctum SPA cookie authentication rather than long-lived browser tokens.

- Endpoints under /api/v1/auth: register, login, logout, me, email verification, forgot password and reset password.
- Suspended users can't log in, and suspending a user ends their existing sessions.
- Configure stateful domains, the session cookie domain, CORS (with credentials) and CSRF for the www, manage and api subdomains through environment variables. Document the values to use for local development.
- Rate-limit login, registration and password reset.
- Feature tests for every endpoint, including a suspended user and a wrong password.
```

**Done when:** all auth tests pass, and a browser on another local port can log in through the CSRF-cookie flow.

### P1.5 — Tenancy, roles, policies and audit log

```text
Implement tenancy and authorization as described in implementation-plan.md Phase 1 steps 10, 11 and 13.

- Organizers, and organizer members with the roles owner and staff. Platform admin is a separate role from organizer roles.
- A policy for every organizer-owned model. It checks that the resource's organizer (directly, or through its event) is one the user belongs to, with a role that permits the action. Platform admins pass only where v1.md §3 allows it, and their cross-organizer actions are audited.
- A safeguard so no route can skip authorization, for example a test that fails if any /api/v1 route has no policy check.
- An AuditLogger service recording the actor, organizer, event, action, resource type and ID, metadata and time.
- Endpoints: create an organizer, list my organizers, invite a member, change a member's role, remove a member.
- A reusable tenant-isolation test helper: for a given endpoint, it asserts that a user from a different organizer is refused on read, update and delete. Apply it to every endpoint built so far.
```

**Done when:** the tenant-isolation tests pass for every endpoint, and the route-coverage safeguard passes.

### P1.6 — API contract and conventions

```text
Write /api/docs/openapi.yaml covering every MVP endpoint, using the endpoints in v1.md §5 and techstack.md §7, the naming in doc/baseline-spec.md and the /api/v1 prefix. Include request and response schemas for each.

Then implement the conventions in /api:
- One JSON error format for validation, authentication, authorization, not found, conflict, rate limit and server errors.
- One pagination format with a standard response envelope.
- A request correlation ID: accept X-Request-Id or generate one, return it in the response, and include it in every log line.

Add a test that checks the implemented endpoints match the contract (route list against the OpenAPI paths).
```

**Done when:** the contract covers every MVP endpoint, and the error format, pagination and correlation ID are implemented and tested.

### P1.7 — Frontend foundation

```text
Build the frontend foundation described in implementation-plan.md Phase 1 steps 14–16.

- In /packages/ui, add the shadcn/ui components we'll need first: button, input, label, form, card, dialog, table, toast, dropdown menu, badge, select and tabs.
- A typed API client generated from openapi.yaml. It sends cookies, calls /sanctum/csrf-cookie before any state-changing request, and turns the standard error format into typed errors.
- A mock mode driven by the OpenAPI spec (for example with MSW), so screens can be built before their endpoints exist.
- /manage: login, forgot password, reset password, email verification and logout. Route users by role to /organizer, /admin and /guard areas, inside an app shell with a sidebar, a header and an organizer switcher for users who belong to more than one organizer.
- /web: the site layout, header, footer and a placeholder home page.
- Component tests for the auth forms.
```

**Done when:** you can log in to /manage against the real local API and in mock mode, and you land in the right area for your role.

### P1.8 — Deploy the working skeleton

```text
Prepare the staging deployment described in implementation-plan.md Phase 1 step 17.

- /api: a deploy script, or documented steps, for cPanel: update the code, composer install --no-dev, migrate --force, cache config, routes and views. The document root points to /api/public, and the .env file sits outside the web root.
- Cron: a single entry runs `php artisan schedule:run` every minute. Unless the hosting check showed that long-running processes are allowed, the scheduler processes the database queue with `queue:work --stop-when-empty`, without overlapping runs.
- A /api/v1/health endpoint that checks the database connection and reports when the scheduler and queue last ran.
- Frontend deployment configuration that follows the hosting decision in implementation-plan.md §11 #5.
- doc/deployment.md listing every step and every environment variable.
No real credentials in any file.
```

**Done when:** staging serves all three apps over HTTPS, login works across the subdomains, and the health endpoint shows the scheduler running.

**Then run the [phase exit check](#phase-exit-check) for Phase 1.**

---

## Phase 2 — Events and ticket configuration

### P2.1 — Event lifecycle and details

```text
Implement events in /api as described in implementation-plan.md Phase 2 steps 1–3.

- Create, update, list and show events for organizers; public list and show return published events only.
- States: draft → published → cancelled or archived, with transitions enforced by an enum and a single service. Every transition is audited.
- Name, slug (unique per organizer), description, venue, timezone (an IANA name) and images stored in Supabase Storage with validated type and size.
- Separate time windows, each validated: sales start and end, gate opening and closing, event start and end. Store times in UTC and return them together with the event's timezone.
- Policies, tenant-isolation tests and feature tests for every rule.
```

**Done when:** the lifecycle and time-window rules are tested, and the public endpoints never return draft events.

### P2.2 — Ticket types, categories, offerings and delegations

```text
Implement ticket configuration as described in implementation-plan.md Phase 2 steps 4–6.

- Ticket types and categories for each event. Seed the defaults (Early Bird, Normal, At the Gate, Group, Promoted; VVIP, VIP, Media) and let organizers add, rename and disable their own.
- Offerings: each allowed type × category combination, with a price in minor units, a currency, capacity, a sales window, a minimum and maximum per order, and a status. Group types define their required quantity. Media can be flagged "approval required".
- Delegations: name, allocation, allowed categories, price rule and status.
- Validation: the total capacity of all offerings can't exceed the event's capacity, and an offering's capacity can't drop below what has been sold plus what is held.
- Policies, tenant-isolation tests and feature tests.
```

**Done when:** offerings and delegations can be created and edited within their capacity rules, and every rule is tested.

### P2.3 — Attendee information setup

```text
Implement attendee information settings for each event, as described in implementation-plan.md Phase 2 step 7.

- The collection mode: purchaser details only, or details for every ticket.
- Field definitions: key, label, type (text, email, phone, select, checkbox, date), whether it's required, options for select fields, display order, and whether it applies to the purchaser or each ticket.
- Full name and email are always collected from the purchaser.
- A reusable validator that, given an event, validates submitted purchaser and attendee data against the event's setup. Phase 3 will use it.
- Tests for both collection modes and every field type.
```

**Done when:** the validator accepts valid data and rejects invalid data in both collection modes.

### P2.4 — Gates, staff invitations and guard assignments

```text
Implement gates and staff as described in implementation-plan.md Phase 2 step 8.

- Gates for each event, with a name, opening and closing times, and a status.
- Guard invitations by email, which work for both new and existing users. Invitations expire and can only be used once.
- Assignments link a guard to an event and, optionally, a gate. Organizers can remove assignments. Assignment changes are audited.
- An endpoint for a guard to list only their current assignments.
- Tests: a guard sees only their own assignments, can't reach any organizer endpoint, and an expired or reused invitation fails.
```

**Done when:** the invitation-to-assignment flow works end to end, and guards are confined to their assignments.

### P2.5 — Publishing rules and admin controls

```text
Implement publishing rules and admin controls as described in implementation-plan.md Phase 2 steps 9–10.

- An event can be published only when it has at least one active offering, valid and consistent time windows, and capacity that covers all allocations. If not, return every failing rule at once.
- After publishing: audit every change, and block or require explicit confirmation for changes that affect existing orders (lowering capacity below sold, changing the price of an offering that has sales, moving the event's dates).
- Admin endpoints: list organizers, suspend and reactivate organizers (suspension unpublishes nothing but blocks new sales and logins), and view any event, with the access audited.
- Tests for each rule.
```

**Done when:** publishing refuses incomplete events with clear reasons, and suspended organizers can't sell.

### P2.6 — Organizer and admin dashboards

```text
Build the organizer and admin screens in /manage for Phase 2, using shadcn/ui components from /packages/ui.

Organizer:
- Event list with status badges and filters.
- A step-by-step event editor: details → times → tickets (an offerings grid of type × category) → attendee fields → gates and staff → review and publish. The publish step shows the API's failing rules if there are any.
- Delegation management, and staff invitation and assignment.

Admin:
- Organizer list with suspend and reactivate actions behind a confirmation dialog.

Forms validate in the browser for convenience, but always show the API's validation errors. Add component tests for the editor steps.
```

**Done when:** an organizer can create and publish an event entirely through the user interface.

### P2.7 — Public event pages

```text
Build the public event pages in /web:
- An event listing page showing published events, with search by name and a date filter.
- An event detail page with the image, description, venue, date and time in the event's timezone, and the available offerings with prices and "sold out" or "sales not open yet" states.
- Server-side rendering with sensible caching, plus SEO metadata (title, description, Open Graph tags).
- Accessible markup: keyboard navigation and correct heading order.
No checkout yet; the "Buy" button can be a placeholder.
```

**Done when:** published events appear with the correct times and states, and draft events don't appear.

**Then run the [phase exit check](#phase-exit-check) for Phase 2.**

---

## Phase 3 — Checkout, orders and inventory

### P3.1 — Orders and inventory holds

```text
Implement order creation with inventory holds as described in implementation-plan.md Phase 3 steps 1–2.

- POST /api/v1/orders (guest checkout allowed) validates the offering, the sales window, quantity limits, group rules and delegation eligibility.
- Hold the inventory inside one database transaction, using this atomic conditional update for each offering:
    UPDATE event_ticket_offerings
    SET reserved_count = reserved_count + :qty
    WHERE id = :id AND status = 'active'
      AND capacity - sold_count - reserved_count >= :qty;
  If 0 rows are updated, roll back and return a "sold out" conflict response. On success, insert inventory_reservations rows with expires_at.
- Build the hold mechanism generically (offering holds now, seat holds in Phase 7).
- Create the order as pending, with the purchaser's details.
- The hold duration comes from config. Use the value in implementation-plan.md §11 #10, or ask me if it's still open.
```

**Done when:** an order holds its inventory, and sold-out requests are refused cleanly.

### P3.2 — Overselling test

```text
Write a concurrency test proving that inventory can't be oversold, as described in implementation-plan.md Phase 3 step 10.

- Against PostgreSQL, create an offering with 10 tickets left, then fire 50 order requests at the same moment using real parallel processes or connections, not a sequential loop.
- Assert that exactly 10 tickets are held, 40 requests get "sold out", and reserved_count + sold_count never exceeds capacity.
- Include a variant with mixed quantities (1–4 tickets per request).
- Add it to CI. If it can't run reliably in CI, add a separate CI job for it.
Show me the test output.
```

**Done when:** the test passes repeatedly, and it fails if the atomic update is replaced with a read-then-write check (try this temporarily and show me).

### P3.3 — Hold expiry

```text
Implement hold expiry as described in implementation-plan.md Phase 3 step 3.
- A scheduled job runs every minute. It finds expired active reservations, releases them (decrementing reserved_count in the same transaction) and marks their pending orders as expired.
- It must be safe to run concurrently and to run twice, so a reservation is never released twice.
- An endpoint that returns an order's status and its hold expiry time for the checkout countdown.
- Tests: expired holds return inventory; paid orders are never expired; running the job twice changes nothing the second time.
```

**Done when:** expired holds reliably return inventory, and a double run is harmless.

### P3.4 — Attendee details, pricing and core promotions

```text
Implement the rest of order building, as described in implementation-plan.md Phase 3 steps 4–7.

- Validate purchaser and attendee data with the validator from P2.3, and store the purchaser separately from ticket holders.
- Pricing service (backend only): unit price × quantity, group rules, then discounts. Order items keep a snapshot of the unit price, discount, fees and currency.
- Core promotions: percentage or fixed discount; eligible offerings; start and end dates; a total usage limit; a per-buyer limit (by email); a minimum quantity or order value. Count usage against the hold so concurrent orders can't exceed a limit, and release the usage when the hold expires.
- POST /api/v1/orders/{id}/apply-promotion, which recalculates the totals.
- Order states follow the baseline spec.
- Tests: every promotion rule, a concurrent test on the last use of a promotion, and a snapshot that stays unchanged after an organizer edits the price.
```

**Done when:** totals come only from the backend, and promotion limits hold under concurrent orders.

### P3.5 — Checkout screens

```text
Build checkout in /web.
- From the event page: choose an offering and quantity → purchaser and attendee forms generated from the event's setup (per-ticket forms only in "every ticket" mode) → promo code → order summary from the API → a "Continue to payment" placeholder.
- A visible countdown for the hold. When it expires, explain clearly and offer to start again.
- Handle sold-out and sales-closed responses gracefully.
- The forms are accessible, with labels, keyboard support and readable error messages.
- Component tests for the dynamic attendee form, plus one Playwright test of the happy path up to the payment placeholder.
```

**Done when:** a buyer can build an order with attendee details and a promo code, and the countdown and expiry behave correctly.

**Then run the [phase exit check](#phase-exit-check) for Phase 3.** This completes payment milestone M2.

---

## Phase 4 — M-Pesa and card payments

### P4.1 — Payment module and fake provider

```text
Create the payment module described in implementation-plan.md Phase 4 step 1.
- A PaymentProvider interface with: initiate(order), queryStatus(payment), refund(payment, amount) and parseNotification(request).
- Payment records: provider, provider reference, amount, currency, status and idempotency key, plus a payment attempts history.
- A FakeProvider for tests and local development that can simulate success, failure, cancellation, timeout (no notification), duplicate notifications and out-of-order notifications.
- POST /api/v1/orders/{id}/payments starts a payment. Using the same idempotency key twice returns the same payment instead of starting a new one.
Don't integrate any real provider yet.
```

**Done when:** a payment can be started with the fake provider, and idempotent repeats are tested.

### P4.2 — Notification handling pipeline

```text
Implement payment notification handling as described in implementation-plan.md Phase 4 step 4, working against the FakeProvider.

POST /api/v1/webhooks/payments/{provider}:
1. Record the raw notification in payment_webhook_events for auditing.
2. Ask the provider adapter for the real payment status (for Pesapal, the transaction status endpoint). Never act on the notification body alone.
3. In one transaction: lock the order row. If the order is already paid, stop, because repeats must be harmless. Otherwise check the amount and currency match; mark the payment succeeded; mark the order paid; convert its held inventory to sold (reserved_count down, sold_count up); write an outbox record for "issue tickets and send email" (Phase 5 will consume it).
4. Reply in the format the provider expects. Slow work goes to the queue.

A payment that arrives after the hold expired follows implementation-plan.md §11 #11 (ask me if it's still open).
An amount mismatch is never marked paid; it is flagged for review.
Tests: every scenario in the Phase 4 sandbox test matrix in implementation-plan.md, using the FakeProvider.
```

**Done when:** every test-matrix scenario passes with the FakeProvider, including duplicates and out-of-order notifications.

### P4.3 — Pesapal integration

```text
Implement the Pesapal provider adapter, following the Pesapal API 3.0 documentation. Check endpoint names, field names and status codes in the documentation rather than relying on memory, and tell me if anything below doesn't match it.

- Base URL, consumer key and consumer secret come from environment variables, with separate values for sandbox and production.
- Authentication: request an access token (RequestToken) and cache it until shortly before it expires.
- IPN registration: an artisan command that registers our IPN URL (RegisterIPN) for the current environment and stores the returned IPN ID in config. Run it once per environment and document it.
- initiate(): submit the order (SubmitOrderRequest) with the amount, currency, description, buyer email, phone and name, the IPN ID and our callback URL. Use the payment attempt's ID, not the order ID, as the merchant reference, so a buyer can retry a failed payment on the same order. Save the order tracking ID Pesapal returns as the payment's provider reference, and return the hosted checkout URL.
- queryStatus(): call the transaction status endpoint (GetTransactionStatus) with the order tracking ID and map Pesapal's statuses (completed, failed, invalid, reversed) to our payment states. Keep the payment method and confirmation code it returns.
- parseNotification(): read the order tracking ID, merchant reference and notification type from the IPN, then confirm with queryStatus(). Build the IPN reply in the exact format Pesapal requires.
- refund(): use Pesapal's refund request (RefundRequest) where Pesapal supports it for the payment method, and track the refund's status.
- If an order total isn't a whole number of shillings, check how Pesapal handles that for M-Pesa and ask me before rounding anything.
- Tests with recorded sandbox responses for every method. Also write doc/payments-pesapal.md: how to get sandbox credentials, register the IPN, run a test M-Pesa payment and a test card payment, and switch to production.
```

**Done when:** a sandbox M-Pesa payment and a sandbox card payment both complete end to end through the IPN and the status check, and the recorded-response tests pass.

### P4.4 — Reconciliation and refunds

```text
Implement implementation-plan.md Phase 4 steps 6–8.
- Reconciliation job: every few minutes (configurable), query the provider about payments that have been pending longer than a threshold. Resolve them through the same transactional path as notifications, or flag them. Record a reconciliation report.
- Refunds: start them through the provider where it supports refunds, and track their status. Move the order to partially refunded or refunded. After a full refund, revoke its tickets. Follow the refund approval rules in implementation-plan.md §11 #12, and audit every refund.
- Payment history endpoints: organizers see their own events; platform admins see everything, with filters by status, provider and date.
- Tests for each.
```

**Done when:** stuck pending payments get resolved by reconciliation, and refunds update the order and ticket states correctly.

### P4.5 — Payment screens

```text
Build the payment step in /web.
- "Pay now" starts the payment and sends the buyer to Pesapal's hosted checkout, where they choose M-Pesa or card. Use a full-page redirect unless I ask for an embedded frame.
- The callback page Pesapal returns the buyer to shows "Confirming your payment…" and checks the order status from the API every few seconds until it's final. It shows clear outcomes for paid, failed and still pending (with a note that the email will follow), plus a retry button for failed payments while the hold is still active.
- The result always comes from the API's status, never from redirect parameters.
In /manage, add payment history and reconciliation screens for organizers and admins, showing the Pesapal payment method and confirmation code.
Add a Playwright test of the payment flow against the FakeProvider.
```

**Done when:** a buyer can pay by M-Pesa and by card in the Pesapal sandbox through the user interface.

**Then run the [phase exit check](#phase-exit-check) for Phase 4.** Save the sandbox test evidence for deliverable D9.

---

## Phase 5 — Ticket issuance and delivery

### P5.1 — Ticket issuance and QR codes

```text
Implement ticket issuance as described in implementation-plan.md Phase 5 steps 1–2.
- Issue tickets inside the payment transaction from P4.2: one ticket per unit bought, each with a unique, non-sequential ticket number, linked to its attendee, offering, delegation and seat where applicable.
- QR credential: a random token with at least 128 bits of entropy. The QR code contains only this token. Store its hash for lookups and an encrypted copy (Laravel's encrypted cast) so the ticket page can show the QR again. Never store it in plain text.
- Issuing must be idempotent: processing the same paid order twice never creates extra tickets.
- Tests: the correct number of tickets, uniqueness, no personal data in the QR payload, and repeated processing creating no duplicates.
```

**Done when:** paid orders produce exactly the right tickets, even when a notification is processed twice.

### P5.2 — Retrieval links and email

```text
Implement implementation-plan.md Phase 5 steps 3–4.
- Retrieval tokens: random tokens, stored hashed in ticket_access_tokens, with an expiry date and the ability to revoke them.
- GET /api/v1/ticket-access/{token} returns the order's tickets for a valid token only.
- POST /api/v1/ticket-access/resend: the buyer enters their email address and order reference, and a new link is emailed. Rate-limit it, and always return the same response so it doesn't reveal whether an order exists.
- Email: a queued Laravel notification, sent from the outbox record written in P4.2, with retries and logged failures. Use the provider from implementation-plan.md §11 #9.
- Document the SPF, DKIM and DMARC DNS records the client must add, in doc/email-setup.md.
- Tests for valid, expired and revoked tokens, for the resend rate limit, and for the email being queued exactly once per order.
```

**Done when:** a paid order sends one email with a working link, and invalid tokens are rejected.

### P5.3 — Revocation and Media tickets

```text
Implement implementation-plan.md Phase 5 steps 5–6.
- Organizers and admins can revoke a ticket with a reason. Revocation is audited, it can't be undone without a separate audited reinstate action, and revoked tickets will fail at the gate.
- Media tickets: if an offering requires approval, its orders wait for organizer approval before tickets are issued. Organizers can also issue Media tickets manually, at no charge, with the attendee's details; manual issues are audited.
- Policies and tests for each.
```

**Done when:** revocation and the Media approval flow work and are audited.

### P5.4 — Ticket page and organizer ticket screens

```text
Build:
- /web ticket page at /t/{token}: event details in the event's timezone, category, attendee name (where collected), ticket number, seat or delegation, and the QR code rendered from the token. Add a download as PDF or image, and a link to request a new retrieval link.
- /manage organizer screens: a ticket list with search by ticket number, name or email; revoke with a reason; and the Media approval queue and manual issue form.
Add a Playwright test: complete a fake payment → open the emailed link (captured in test mode) → the ticket page shows the right number of QR codes.
```

**Done when:** the buyer can open and download their tickets, and organizers can find and revoke them.

**Then run the [phase exit check](#phase-exit-check) for Phase 5.** This completes payment milestone M3.

---

## Phase 6 — Gate access and scanning

### P6.1 — Scan endpoint and atomic redemption

```text
Implement POST /api/v1/events/{event}/scans/validate as described in implementation-plan.md Phase 6 steps 1–4.

Checks, in order: the guard is logged in → assigned to this event (and to this gate, if the assignment names one) → the gate is open by server time → the ticket exists (looked up by token hash) → it belongs to this event → it is paid and not revoked → redeem it.

Redeem with one atomic update:
    UPDATE tickets SET status = 'redeemed', redeemed_at = now()
    WHERE id = :id AND event_id = :event_id AND status = 'valid';
1 row updated = admit. 0 rows = already redeemed (or the status changed).

- Log every attempt in ticket_scans, including rejected ones: result, gate, guard and time.
- The response contains only: the result, the reason, the category, the attendee name where collected, the seat or delegation, and, for a ticket already used, the earlier time and gate.
- Rate-limit by guard.
```

**Done when:** valid tickets are admitted once, and every rejection reason is returned correctly.

### P6.2 — Simultaneous-scan and rejection tests

```text
Write tests for implementation-plan.md Phase 6 steps 9–10:
- Simultaneous scans: 10 parallel requests from different guards for the same ticket. Exactly one is admitted and nine get "already redeemed". Run against PostgreSQL with real parallel processes.
- One test for each rejection case: wrong event, wrong gate, revoked, unpaid, before the gate opens, after it closes, unknown token, a guard not assigned, and a guard whose assignment was removed.
- Every attempt appears in ticket_scans.
Show me the output.
```

**Done when:** the simultaneous-scan test passes repeatedly, and every rejection case is covered.

### P6.3 — Guard scanner screens

```text
Build the guard area in /manage, designed for phones first:
- Log in → list of assigned events → choose a gate → full-screen camera scanner. Use a maintained browser QR-scanning library and tell me which one you chose and why. Note that camera access requires HTTPS.
- Result screens: large and high-contrast, readable at arm's length, each with a distinct color and icon, plus a sound or vibration where the device supports it. The four states are VALID (admit), ALREADY USED (show the time and gate), INVALID (show the reason) and UNABLE TO VERIFY.
- Fail closed: a timeout (about 5 seconds, configurable) or a network error shows "Unable to verify — do not admit". It must never show success.
- After each result, the scanner returns to scanning with one tap.
- Organizer view: live gate activity and attendance counts for each gate.
Add component tests for each result state, including the network-error state.
```

**Done when:** a guard can scan on a real phone over HTTPS on staging, and switching off the network shows "Unable to verify".

**Then run the [phase exit check](#phase-exit-check) for Phase 6.** After that, hold the field rehearsal from implementation-plan.md Phase 6 step 11 with real phones.

---

## Phase 7 — Reserved seating, promotions and reporting

### P7.1 — Reserved seating (backend)

```text
Implement reserved seating as described in implementation-plan.md Phase 7 steps 1–5.
- Organizers manage sections, rows and seat numbers, including bulk creation (for example, row A, seats 1–30). No visual seat-map editor: it's out of scope (SOW §9).
- Seat states: available, held, sold and released. Seat holds reuse the generic hold mechanism from P3.1 and the expiry job from P3.3.
- During checkout for a seated event, the buyer picks specific seats, and the hold covers both the offering capacity and the seats, in one transaction.
- The partial unique index ensures one active ticket per seat per event.
- The seat appears on the ticket and in the scan response.
- Events without seating need no seat setup.
- Tests, including a simultaneous test where two buyers choose the same seat and exactly one gets it.
```

**Done when:** a seat can never be sold twice, and expired holds release seats.

### P7.2 — Seat selection screens

```text
Build:
- /manage: a seating setup screen (sections and rows, bulk seat creation, a seat list with states).
- /web: seat selection during checkout, shown as an accessible grid by section and row with clear available, held and selected states, and fully usable with the keyboard. Seats taken by someone else while the buyer is choosing show a clear message.
```

**Done when:** a buyer can pick seats, and the chosen seats appear on their tickets.

### P7.3 — Remaining promotion rules and reports (backend)

```text
Implement implementation-plan.md Phase 7 steps 6–8.
- Complete any promotion rules from SOW §2.8 that P3.4 didn't cover, plus redemption tracking.
- Organizer report endpoints (own events only): sales by offering, payment status, remaining capacity, promotion performance, attendance, and gate activity by time and gate.
- Admin report endpoints: platform-wide summaries, transaction oversight, and an audit log search filtered by actor, organizer, action and date.
- Reports must match the order and payment records exactly. Write tests that compare report totals with direct sums.
- Tenant-isolation tests for every report endpoint.
```

**Done when:** report figures match the records, and organizers only ever see their own events.

### P7.4 — Report screens

```text
Build report screens in /manage for organizers and admins, using the P7.3 endpoints:
- Summary cards, tables with filters, and simple charts where they help: sales over time and attendance by gate.
- An audit log viewer for admins.
- Every number is labeled with its unit and time range.
```

**Done when:** organizers and admins can answer "how many sold, how much paid, how many admitted" from the screens.

**Then run the [phase exit check](#phase-exit-check) for Phase 7.**

---

## Phase 8 — Testing, deployment and handover

### P8.1 — End-to-end tests

```text
Write Playwright end-to-end tests for the full journey, running against staging-like settings with the FakeProvider:
1. An organizer creates and publishes an event with two offerings.
2. A buyer purchases tickets (one simulated M-Pesa payment and one simulated card payment), receives the email (captured in test mode) and opens the tickets.
3. A guard scans each ticket: admitted. Scanning one again: "already used".
4. The organizer sees the sale, the payment and the attendance in the reports.
5. A refund revokes the tickets, and a scan then shows "invalid".
Add them to CI as a separate job.
```

**Done when:** the full journey passes in CI.

### P8.2 — Security review

```text
Review the whole codebase for security, as described in implementation-plan.md Phase 8 step 3, then fix what you find.
Check: authorization on every route (compare against v1.md §3); tenant isolation; mass assignment; input validation; SQL injection; XSS in both frontends; CSRF; CORS; session and cookie flags; rate limits on login, resend, scan and webhook endpoints; webhook forgery; secrets in the repository or logs; personal data in logs; security headers; and dependency vulnerabilities (composer audit, pnpm audit).
First report your findings ranked by severity, each with its location and the fix. Wait for my go-ahead, then fix them one by one, adding a test for each.
```

**Done when:** no critical or high findings remain open.

### P8.3 — Performance tests

```text
Write k6 scripts for the performance targets recorded in implementation-plan.md Phase 0 step 6:
- A checkout burst: many buyers ordering from one event at the same moment (FakeProvider for payments).
- Scan throughput: several guards scanning in parallel.
Report response times (median and 95th percentile) against the targets, and identify the slowest queries. Suggest indexes or fixes and apply them after I approve.
```

**Done when:** the targets are met on staging, or the gaps are documented with a plan.

### P8.4 — Monitoring and operations

```text
Set up production monitoring as described in implementation-plan.md Phase 8 steps 5–6:
- Error tracking for /api, /web and /manage, with personal data scrubbed.
- Health and heartbeat checks suitable for an external uptime monitor.
- Alerts for: failed queued jobs, a scheduler that has stopped, payments stuck in pending longer than a threshold, reconciliation mismatches, and email send failures.
- doc/runbook.md: how to respond to each alert, how to restore a backup into staging, and how to rotate payment and email credentials.
```

**Done when:** each alert has been triggered on staging and reached the right people, and the runbook covers each one.

### P8.5 — Go-live checklist and handover documents

```text
Prepare the go-live and handover documents described in implementation-plan.md Phase 8 steps 7–11:
- doc/go-live-checklist.md: production environment variables, cron, HTTPS on all subdomains, Sanctum production domains, production Pesapal keys and the production IPN registration, email DNS records, backups confirmed, monitoring active, the smoke test, and one real low-value payment with each method followed by a refund.
- Update doc/deployment.md and the OpenAPI contract so they match the final code.
- doc/admin-guide.md and doc/organizer-guide.md: plain-language guides with the main tasks step by step.
- A test results summary for deliverable D11, mapping each SOW §5 acceptance criterion to the tests that prove it.
```

**Done when:** every SOW §5 criterion maps to evidence, and the guides cover every main task.

**Then run the [phase exit check](#phase-exit-check) for Phase 8.** UAT, client sign-off and payment milestone M4 follow.
