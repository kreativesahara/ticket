# AI Build Prompts: Event Ticketing Platform

Ready-to-paste prompts for an AI coding assistant (such as Claude Code or Cursor) to build the platform in the order set out in [implementation-plan.md](implementation-plan.md).

## How to use these prompts

1. **Finish Phase 0's human tasks first.** Settle the decision log (implementation-plan.md §11), work through the open hurdles in [hurdles.md](hurdles.md) (H1–H5 first, because they change the database and payment design), get the business rules in [limitation.md](limitation.md) signed off, submit the Pesapal merchant application and run the hosting check. Several prompts depend on those answers, and each one names the hurdles it depends on.
2. **Give the assistant the project context.** Paste [the context block](#project-context) at the start of every new session. Alternatively, save it in your tool's project instructions file (`CLAUDE.md` for Claude Code) so it loads automatically. The backend repository already has it. Give the frontend repository a copy that points to this repository for the specification and to the published OpenAPI contract.
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
- v1.md — requirements, and the single source of truth for table names, roles and API paths: permissions matrix (§3), schema (§4), state models (§4.6), API (§5).
- doc/state-machines.md — complete state machines for events, orders, payments, tickets, seats and reservations (created in P0.1).
- techstack.md — architecture and stack.
- sow.md — contractual scope. Never build anything listed in §9 "Out of scope".
- hurdles.md — known gaps (H1–H13), each marked Open or Decided. An open hurdle is an undecided requirement: don't guess, ask me.
- limitation.md — the client-facing business rules (Part A), accepted limitations (Part B) and client decisions (Part C). If it disagrees with v1.md, v1.md wins; tell me about the mismatch.

STACK:
- Two repositories. The backend repository (this one) holds /api and the specification; the frontend repository holds the Next.js app.
- /api — Laravel (latest stable release), PostgreSQL on Supabase. Admins, organizer staff and event owners sign in with Google OAuth through Laravel Socialite. Guards sign in only with an email and password account created by their organizer, valid only for their assigned event; only guard accounts scan. Sanctum SPA cookie sessions after sign-in.
- Frontend — one Next.js + TypeScript app with server-side rendering, on Vercel, for every role: the public event site, checkout and ticket retrieval, plus the organizer, event owner, admin and guard areas. shadcn/ui components and Tailwind CSS.
- Payments — Pesapal API 3.0: buyers pay by M-Pesa or card on Pesapal's hosted checkout; Pesapal sends IPN notifications; results are confirmed with Pesapal's transaction status endpoint.
- API hosted on cPanel: scheduler and queue run from cron.

NON-NEGOTIABLE RULES:
1. Tenant isolation: every organizer-owned resource is authorized through a Laravel policy that checks the authenticated user's organizer membership, or, for event owners and guards, their event assignment. Event owners may only view, filter and export the guest list of their assigned events. Never trust role, organizer_id, event_id or gate_id from the request without checking it.
2. Money is stored as integer minor units plus a currency code. Never floats.
3. Inventory holds, payment confirmation, ticket issuance and ticket redemption use database transactions and atomic conditional updates. Never read-then-write checks.
4. Tests run against PostgreSQL, never SQLite.
5. Every new resource gets a tenant-isolation test.
6. Sensitive actions write to the audit log.
7. Never store raw card data, plaintext passwords (guard passwords are hashed, and deleted when their event closes), Google access tokens, plaintext QR tokens or plaintext retrieval tokens. Never commit secrets.
8. The backend calculates all prices and decides all statuses. Never trust values from the browser or a payment redirect.

HOW TO WORK:
- Before coding, restate the task as a short plan and name the files you'll change.
- If the task depends on a decision that is still open in implementation-plan.md §11, or on a hurdle still marked Open in hurdles.md, stop and ask me.
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
List any hurdle in hurdles.md that should be decided by the next phase and is still Open.
Then run the full test suite and report the results.
Don't fix anything yet — just report.
```

---

## Phase 0 — Discovery and specification

### P0.1 — Specification check and state machines

```text
Read v1.md, techstack.md, sow.md, implementation-plan.md, hurdles.md and limitation.md. v1.md is the single source of truth for table names, roles and API paths. Don't write code yet.

1. Check that techstack.md, sow.md, implementation-plan.md and limitation.md agree with v1.md on table names, roles, API paths, business rules, the QR storage rule and the short code rules. Also check that every Decided hurdle in hurdles.md has been recorded in v1.md and the decision log. List every mismatch with its file and line, and which document should change. Don't edit the documents yourself.
2. Create doc/state-machines.md with the complete state machines for events, orders, payments, tickets, seats and inventory reservations: every state, every allowed transition, what triggers it, and who may trigger it. Include the states v1.md mentions only in prose (cancelled, expired, partially refunded, refunded, revoked), Pesapal's reversed payments, and the event state model in v1.md §4.6, including the closed state that a published event enters automatically when its last gate closes.
3. List anything still ambiguous in the specification, written as questions for me. Include every hurdle still marked Open, with the prompts it blocks.
```

**Done when:** no mismatches remain, the state machines are written down, and you've answered the open questions.

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
Set up the two repositories as described in implementation-plan.md Phase 1 step 6:
- Backend repository (this one): /api, a new Laravel project (latest stable) configured for PostgreSQL, with Pest for tests; docker-compose.yml with PostgreSQL for local development and tests; Laravel Pint and Larastan.
- Frontend repository: one Next.js app with TypeScript, the App Router, server-side rendering, Tailwind CSS and shadcn/ui (theme defined as CSS variables), using pnpm; ESLint and strict TypeScript. Route groups for the public site and the /organizer, /event-owner, /admin and /guard areas.
- A README in each repository explaining how to run it, plus .env.example files with no real secrets.

Check that every app starts and its default tests pass.
```

**Done when:** the API and the Next.js app run locally and their default tests pass.

### P1.2 — Continuous integration

```text
Add a CI workflow to each repository (GitHub Actions unless I say otherwise). On every push and pull request:
- Backend: install dependencies, run the Pint check, run Larastan, and run the tests against a PostgreSQL service container.
- Frontend: install, lint, type-check, build and run unit tests.
Any failure must fail the build.
```

**Done when:** a test pull request shows every check passing, and a deliberately broken test makes the build fail.

### P1.3 — Database schema and migrations

```text
Using v1.md §4 (every table and column) and doc/state-machines.md, write Laravel migrations for the full MVP schema, including seating, so that later phases need no structural rework.

Include the tables and columns the decided hurdles add: H1 (settlement report and payout log), H3 (zero-total orders, delegation sub-quotas and claim codes), H5 (cancellation and postponement), H11 (checkout tokens) and H12 (deleting a person's data on request while keeping financial and scan records), plus the identity field, ticket transfers and event categories. How the platform fee is calculated (decision #2) is still open: store platform_fee_amount on order items, and ask me before adding any fee settings.

Requirements:
- UUID primary keys for records referenced from outside the system.
- A foreign key for every ownership relationship. Choose delete behavior deliberately: never cascade-delete orders, payments, tickets or audit logs.
- Unique constraints: user email; event slug per organizer; ticket_number; qr_token_hash; (event_id, short_code_hash); (provider, provider_reference) on payments, where the reference is Pesapal's order tracking ID; idempotency_key; and one active ticket per seat per event (a partial unique index). Pesapal notifications carry no unique event ID, so don't add a unique key on webhook events; idempotency comes from the payment and order checks in P4.2.
- CHECK constraints where they're cheap: non-negative counts and amounts, and capacity - sold_count - reserved_count >= 0.
- Money as bigint minor units plus a char(3) currency column.
- Status columns as strings backed by PHP enums, one enum per state machine in doc/state-machines.md.

Also create Eloquent models with relationships and enum casts, a factory for every model, and a Mermaid ERD of the final schema at doc/diagrams/erd.mmd. It replaces the outdated diagram in v1.md §4.5; follow the tables in v1.md §4, not that diagram.
Run the migrations from scratch against local PostgreSQL and show the output.
```

**Done when:** `migrate:fresh` succeeds, every model has a factory, and the ERD matches the tables in v1.md §4.

### P1.4a — Google sign-in

```text
Implement sign-in with Google as described in implementation-plan.md Phase 1 step 9 and the sign-in rules in techstack.md §3. Use Laravel Socialite for Google OAuth and Sanctum SPA cookie sessions after sign-in. Passwords exist only for guard password accounts (P1.4b); there is no self-registration, email verification or email-based password reset.

- Web routes, not under /api/v1, because they need the session for Google's state check: GET /auth/google/redirect and GET /auth/google/callback. Request only the openid, email and profile scopes. Don't store Google's access or refresh tokens.
- On callback: reject Google accounts with unverified emails. Find the user by users.google_id, looking only at Google users. Never link a Google sign-in to a guard password account: they are separate users even when the email is the same (users.email is unique per sign_in_method). If there's no Google user yet, create one with their Google ID, name and email, with no memberships. Reject suspended users. Log the user in, regenerate the session, and redirect to the Next.js app, accepting only an allowlisted return path (no open redirects).
- API endpoints: POST /api/v1/auth/logout (invalidates the session) and GET /api/v1/auth/me (the user, platform role, organizer memberships with roles, and event assignments as guard or event owner).
- Suspending a user ends their existing sessions.
- An artisan command to grant or remove platform admin (users.platform_role) by email, because no one can become an admin through sign-in.
- Configure stateful domains, the session cookie domain, CORS (with credentials) and CSRF for the www and api subdomains through environment variables. Requests that the Next.js server makes during server-side rendering forward the user's session cookie and Origin header, and must be accepted as the same session. The Google client ID, secret and callback URL also come from environment variables. Document the values for local development, including the localhost callback URL to register in the Google OAuth client.
- Rate-limit the callback route.
- Feature tests with Socialite mocked: new user, returning user, unverified email, suspended user, tampered state and a disallowed return path.
```

**Done when:** all auth tests pass, and you can sign in locally with a real Google account and call /api/v1/auth/me from the Next.js app, both from the browser and during server-side rendering.

### P1.4b — Guard password sign-in

```text
Implement guard password sign-in as described in techstack.md §3 ("Guard password accounts") and v1.md FR-01. Organizers create these accounts and issue their passwords in P2.4; for now, use factories in tests.

- Passwords live in guard_credentials (v1.md §4.1): one row per issued password, tied to one event, with the hash, password_change_required, who issued it, and revoked_at.
- POST /api/v1/auth/guard/login (email and password) using Sanctum's SPA flow; this is the only way guards sign in: the client fetches /sanctum/csrf-cookie first. Only users with sign_in_method = password can use it, and only with an unrevoked credential whose event is still open. An event is closed when its last gate closing time has passed (or its end time, if it has no gates) or it has been cancelled or archived. On success, log in and regenerate the session.
- Every request from a password guard checks again that their credential is unrevoked and its event still open. If not, end the session and return a clear "your access for this event has ended" error.
- A password account can only ever hold the guard role. Enforce this in the models and policies, so it can never become an organizer member, event owner or platform admin.
- Hash passwords with Laravel's default hasher. Rate-limit sign-in by email and IP, lock temporarily after repeated failures, and return the same error for an unknown email and a wrong password.
- First sign-in: while password_change_required is true, every endpoint except POST /api/v1/auth/guard/password and logout returns a "password change required" error. The new password must meet Laravel's password rules (minimum length, not a commonly breached password) and differ from the temporary one.
- Use the database session driver, so the platform can end a specific user's sessions. Guard sessions also expire after a configurable time (default 12 hours).
- Feature tests: successful sign-in; wrong password and unknown email give the same error; lockout; forced password change; suspended account; a closed event refuses sign-in and ends an existing session; adding a password account as an organizer member or event owner is rejected; a Google user and a guard account with the same email stay separate and each signs in only through its own route; session expiry.
```

**Done when:** all tests pass, and a factory-made guard can sign in from the Next.js app, is forced to change their password, and is signed out once their event closes.

### P1.5 — Tenancy, roles, policies and audit log

```text
Implement tenancy and authorization as described in implementation-plan.md Phase 1 steps 10, 11 and 13.

- Organizers, and organizer members with the role staff (v1.md §3 and §4.1). Staff have full control of their organizer account (profile, events, tickets, delegations, guards and reports). Organizers can also assign event owners to specific events (event_staff with role event_owner), whose role and privileges are strictly restricted to viewing, filtering, and exporting guest/attendee data. Platform admin is users.platform_role with full system-wide rights and privileges across all operations.
- A policy for every model. Platform admins pass with full rights across all resources, except scanning, which only guard accounts can do. Organizer staff pass for their own organizer's resources. Event owners pass only to view/filter/export attendee data for their assigned events.
- A safeguard so no route can skip authorization, for example a test that fails if any /api/v1 route has no policy check.
- An AuditLogger service recording the actor, organizer, event, action, resource type and ID, metadata and time.
- Endpoints: apply to create an organizer (it stays pending until a platform admin approves it), list my organizers, invite a staff member or event owner, remove a member, and approve or reject organizer applications (platform admin only).
- Invitations, for organizer staff and event owners only (guards get password accounts in P2.4, never invitations): the invitations table from v1.md §4.1, with the token stored hashed, an expiry date and single use. POST /api/v1/invitations/{token}/accept succeeds only when the signed-in user's verified Google email matches the invited email.
- A reusable tenant-isolation test helper: for a given endpoint, it asserts that a user from a different organizer is refused on read, update and delete, and that an event owner is refused on every endpoint except the guest list of their assigned events. Apply it to every endpoint built so far.
```

**Done when:** the tenant-isolation tests pass for every endpoint, and the route-coverage safeguard passes.

### P1.6 — API contract and conventions

```text
Write /api/docs/openapi.yaml covering every MVP endpoint, using the endpoints and paths in v1.md §5. Include request and response schemas for each.

Then implement the conventions in /api:
- One JSON error format for validation, authentication, authorization, not found, conflict, rate limit and server errors.
- One pagination format with a standard response envelope.
- A request correlation ID: accept X-Request-Id or generate one, return it in the response, and include it in every log line.

Add any endpoint v1.md §5 doesn't list yet, under its prefix rule, and update v1.md §5 to match.

Guest buyers have no account, so an order must never be readable by its ID alone (hurdles.md H11). POST /api/v1/orders returns a random checkout token once, stored hashed; the buyer's checkout calls (order status, apply promotion, start payment) must send it. Tickets are reached only through the retrieval link. Organizer staff and admins view orders at GET /api/v1/organizer/orders/{id}.

Add a test that checks the implemented endpoints match the contract (route list against the OpenAPI paths).
```

**Done when:** the contract covers every MVP endpoint, and the error format, pagination and correlation ID are implemented and tested.

### P1.7 — Frontend foundation

```text
Build the frontend foundation described in implementation-plan.md Phase 1 steps 14–16, in the frontend repository's Next.js app.

- shadcn/ui components we'll need first: button, input, label, form, card, dialog, table, toast, dropdown menu, badge, select and tabs.
- A typed API client generated from openapi.yaml, usable from server components and the browser. In the browser it sends cookies and calls /sanctum/csrf-cookie before any state-changing request; on the server it forwards the user's session cookie and Origin header. It turns the standard error format into typed errors.
- A mock mode driven by the OpenAPI spec (for example with MSW), so screens can be built before their endpoints exist.
- Pages: "Sign in with Google"; "Guard sign-in" (email and password), linked from it, with the first-sign-in password change screen; an invitation page (/invite/{token}) that sends the user through Google sign-in and then accepts the invitation; an "apply for an organizer account" form and a "waiting for approval" screen for users with no role yet; and logout. Route users by role to the /organizer, /event-owner, /admin and /guard areas (the event owner area shows only the guest list of their assigned events), inside an app shell with a sidebar, a header and an organizer switcher for users who belong to more than one organizer. Protect each area in Next.js middleware as well as in the API.
- The public site layout, header, footer and a placeholder home page.
- Component tests for both sign-in pages, the password change screen, and the invitation and application pages.
```

**Done when:** you can sign in with Google to the Next.js app against the real local API (mock mode uses a fake signed-in user), and you land in the right area for your role.

### P1.8 — Deploy the working skeleton

```text
Prepare the staging deployment described in implementation-plan.md Phase 1 step 17.

- /api: a deploy script, or documented steps, for cPanel: update the code, composer install --no-dev, migrate --force, cache config, routes and views. The document root points to /api/public, and the .env file sits outside the web root.
- Cron: a single entry runs `php artisan schedule:run` every minute. Unless the hosting check showed that long-running processes are allowed, the scheduler processes the database queue with `queue:work --stop-when-empty`, without overlapping runs.
- A /api/v1/health endpoint that checks the database connection and reports when the scheduler and queue last ran.
- The Next.js app on Vercel (decision #5), on the www domain, with the API's URL and the allowed domains in its environment variables.
- The staging Google OAuth settings: document the callback URL to register in the staging OAuth client.
- doc/deployment.md listing every step and every environment variable.
No real credentials in any file.
```

**Done when:** staging serves the API and the Next.js app over HTTPS, Google sign-in works and the session holds across both subdomains, including during server-side rendering, and the health endpoint shows the scheduler running.

**Then run the [phase exit check](#phase-exit-check) for Phase 1.**

---

## Phase 2 — Events and ticket configuration

### P2.1 — Event lifecycle and details

```text
Implement events in /api as described in implementation-plan.md Phase 2 steps 1–3.

- Create, update, list and show events for organizers; public list and show return published events only.
- States follow the event state model in v1.md §4.6 and doc/state-machines.md (draft, published, closed, cancelled, archived), with transitions enforced by an enum and a single service. Every transition is audited.
- A scheduled job runs every minute and moves published events to closed once their last gate closing time has passed, or their end time if they have no gates. No user can set or undo closed. Closed events can't be bought or scanned, and drop out of the public list.
- DELETE /api/v1/organizer/events/{id} deletes a draft that was never published, with an audit log entry. Any other event returns a conflict response: once published, an event can only be archived (from closed or cancelled), never deleted.
- Cancelling (hurdles.md H5): revoke every valid and pending-issuance ticket, cancel pending orders, end guard access, and write outbox records to email every buyer (P5.2 sends them). Admins refund the orders afterwards (P4.4). Changing a published event's dates is a postponement: tickets stay valid, refund_requests_until is set from the event's refund policy, and buyers are emailed a refund-request link.
- Name, slug (unique per organizer), description, venue, timezone (an IANA name), the identity field (none, phone or id_number; see P2.3), capacity, refund policy, a category from the admins' list, the captcha_required flag and images stored in Supabase Storage with validated type and size.
- Separate time windows, each validated: sales start and end, gate opening and closing, event start and end. Store times in UTC and return them together with the event's timezone.
- Policies, tenant-isolation tests and feature tests for every rule.
```

**Done when:** the lifecycle and time-window rules are tested, events close automatically when their last gate closes (or at their end time without gates), and the public endpoints never return draft or closed events.

### P2.2 — Ticket types, categories, offerings and delegations

```text
Implement ticket configuration as described in implementation-plan.md Phase 2 steps 4–6.

- Ticket types and categories for each event. Seed the defaults (Early Bird, Normal, At the Gate, Group, Promoted; VVIP, VIP, Media) and let organizers add, rename and disable their own.
- Offerings: each allowed type × category combination, with a price in minor units, a currency, capacity, a sales window, a minimum and maximum per order, and a status. Group types define their required quantity. Media can be flagged "approval required".
- Delegations (hurdles.md H3, doc/sponsored.md): name, allocation, allowed categories (delegation_categories), price rule and status. A delegation is a sub-quota of its offerings' capacity, with its own sold and reserved counters. Generate a private claim link and an access code for each, stored as a hash plus an encrypted copy so staff can show them again.
- Validation: the total capacity of all offerings can't exceed the event's capacity, and an offering's capacity can't drop below what has been sold plus what is held.
- At the Gate tickets (hurdles.md H4) are ordinary offerings that buyers buy on their own phones through the public site. No box office and no cash sales.
- Policies, tenant-isolation tests and feature tests.
```

**Done when:** offerings and delegations can be created and edited within their capacity rules, and every rule is tested.

### P2.3 — Attendee information setup

```text
Implement attendee information settings for each event, as described in implementation-plan.md Phase 2 step 7.

- The collection mode: purchaser details only, or details for every ticket.
- Field definitions for extra questions: key, label, type (select, checkbox or date only; no free-text questions), whether it's required, options for select fields, display order, and whether it applies to the purchaser or each ticket.
- Full name and email are always collected from the purchaser.
- The identity field (v1.md FR-07): events.identity_field is none, phone or id_number. Staff set it when creating the event and can change it only while the event is a draft. With phone or id_number, the collection mode is forced to every ticket, and each ticket holder must give their name and that field. Phone and ID number are not field types organizers can add as questions, so no event can ask for both, and buyers never choose which one to give.
- A reusable validator that, given an event, validates submitted purchaser and attendee data against the event's setup. It requires the identity field for every ticket holder when the event sets one, and rejects any field the event didn't ask for, such as an ID number on a phone event or either one on a none event. Phase 3 will use it.
- Store phone numbers in attendees.phone and only the last 4 digits of an ID number in attendees.id_number_last4, with a CHECK constraint so an attendee row never has both.
- Tests for both collection modes and every field type, including: a phone event rejects an ID number, an ID event rejects a phone number and a none event rejects both; every ticket holder must give the identity field; the identity field can't change once the event is published; only the last 4 ID digits reach the database.
```

**Done when:** the validator accepts valid data and rejects invalid data in both collection modes.

### P2.4 — Gates, guard accounts and assignments

```text
Implement gates and staff as described in implementation-plan.md Phase 2 step 8.

- Gates for each event, with a name, opening and closing times, and a status. Gates are optional: an event without gates admits people between its start and end times.
- Guard password accounts (techstack.md §3): POST /api/v1/organizer/guards creates the account (name and the guard's own email) with managed_by_organizer_id set to the creating organizer. Guards never use Google; the email may match a Google user's, and the two stay separate users. A password account is assigned to one event at a time. POST /api/v1/organizer/guards/{id}/password issues a temporary password for the guard's current assignment: it creates a guard_credentials row, returns the password once in the response and never again, and revokes any earlier credential and its sessions. PATCH /api/v1/organizer/guards/{id} disables or re-enables the account. Only the owning organizer or a platform admin can assign, issue passwords for or disable a password guard.
- Revocation when the event closes: a scheduled job runs every minute. For every event that has closed (entry ended, or cancelled or archived), it deletes the password hash of each of its guard credentials, sets revoked_at, deletes the guards' sessions, ends their assignments and writes an audit log entry. Cancelling or archiving an event triggers the same revocation immediately. Keep the guard's user record, so scan records still name them. Every guard assignment for the event ends.
- Assignments link a guard to an event and, optionally, a gate. An event can have several guards. Organizers can remove assignments. Assignment changes are audited.
- Event owner assignments: accepting an event_owner invitation creates an event_staff row with role event_owner and no gate. Organizers can list and remove an event's event owners; removal ends their access to that event immediately and is audited.
- An endpoint for a guard to list only their current assignments.
- Tests: a guard sees only their own assignments and can't reach any organizer endpoint; an event owner sees only their assigned events and is refused on every organizer endpoint except the guest list; an expired or reused event owner invitation fails; signing in with a different Google email doesn't accept it; another organizer can't assign or issue a password for a password guard; a temporary password can't be retrieved after it's issued; a password account can't be assigned to a second open event; once the last gate closes, the password hash is gone, the guard can't sign in and their session has ended; cancelling an event revokes its guard passwords immediately; and the guard's past scans still show their name.
```

**Done when:** guard account creation and assignment work end to end, event owner invitations work, and guards are confined to their assignments.

### P2.5 — Publishing rules and admin controls

```text
Implement publishing rules and admin controls as described in implementation-plan.md Phase 2 steps 9–10.

- An event can be published only when it has at least one active offering, valid and consistent time windows, capacity that covers all allocations, and, if any offering has a price above zero, a verified organizer. If not, return every failing rule at once.
- After publishing: audit every change, and block or require explicit confirmation for changes that affect existing orders (lowering capacity below sold, changing the price of an offering that has sales, moving the event's dates).
- Organizer verification (hurdles.md H2): organizers submit business or ID details and their payout account (stored encrypted); an admin verifies or rejects them.
- Event categories: admins create, rename and disable categories; the public list endpoint returns active ones.
- Admin endpoints: list organizers, suspend and reactivate organizers (suspension unpublishes nothing but blocks new sales and the sign-ins of its staff, event owners and guards), and view any event, with the access audited.
- Tests for each rule.
```

**Done when:** publishing refuses incomplete events with clear reasons, and suspended organizers can't sell.

### P2.6 — Organizer and admin dashboards

```text
Build the organizer and admin areas of the Next.js app for Phase 2, using shadcn/ui components.

Organizer:
- Event list with status badges and filters.
- A step-by-step event editor: details → times → tickets (an offerings grid of type × category) → attendee fields → gates and staff → review and publish. The publish step shows the API's failing rules if there are any.
- Delegation management with claim links and access codes; staff and event owner invitations, and each event's event owners with a remove action; guard password accounts (create with the guard's email, assign, "Issue password" with a one-time copy dialog, disable); and guard assignments, showing when each password guard's access ends.

Admin:
- Organizer list with suspend and reactivate actions behind a confirmation dialog, the organizer verification queue, and the event category list.

Forms validate in the browser for convenience, but always show the API's validation errors. Add component tests for the editor steps.
```

**Done when:** an organizer can create and publish an event entirely through the user interface.

### P2.7 — Public event pages

```text
Build the public event pages in the Next.js app:
- An event listing page showing published events, with search by name, a category filter and a date filter.
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

- POST /api/v1/orders (guest checkout allowed) validates the offering, the sales window, quantity limits, group rules, delegation eligibility (claim token or access code) and, when the event has captcha_required on, the CAPTCHA response.
- Hold the inventory inside one database transaction, using this atomic conditional update for each offering:
    UPDATE event_ticket_offerings
    SET reserved_count = reserved_count + :qty
    WHERE id = :id AND status = 'active'
      AND capacity - sold_count - reserved_count >= :qty;
  If 0 rows are updated, roll back and return a "sold out" conflict response. For a delegation order, hold its quota the same way in the same transaction. On success, insert inventory_reservations rows with expires_at.
- Build the hold mechanism generically (offering holds now, seat holds in Phase 7).
- Create the order as pending, with the purchaser's details, and return a random checkout token once (stored hashed; P1.6).
- The hold duration comes from config: 15 minutes by default (decision #10).
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
- An endpoint that returns an order's status and its hold expiry time for the checkout countdown. It requires the order's checkout token (P1.6): knowing the order ID alone isn't enough.
- Tests: expired holds return inventory; paid orders are never expired; running the job twice changes nothing the second time.
```

**Done when:** expired holds reliably return inventory, and a double run is harmless.

### P3.4 — Attendee details, pricing and core promotions

```text
Implement the rest of order building, as described in implementation-plan.md Phase 3 steps 4–7.

- Validate purchaser and attendee data with the validator from P2.3, and store the purchaser separately from ticket holders. An order whose details include a field the event didn't ask for, such as an ID number on a phone event, is rejected before any inventory is held.
- Pricing service (backend only): unit price × quantity, group rules, then discounts. Order items keep a snapshot of the unit price, discount, fees and currency. The platform fee is deducted from the organizer, never added to the buyer's total (decision #2); snapshot it on each order item. How it's calculated is still open, so ask me before implementing the calculation.
- Orders that total zero (complimentary, a free delegation claim or a 100% discount) skip payment (hurdles.md H3): the order goes pending → paid → completed in one transaction, with the same checks and audit trail, and no Pesapal payment.
- Core promotions: percentage or fixed discount; eligible offerings; start and end dates; a total usage limit; a per-buyer limit (by email); a minimum quantity or order value. Count usage against the hold so concurrent orders can't exceed a limit, and release the usage when the hold expires.
- POST /api/v1/orders/{id}/apply-promotion, which recalculates the totals.
- Order states follow doc/state-machines.md.
- Tests: every promotion rule, a concurrent test on the last use of a promotion, and a snapshot that stays unchanged after an organizer edits the price.
```

**Done when:** totals come only from the backend, and promotion limits hold under concurrent orders.

### P3.5 — Checkout screens

```text
Build checkout in the Next.js app.
- From the event page: choose an offering and quantity → purchaser and attendee forms generated from the event's setup (per-ticket forms only in "every ticket" mode) → promo code → order summary from the API → a "Continue to payment" placeholder.
- A visible countdown for the hold. When it expires, explain clearly and offer to start again.
- Handle sold-out and sales-closed responses gracefully.
- A CAPTCHA step when the event has captcha_required on (hurdles.md H13). Tell me which CAPTCHA provider you chose and why.
- A delegation claim page (/claim/{token}, or an access code entry) that opens checkout for that delegation's allowed categories.
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

A payment confirmed when the order is no longer pending (expired, cancelled or already paid), for an event no longer on sale, or with a wrong amount or currency, is never marked paid: mark it rejected and create an automatic refund for it (decision #11, doc/state-machines.md §3), and notify an admin. P4.4 submits the refund.
Tests: every scenario in the Phase 4 sandbox test matrix in implementation-plan.md, using the FakeProvider.
```

**Done when:** every test-matrix scenario passes with the FakeProvider, including duplicates and out-of-order notifications.

### P4.3 — Pesapal integration

```text
Implement the Pesapal provider adapter, following the Pesapal API 3.0 documentation. Check endpoint names, field names and status codes in the documentation rather than relying on memory, and tell me if anything below doesn't match it.

- Base URL, consumer key and consumer secret come from environment variables, with separate values for sandbox and production.
- Authentication: request an access token (RequestToken) and cache it until shortly before it expires.
- IPN registration: an artisan command that registers our IPN URL (RegisterIPN) for the current environment and stores the returned IPN ID in config. Run it once per environment and document it.
- initiate(): submit the order (SubmitOrderRequest) with the amount, currency, description, buyer email and name (never a phone or ID number from our checkout), the IPN ID and our callback URL. Use the payment attempt's ID, not the order ID, as the merchant reference, so a buyer can retry a failed payment on the same order. Save the order tracking ID Pesapal returns as the payment's provider reference, and return the hosted checkout URL.
- queryStatus(): call the transaction status endpoint (GetTransactionStatus) with the order tracking ID and map Pesapal's statuses (completed, failed, invalid, reversed) to our payment states. Keep only the payment method and confirmation code it returns. Never store, log or audit the paying account (Pesapal's payment_account field: the M-Pesa phone number or masked card number); strip it from the response before anything is saved.
- parseNotification(): read the order tracking ID, merchant reference and notification type from the IPN, then confirm with queryStatus(). Build the IPN reply in the exact format Pesapal requires.
- refund(): use Pesapal's refund request (RefundRequest) where Pesapal supports it for the payment method, and track the refund's status.
- If an order total isn't a whole number of shillings, check how Pesapal handles that for M-Pesa and ask me before rounding anything.
- Tests with recorded sandbox responses for every method, with the paying account removed from the recordings, plus a test that no stored record or log line contains it. Also write doc/payments-pesapal.md: how to get sandbox credentials, register the IPN, run a test M-Pesa payment and a test card payment, and switch to production.
```

**Done when:** a sandbox M-Pesa payment and a sandbox card payment both complete end to end through the IPN and the status check, and the recorded-response tests pass.

### P4.4 — Reconciliation and refunds

```text
Implement implementation-plan.md Phase 4 steps 6–8.
- Reconciliation job: every few minutes (configurable), query the provider about payments that have been pending longer than a threshold. Resolve them through the same transactional path as notifications, or flag them. Record a reconciliation report.
- Refunds (decision #12): only platform admins start them, naming the tickets each covers; the system starts them only for rejected payments. Submit through Pesapal where it supports the payment method, or let an admin record a manual refund with its reference. Track their status. Revoke the covered tickets and move the order to partially refunded or refunded; a never-redeemed ticket's place goes back on sale if the event is still published. Audit every refund.
- Cancelled and postponed events (hurdles.md H5): for a cancelled event, admins see a list of its orders awaiting refund and refund them one by one. For a postponed event, ticket holders request a refund through the link in the postponement email (POST /api/v1/ticket-access/{token}/refund-requests) until refund_requests_until; each request lands in an admin queue to approve or decline.
- Payment history endpoints: organizers see their own events; platform admins see everything, with filters by status, provider and date.
- Tests for each.
```

**Done when:** stuck pending payments get resolved by reconciliation, and refunds update the order and ticket states correctly.

### P4.5 — Payment screens

```text
Build the payment step in the Next.js app.
- "Pay now" starts the payment and sends the buyer to Pesapal's hosted checkout, where they choose M-Pesa or card. Use a full-page redirect unless I ask for an embedded frame.
- The callback page Pesapal returns the buyer to shows "Confirming your payment…" and checks the order status from the API every few seconds until it's final. It shows clear outcomes for paid, failed and still pending (with a note that the email will follow), plus a retry button for failed payments while the hold is still active.
- The result always comes from the API's status, never from redirect parameters. The status check sends the order's checkout token (P1.6).
In the organizer and admin areas, add payment history and reconciliation screens, the admin refund screens and the refund-request queue, showing the Pesapal payment method and confirmation code.
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
- QR credential: a random token with at least 128 bits of entropy. The QR code contains only this token. Store its hash in qr_token_hash for lookups and an encrypted copy in qr_token_encrypted (Laravel's encrypted cast) so the ticket page can show the QR again, as v1.md §4.3 requires. Never store it in plain text.
- Short code: a random 6-character code from an alphabet without easily confused characters (no 0/O or 1/I/L), unique within the event (generate again on a collision). Store a keyed hash (HMAC with a server-side secret) in short_code_hash for lookups and an encrypted copy in short_code_encrypted for display. A plain hash isn't enough, because every six-character combination can be tried in seconds.
- Issuing must be idempotent: processing the same paid order twice never creates extra tickets.
- Tests: the correct number of tickets, uniqueness of QR tokens and of short codes within an event, no personal data in the QR payload, no plain-text QR tokens or short codes in the database, and repeated processing creating no duplicates.
```

**Done when:** paid orders produce exactly the right tickets, even when a notification is processed twice.

### P5.2 — Retrieval links and email

```text
Implement implementation-plan.md Phase 5 steps 3–4.
- Retrieval tokens: random tokens, stored hashed in ticket_access_tokens, with an expiry date and the ability to revoke them.
- GET /api/v1/ticket-access/{token} returns the order's tickets for a valid token only.
- POST /api/v1/ticket-access/resend: the buyer enters their email address and order reference, and a new link is emailed. Rate-limit it, and always return the same response so it doesn't reveal whether an order exists.
- Email: a queued Laravel notification, sent from the outbox record written in P4.2, with retries and logged failures. Use the provider from implementation-plan.md §11 #9; while the client hasn't chosen one, use a local mail catcher and ask me before configuring a real provider.
- Buyer emails (hurdles.md H9, H10): the order confirmation with the retrieval link, a receipt for every paid order (with orders.receipt_number), postponement emails with the refund-request link, cancellation emails, and transfer emails. No SMS.
- Document the SPF, DKIM and DMARC DNS records the client must add, in doc/email-setup.md.
- Tests for valid, expired and revoked tokens, for the resend rate limit, and for the email being queued exactly once per order.
```

**Done when:** a paid order sends one email with a working link, and invalid tokens are rejected.

### P5.3 — Revocation, Media tickets and transfers

```text
Implement implementation-plan.md Phase 5 steps 5–6.
- Organizers and admins can revoke a ticket with a reason. Revocation is audited, it can't be undone without a separate audited reinstate action, and revoked tickets will fail at the gate.
- Media tickets: if an offering requires approval, its paid orders' tickets wait in pending_issuance until staff approve them (POST /api/v1/organizer/tickets/{id}/approve); rejection revokes them, returns their places to sale and adds them to the admin refund queue. Organizers can also issue Media tickets manually, at no charge, with the attendee's details; manual issues are audited.
- Transfers (decision #17): POST /api/v1/ticket-access/{token}/tickets/{id}/transfer lets the holder transfer a valid ticket before the event starts, unless the event's identity field is id_number. The recipient gives a name, email and, for phone events, phone number. Create a new attendee record, issue a new QR token and short code (the old ones stop working), record it in ticket_transfers, and email the recipient their own retrieval link through the outbox. The old link no longer shows the ticket.
- Policies and tests for each, including: a transfer after the event starts, or on an id_number event, is refused; the old QR code and short code are rejected at the gate.
```

**Done when:** revocation, the Media approval flow and transfers work and are audited.

### P5.4 — Ticket page and organizer ticket screens

```text
Build:
- The ticket page in the Next.js app at /t/{token}: event details in the event's timezone, category, attendee name (where collected), ticket number, seat or delegation, the QR code rendered from the token, and the 6-character short code printed clearly beside it (grouped as three and three characters, for example K7P 3QX). Add a download as PDF or image that includes the short code, a link to request a new retrieval link, a transfer form, and, after a postponement, a refund-request form.
- Organizer area screens: a ticket list with search by ticket number, name or email; revoke with a reason; and the Media approval queue and manual issue form.
Add a Playwright test: complete a fake payment → open the emailed link (captured in test mode) → the ticket page shows the right number of QR codes.
```

**Done when:** the buyer can open and download their tickets, and organizers can find and revoke them.

**Then run the [phase exit check](#phase-exit-check) for Phase 5.** This completes payment milestone M3.

---

## Phase 6 — Gate access and scanning

### P6.1 — Scan endpoint and atomic redemption

```text
Implement POST /api/v1/events/{event}/scans/validate as described in implementation-plan.md Phase 6 steps 1–4.

Checks, in order: a guard password account is logged in (Google users are refused) → assigned to this event by its organizer, and to this gate if the assignment names one (for a password guard, this must be the one event their password was issued for) → it is within the entry window by server time (the assigned gate is open; for an event without gates, between its start and end times) → the ticket exists (looked up by token hash) → it belongs to this event → it is paid and not revoked → redeem it.

Redeem with one atomic update:
    UPDATE tickets SET status = 'redeemed', redeemed_at = now()
    WHERE id = :id AND event_id = :event_id AND status = 'valid';
1 row updated = admit. 0 rows = already redeemed (or the status changed).

- The endpoint accepts either a QR token or a 6-character short code (case-insensitive; spaces and dashes ignored). A short code is looked up by its keyed hash within this event only, then goes through exactly the same checks and the same atomic update.
- Short code entry is rate-limited per guard (for example, 10 per minute). After 5 invalid codes in a row, lock short code entry for that guard (event_staff.short_code_locked_at) until organizer staff unlock it with POST /api/v1/organizer/events/{id}/staff/{staffId}/unlock-short-codes, and notify the organizer. QR scanning keeps working during a lock.
- Log every attempt in ticket_scans, including rejected ones: result, gate, guard, time and method (qr or short_code).
- The response contains only: the result, the reason, the category, the attendee name where collected, the last 4 digits of the holder's phone or ID number when the event has an identity field, the seat or delegation, and, for a ticket already used, the earlier time and gate.
- Rate-limit by guard.
- Re-entry is not supported: a redeemed ticket never admits again (hurdles.md H7, limitation.md rule 9.10).
```

**Done when:** valid tickets are admitted once, and every rejection reason is returned correctly.

### P6.2 — Simultaneous-scan and rejection tests

```text
Write tests for implementation-plan.md Phase 6 steps 9–10:
- Simultaneous scans: 10 parallel requests from different guards for the same ticket. Exactly one is admitted and nine get "already redeemed". Run against PostgreSQL with real parallel processes.
- Short codes: a valid short code admits once; a QR scan and a short code entry for the same ticket at the same moment admit exactly once; a short code from another event is rejected; 5 invalid codes in a row lock short code entry for that guard, while QR scanning still works.
- One test for each rejection case: wrong event, wrong gate, revoked, unpaid, before the gate opens, after it closes, unknown token, a guard not assigned, a guard whose assignment was removed, a password guard scanning a ticket from a different event, a password guard whose event has closed (their session must already be gone), a Google-signed-in staff member or admin, and the old QR code or short code of a transferred ticket.
- Every attempt appears in ticket_scans.
Show me the output.
```

**Done when:** the simultaneous-scan test passes repeatedly, and every rejection case is covered.

### P6.3 — Guard scanner screens

```text
Build the guard area of the Next.js app, designed for phones first:
- Sign in with the guard email and password → list of assigned events → choose a gate (skipped for events without gates) → full-screen camera scanner. Include a visible "Sign out" button for shift changes on shared phones. When a password guard's event closes, show that their access has ended. Use a maintained browser QR-scanning library and tell me which one you chose and why. Note that camera access requires HTTPS.
- An "Enter code" button for when a QR code won't scan. The guard asks the attendee to show their downloaded or printed ticket and types its 6-character short code on a large keypad with letters and numbers. The result screen shows the attendee name (where collected) and category, so the guard can compare it with the ticket shown. When the event has an identity field, it also shows the last 4 digits of the holder's phone or ID number with a "Check" prompt. If short code entry is locked, say so clearly and point the guard back to QR scanning.
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

Reserved seating comes last in this phase because it is the first item to defer if the schedule slips (SOW §4.1). If it has been deferred by change control, skip P7.3 and P7.4.

### P7.1 — Remaining promotion rules and reports (backend)

```text
Implement implementation-plan.md Phase 7 steps 1–4.
- Complete any promotion rules from SOW §2.8 that P3.4 didn't cover, plus redemption tracking.
- Organizer report endpoints (own events only): sales by offering, payment status, remaining capacity, promotion performance, attendance, and gate activity by time and gate.
- Admin report endpoints: platform-wide summaries, transaction oversight, and an audit log search filtered by actor, organizer, action and date.
- Settlement (hurdles.md H1): a report per organizer and event (sales, refunds, Pesapal fees, platform fee and amount owed) for admins, and for organizer staff for their own organizer; and a payout log where admins record each manual payout. The platform fee calculation is still open (decision #2): ask me before building that column.
- A read-only audit list for organizer staff, for their own events (GET /api/v1/organizer/events/{id}/audit-logs).
- Guest list endpoints (v1.md §5): GET /api/v1/organizer/events/{id}/attendees with filters for ticket category, delegation, purchase date and check-in status, and GET /api/v1/organizer/events/{id}/attendees/export, which streams the same filtered list as CSV. Organizer staff can use them for their own events and event owners for their assigned events only (doc/roleandprivileage.md). For event owners, every row and CSV export contains only the holder's name, ticket category, delegation and check-in status. Event owners get no other report endpoint.
- A sales list CSV export for organizer staff (GET /api/v1/organizer/events/{id}/reports/sales/export).
- Reports must match the order and payment records exactly. Write tests that compare report totals with direct sums.
- Tenant-isolation tests for every report endpoint, including that an event owner can export only their assigned events' guest lists.
```

**Done when:** report figures match the records, and organizers only ever see their own events.

### P7.2 — Report screens

```text
Build report screens in the Next.js app for organizers, event owners and admins, using the P7.1 endpoints:
- Summary cards, tables with filters, and simple charts where they help: sales over time and attendance by gate.
- An audit log viewer for admins, and a read-only audit list per event for organizer staff.
- Settlement screens for admins and organizer staff, and a payout log form for admins.
- A guest list screen with the P7.1 filters and an "Export CSV" button, in the organizer area for each event and as the only screen in the event owner area.
- Every number is labeled with its unit and time range.
```

**Done when:** organizers and admins can answer "how many sold, how much paid, how many admitted" from the screens, and an event owner can filter and export the guest list of their assigned event.

### P7.3 — Reserved seating (backend)

```text
Implement reserved seating as described in implementation-plan.md Phase 7 steps 5–9.
- Organizers manage sections, rows and seat numbers, including bulk creation (for example, row A, seats 1–30). No visual seat-map editor: it's out of scope (SOW §9).
- Seat states: available, held, sold and released. Seat holds are inventory_reservations rows with seat_id set, so they reuse the hold mechanism from P3.1 and the expiry job from P3.3.
- During checkout for a seated event, the buyer picks specific seats, and the hold covers both the offering capacity and the seats, in one transaction.
- The partial unique index ensures one active ticket per seat per event.
- The seat appears on the ticket and in the scan response.
- Events without seating need no seat setup.
- Tests, including a simultaneous test where two buyers choose the same seat and exactly one gets it.
```

**Done when:** a seat can never be sold twice, and expired holds release seats.

### P7.4 — Seat selection screens

```text
Build:
- Organizer area: a seating setup screen (sections and rows, bulk seat creation, a seat list with states).
- Public checkout: seat selection, shown as an accessible grid by section and row with clear available, held and selected states, and fully usable with the keyboard. Seats taken by someone else while the buyer is choosing show a clear message.
```

**Done when:** a buyer can pick seats, and the chosen seats appear on their tickets.

**Then run the [phase exit check](#phase-exit-check) for Phase 7.**

---

## Phase 8 — Testing, deployment and handover

### P8.1 — End-to-end tests

```text
Write Playwright end-to-end tests for the full journey, running against staging-like settings with the FakeProvider:
1. An organizer creates and publishes an event with two offerings.
2. A buyer purchases tickets (one simulated M-Pesa payment and one simulated card payment), receives the email (captured in test mode) and opens the tickets.
3. A guard signed in with a guard password account scans each ticket: admitted. One ticket is admitted by typing its short code instead. Scanning one again: "already used".
4. The organizer sees the sale, the payment and the attendance in the reports.
5. An admin refund revokes the tickets, and a scan then shows "invalid".
6. A ticket is transferred, its old QR code is rejected and the new one admits.
7. When the event's last gate closes, the password guard is signed out and can't sign in again.
Add them to CI as a separate job.
```

**Done when:** the full journey passes in CI.

### P8.2 — Security review

```text
Review the whole codebase for security, as described in implementation-plan.md Phase 8 step 3, then fix what you find.
Check: authorization on every route (compare against v1.md §3); tenant isolation; the Google sign-in flow (state check, verified emails only, linking by Google account ID, session regeneration, no open redirects); guard password sign-in (hashing, rate limiting, lockout, identical errors, forced password change, guard-only scope, and deletion of the password when the event closes); that only guard accounts can scan; that the paying account from Pesapal is never stored or logged; mass assignment; input validation; SQL injection; XSS in both frontends; CSRF; CORS; session and cookie flags; rate limits on the sign-in callback, resend, scan and webhook endpoints; webhook forgery; secrets in the repository or logs; personal data in logs; security headers; and dependency vulnerabilities (composer audit, pnpm audit).
First report your findings ranked by severity, each with its location and the fix. Wait for my go-ahead, then fix them one by one, adding a test for each.
```

**Done when:** no critical or high findings remain open.

### P8.3 — Performance tests

```text
Write k6 scripts for the performance targets recorded in implementation-plan.md Phase 0 step 6, sized to the client's largest expected event and peak buyer numbers (hurdles.md H13):
- A checkout burst: many buyers ordering from one event at the same moment (FakeProvider for payments).
- Scan throughput: several guards scanning in parallel.
Report response times (median and 95th percentile) against the targets, and identify the slowest queries. Suggest indexes or fixes and apply them after I approve.
```

**Done when:** the targets are met on staging, or the gaps are documented with a plan.

### P8.4 — Monitoring and operations

```text
Set up production monitoring as described in implementation-plan.md Phase 8 steps 5–6:
- Error tracking for the API and the Next.js app, with personal data scrubbed.
- Health and heartbeat checks suitable for an external uptime monitor.
- Alerts for: failed queued jobs, a scheduler that has stopped, payments stuck in pending longer than a threshold, reconciliation mismatches, and email send failures.
- doc/runbook.md: how to respond to each alert, how to restore a backup into staging, and how to rotate payment and email credentials.
```

**Done when:** each alert has been triggered on staging and reached the right people, and the runbook covers each one.

### P8.5 — Go-live checklist and handover documents

```text
Prepare the go-live and handover documents described in implementation-plan.md Phase 8 steps 7–11:
- doc/go-live-checklist.md: production environment variables, cron, HTTPS on all subdomains, Sanctum production domains, the production Google OAuth client with its callback URL and published consent screen, production Pesapal keys and the production IPN registration, email DNS records, backups confirmed, monitoring active, the privacy notice and the deletion-on-request procedure in place (hurdles.md H12), every hurdle in hurdles.md Decided or accepted, limitation.md signed off, the smoke test, and one real low-value payment with each method followed by a refund.
- Update doc/deployment.md and the OpenAPI contract so they match the final code.
- A known issues list for the handover, starting from the known limitations in hurdles.md §5 and limitation.md Part B.
- doc/admin-guide.md and doc/organizer-guide.md: plain-language guides with the main tasks step by step.
- A test results summary for deliverable D11, mapping each SOW §5 acceptance criterion to the tests that prove it.
```

**Done when:** every SOW §5 criterion maps to evidence, and the guides cover every main task.

**Then run the [phase exit check](#phase-exit-check) for Phase 8.** UAT, client sign-off and payment milestone M4 follow.
