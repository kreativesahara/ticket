# AI Build Prompts: Event Ticketing Platform

Ready-to-paste prompts for an AI coding assistant (such as Claude Code or Cursor) to build the platform in the order set out in [implementation-plan.md](implementation-plan.md).

## How to use these prompts

1. **Finish Phase 0's human tasks first.** Settle the decision log (implementation-plan.md §11), work through the open hurdles in [hurdles.md](hurdles.md) (H1–H5 first, because they change the database and payment design), get the business rules in [limitation.md](limitation.md) signed off, submit the Pesapal merchant application, and create the Supabase development and test projects and the local Google OAuth client (see [Environments](#environments)). Several prompts depend on those answers, and each one names the hurdles it depends on.
2. **Give the assistant the project context.** Paste [the context block](#project-context) at the start of every new session. Alternatively, save it in your tool's project instructions file (`CLAUDE.md` for Claude Code) so it loads automatically. The backend repository already has it. Give the frontend repository a copy that points to this repository for the specification and to the published OpenAPI contract.
3. **Run one prompt at a time, in order.** Review the changes and the test results. Don't move on until the prompt's **Done when** checks pass.
4. **Commit after every prompt**, so any step can be undone on its own.
5. **Run the [phase exit check](#phase-exit-check) at the end of every phase** before starting the next.

Prompt IDs follow the plan: **P3.2** is Phase 3, prompt 2.

---

## Environments

Development is local until Phase 8. Nothing is deployed before then: no staging, no cPanel and no Vercel. Where implementation-plan.md says "on staging" before Phase 8, read "locally"; staging is first deployed in P8.2.

| What           | Phases 0–7                                                                                   | Phase 8                                          |
| -------------- | -------------------------------------------------------------------------------------------- | ------------------------------------------------ |
| API (Laravel)  | `localhost:8000`, with the scheduler (`schedule:work`) and the queue worker running locally   | cPanel, with the scheduler and queue run from cron |
| Next.js app    | `localhost:3000`                                                                             | Vercel                                           |
| Database       | The Supabase **development** project (online)                                                | A new Supabase **staging** project, then production |
| Tests          | The Supabase **test** project (online), never the development project. CI uses its own PostgreSQL service | Unchanged                              |
| Public HTTPS   | From P1.8, a tunnel to the local apps (for phones and Pesapal's IPN)                         | Staging and production domains                   |
| Pesapal        | Sandbox, its IPN reaching the local API through the tunnel                                    | Sandbox on staging, then production keys         |
| Email          | A local mail catcher that runs without Docker, such as Mailpit                               | The cPanel host's own mail server (decision #9) |
| Google sign-in | The local OAuth client, with localhost and tunnel callback URLs                              | The staging client, then the production client   |

- Connect to Supabase through its session pooler, with SSL required. The direct connection needs IPv6, and the transaction pooler doesn't support prepared statements.
- The test suite wipes its database, so it refuses to run against anything but the test project (P1.1). If more than one developer runs tests, give each their own test project. Never point CI at a Supabase project: two runs on the same database wipe each other's data.
- Supabase pauses free-plan projects after a period without activity. Restore them from the Supabase dashboard.
- The tunnel (P1.8) uses two HTTPS hostnames on one domain: one for the API and one for the Next.js app. That way the session cookie works across them as it will across `www` and `api` in production. It's needed for the printed QR code (P2.8), Pesapal's IPN (P4.3) and phone cameras (P6.3).

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
- hurdles.md — known gaps (H1–H14), each marked Open or Decided. An open hurdle is an undecided requirement: don't guess, ask me.
- limitation.md — the client-facing business rules (Part A), accepted limitations (Part B) and client decisions (Part C). If it disagrees with v1.md, v1.md wins; tell me about the mismatch.
- doc/roleandprivileage.md — roles, privileges and the authorization safeguards in detail, including the event owner boundaries (§5.1), staff event access and who sees whom (§5.2), and guard safeguards (§5.3). If it disagrees with v1.md, v1.md wins; tell me about the mismatch.

STACK:
- Two repositories. The backend repository (this one) holds /api and the specification; the frontend repository holds the Next.js app.
- /api — Laravel (latest stable release), PostgreSQL on Supabase. Admins, organizer staff and event owners sign in with Google OAuth through Laravel Socialite. Guards sign in only with an email and password account created by their organizer, valid only for their assigned event; only guard accounts scan. Sanctum SPA cookie sessions after sign-in.
- Frontend — one Next.js + TypeScript app with server-side rendering, for every role: the public event site, checkout and ticket retrieval, plus the organizer, event owner, admin and guard areas. shadcn/ui components and Tailwind CSS.
- Payments — Pesapal API 3.0: buyers pay by M-Pesa or card on Pesapal's hosted checkout; Pesapal sends IPN notifications; results are confirmed with Pesapal's transaction status endpoint.
- Environments — until Phase 8 everything runs locally: the API on localhost:8000 (with the scheduler and queue worker), the Next.js app on localhost:3000, and the database on an online Supabase development project. Tests use a separate Supabase test project. Nothing is deployed before Phase 8; where implementation-plan.md says "staging" before Phase 8, it means local.
- Hosting, from Phase 8 — the API on cPanel (scheduler and queue run from cron), the Next.js app on Vercel.

NON-NEGOTIABLE RULES:
1. Tenant isolation: every organizer-owned resource is authorized through a Laravel policy. Organizer-level resources check the authenticated user's organizer membership; anything about a specific event checks their event assignment: manager for organizer staff (other staff of the organizer get only the event's read-only summary), event owner or guard. Only platform admins grant staff access to an event. Event owners may only view, filter and export the guest list of their assigned events, and see who manages them. Never trust role, organizer_id, event_id or gate_id from the request without checking it.
2. Money is stored as integer minor units plus a currency code. Never floats.
3. Inventory holds, payment confirmation, ticket issuance and ticket redemption use database transactions and atomic conditional updates. Never read-then-write checks.
4. Tests run against PostgreSQL (the Supabase test project locally, a PostgreSQL service in CI), never SQLite, and never against the Supabase development project.
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

The hosting check script is now P8.1: nothing is deployed before Phase 8.

---

## Phase 1 — Design and foundation

### P1.1 — Repository scaffold

```text
Set up the two repositories as described in implementation-plan.md Phase 1 step 6, for local development. Nothing is deployed before Phase 8 (see Environments in prompts.md).
- Backend repository (this one): /api, a new Laravel project (latest stable) configured for PostgreSQL on Supabase, with Pest for tests; Laravel Pint and Larastan. No Docker.
- Database connections: the app uses the Supabase development project and the tests use the separate Supabase test project, both through Supabase's session pooler with sslmode=require. Keep the test connection out of phpunit.xml: put it in .env.testing (git-ignored), with a committed .env.testing.example.
- A safeguard that stops the test suite before it touches any database unless it is connected to the test project. For example, check that the project reference in the database username (Supabase pooler usernames are postgres.<project-ref>) matches SUPABASE_TEST_PROJECT_REF, and refuse if .env.testing is missing or names the same project as .env.
- Frontend repository: one Next.js app with TypeScript, the App Router, server-side rendering, Tailwind CSS and shadcn/ui (theme defined as CSS variables), using pnpm; ESLint and strict TypeScript. Route groups for the public site and the /organizer, /event-owner, /admin and /guard areas.
- A README in each repository explaining how to run it locally (in the backend: the API server, the scheduler with `php artisan schedule:work`, and the queue worker; in the frontend: `pnpm dev`), plus .env.example files with no real secrets.

Check that every app starts locally and the default tests pass against the Supabase test project.
```

**Done when:** the API and the Next.js app run locally against the Supabase development project, their default tests pass, and the backend tests refuse to run when pointed at the development project.

### P1.2 — Continuous integration

```text
Add a CI workflow to each repository (GitHub Actions unless I say otherwise). On every push and pull request:
- Backend: install dependencies, run the Pint check, run Larastan, and run the tests against a PostgreSQL service container with the same major version as the Supabase projects. Never point CI at a Supabase project, so CI runs and local runs can't wipe each other's data. Let the P1.1 test safeguard accept the CI service container.
- Frontend: install, lint, type-check, build and run unit tests.
Any failure must fail the build.
```

**Done when:** a test pull request shows every check passing, and a deliberately broken test makes the build fail.

### P1.3 — Database schema and migrations

```text
Using v1.md §4 (every table and column) and doc/state-machines.md, write Laravel migrations for the full MVP schema, including seating, so that later phases need no structural rework.

Include the tables and columns the decided hurdles add: H1 (settlement report and payout log), H3 (zero-total orders and delegation claim codes), H5 (cancellation and postponement), H11 (checkout tokens), H12 (deleting a person's data on request while keeping financial and scan records) and H14 (each delegation's set-aside offerings: event_ticket_offerings.delegation_id), plus the identity field, ticket transfers, event categories and ticket waves (ticket_waves, ticket_categories.sells_in_waves and event_ticket_offerings.wave_id; decision #23). How the platform fee is calculated (decision #2) is still open: store platform_fee_amount on order items, and ask me before adding any fee settings.

Requirements:
- UUID primary keys for records referenced from outside the system.
- A foreign key for every ownership relationship. Choose delete behavior deliberately: never cascade-delete orders, payments, tickets or audit logs.
- The logs (v1.md §4.7; decisions #35–#39): audit_logs keeps actor_user_id, organizer_id and event_id with no foreign keys, so deleting a draft event never has to change an entry. A trigger refuses every UPDATE on audit_logs, and every DELETE unless the session runs as a dedicated retention database role and the entry is older than 5 years; the app's role gets INSERT and SELECT only. Create that role in a migration, and check that Supabase's session pooler accepts it; ask me if it doesn't. Add reconciliation_runs (v1.md §4.4). Test that the app can't update or delete an audit entry, and that deleting a draft event leaves its audit entries untouched.
- Unique constraints: user email; event slug (across the platform: it forms the event's link, /events/{slug}); ticket_number; qr_token_hash; (event_id, short_code_hash); (provider, provider_reference) on payments, where the reference is Pesapal's order tracking ID; idempotency_key; one active ticket per seat per event (a partial unique index); and one offering per ticket type in each wave, per type and category among an event's ordinary public offerings, and per type and category in each delegation (partial unique indexes).
- An exclusion constraint so a category's waves never overlap (EXCLUDE USING gist, with the btree_gist extension), and a CHECK so an offering has at most one of wave_id and delegation_id. Pesapal notifications carry no unique event ID, so don't add a unique key on webhook events; idempotency comes from the payment and order checks in P4.2.
- CHECK constraints where they're cheap: non-negative counts and amounts, capacity - sold_count - reserved_count >= 0, and an invitation has no organizer exactly when its role is platform_admin.
- Money as bigint minor units plus a char(3) currency column.
- Status columns as strings backed by PHP enums, one enum per state machine in doc/state-machines.md.
- event_staff.role is manager, guard or event_owner (decision #31), and gate_id is set only on guard rows.

Also create Eloquent models with relationships and enum casts, a factory for every model, and update the ERD in doc/diagrams/erd/ (each .mmd file, its .md file and the rendered images; see doc/README.md) so it matches the final schema.
Enable btree_gist in a migration (Supabase supports it), and make sure migrate:fresh touches only the public schema, never Supabase's own schemas (auth, storage and the rest).
Run the migrations from scratch against the Supabase development project and show the output.
```

**Done when:** `migrate:fresh` succeeds on the Supabase development project and in the test suite, every model has a factory, and the ERD in doc/diagrams/erd/ matches the migrations.

### P1.4a — Google sign-in

```text
Implement sign-in with Google as described in implementation-plan.md Phase 1 step 9 and the sign-in rules in techstack.md §3. Use Laravel Socialite for Google OAuth and Sanctum SPA cookie sessions after sign-in. Passwords exist only for guard password accounts (P1.4b); there is no self-registration, email verification or email-based password reset.

- Web routes, not under /api/v1, because they need the session for Google's state check: GET /auth/google/redirect and GET /auth/google/callback. Request only the openid, email and profile scopes. Don't store Google's access or refresh tokens.
- On callback: reject Google accounts with unverified emails. Find the user by users.google_id, looking only at Google users. Never link a Google sign-in to a guard password account: they are separate users even when the email is the same (users.email is unique per sign_in_method). If there's no Google user yet, create one with their Google ID, name and email only when a pending, unexpired invitation is addressed to that verified email (matched case-insensitively); otherwise refuse the sign-in and create nothing (decision #7). Reject suspended users. Refuse an existing user who holds no role (platform admin, active staff or active event owner) and has no pending invitation. Log the user in, regenerate the session, and redirect to the Next.js app, accepting only an allowlisted return path (no open redirects).
- API endpoints: POST /api/v1/auth/logout (invalidates the session) and GET /api/v1/auth/me (the user, platform role, organizer memberships with roles, and event assignments as guard or event owner).
- Suspending a user ends their existing sessions.
- A one-time setup command for the very first platform admin (v1.md FR-01, decision #30): `php artisan platform:bootstrap-admin {email}` creates a platform_admin invitation for that email (no organizer, no inviting user) and prints its single-use link. It refuses to run once any platform admin exists. Every later admin is invited by an existing admin (P1.5); no one becomes an admin through sign-in. Audit its use.
- Configure stateful domains, the session cookie domain, CORS (with credentials) and CSRF for the www and api subdomains through environment variables. Requests that the Next.js server makes during server-side rendering forward the user's session cookie and Origin header, and must be accepted as the same session. The Google client ID, secret and callback URL also come from environment variables. Document the values for local development, including the localhost callback URL to register in the Google OAuth client.
- Rate-limit the callback route.
- Sign-in history (decision #38): write an audit entry for every successful sign-in, sign-out and refused sign-in (unverified email, suspended user, tampered state, no account or no role), with the method, IP address and browser.
- Feature tests with Socialite mocked: an invited new user, returning user, a stranger with no invitation (refused, nothing created), an expired or accepted invitation, a user whose roles have ended, unverified email, suspended user, tampered state and a disallowed return path, each leaving the right audit entry.
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
- Sign-in history (decision #38): write an audit entry, against the guard's event, for every successful sign-in, sign-out, failed attempt and lockout, with the IP address and browser. A failed attempt for an unknown email is recorded without the email.
- Feature tests: successful sign-in; wrong password and unknown email give the same error; lockout; each of these leaves the right audit entry; forced password change; suspended account; a closed event refuses sign-in and ends an existing session; adding a password account as an organizer member or event owner is rejected; a Google user and a guard account with the same email stay separate and each signs in only through its own route; session expiry.
```

**Done when:** all tests pass, and a factory-made guard can sign in from the Next.js app, is forced to change their password, and is signed out once their event closes.

### P1.5 — Tenancy, roles, policies and audit log

```text
Implement tenancy and authorization as described in implementation-plan.md Phase 1 steps 10, 11 and 13.

- Organizers, and organizer members with the role staff (v1.md §3 and §4.1). Staff manage their organizer account (profile, verification details, staff invitations and guard accounts) and can create events; only platform admins enter, change or see the payout account (P2.5). They manage a specific event (its tickets, delegations, guard and event owner assignments, orders and reports) only with event access: an event_staff row with role manager, created automatically for the event's creator (P2.1) and otherwise only by a platform admin (decision #31). Organizers can also assign event owners to specific events (event_staff with role event_owner), whose role and privileges are strictly restricted to viewing, filtering, and exporting guest/attendee data. Platform admin is users.platform_role with full system-wide rights and privileges across all operations.
- A policy for every model. Platform admins pass with full rights across all resources, except scanning, which only guard accounts can do. Organizer staff pass for their own organizer's organizer-level resources, and for a specific event's resources only when they manage it; for the organizer's other events they get only the read-only summary (v1.md §3, rule 8). Event owners pass only to view/filter/export attendee data and to list the managers of their assigned events.
- A safeguard so no route can skip authorization, for example a test that fails if any /api/v1 route has no policy check.
- An AuditLogger service recording the actor, organizer, event, action, resource type and ID, metadata and time. It never writes a buyer's or attendee's name, email, phone number or ID digits, or the text of a search, into metadata: it refers to records by ID (decision #36). It records the IP address and browser only for signed-in users' actions.
- A retention job (decision #35, v1.md §4.7) that runs daily under the retention database role from P1.3 and deletes audit entries, payment notifications and reconciliation runs older than 5 years, scan records 12 months after their event ends, and processed outbox messages and failed queue jobs older than 90 days. It records each run (how many rows it deleted from each table), and P1.8's health endpoint reports when it last ran. Test each period with backdated rows.
- Endpoints: POST /api/v1/admin/organizers (platform admins only; decision #7) creates an organizer and, in the same transaction, an invitation for its first staff member's email, audited; there are no organizer applications. List my organizers, invite a staff member or event owner (staff of the organizer, and platform admins, who can also resend an expired first invitation), and remove a member (refused with a conflict while they are the only manager of a published event; decision #44). Tests: organizer staff, event owners and guards can't create organizers.
- Invitations, for organizer staff, event owners and platform admins only (guards get password accounts in P2.4, never invitations): the invitations table from v1.md §4.1, with the token stored hashed, an expiry date and single use. POST /api/v1/invitations/{token}/accept succeeds only when the signed-in user's verified Google email matches the invited email.
- Platform admins (v1.md FR-01, decision #30), for platform admins only: GET /api/v1/admin/admins lists them; POST /api/v1/admin/invitations invites a new admin by email; accepting that invitation sets users.platform_role; DELETE /api/v1/admin/admins/{userId} removes a user's admin rights, and refuses to remove the last admin (lock the admin rows so two admins removing each other at the same moment can't leave none). Audit every invitation, grant and removal. Tests: organizer staff, event owners and guards are refused; a non-matching Google email can't accept an admin invitation; the last admin can't be removed, even by two simultaneous requests; the setup command from P1.4a refuses once an admin exists.
- Event access (decision #31), for platform admins only: POST /api/v1/admin/events/{id}/managers with a user_id that must be an active staff member of the event's organizer (never a guard account), and DELETE /api/v1/admin/events/{id}/managers/{userId}, which is refused with a conflict when it would leave a published event with no manager (decision #44). Both are audited. Removing a staff member from the organizer ends all their event access in the same transaction.
- Who sees whom (decision #32, v1.md §3, rule 9): GET /api/v1/admin/users lists every user, for platform admins only (paginated, searchable by name and email, and filterable by organizer to list its staff). GET /api/v1/organizer/members lists only the current organizer's staff (name, email and status). GET /api/v1/organizer/events/{id}/managers lists an event's managers (name and email) for the organizer's staff and the event's event owners. No other endpoint returns people outside these scopes: an event's event owners and guard assignments appear only to its managers.
- A reusable tenant-isolation test helper: for a given endpoint, it asserts that a user from a different organizer is refused on read, update and delete, that a staff member of the same organizer who doesn't manage the event gets at most its read-only summary, and that an event owner is refused on every endpoint except the guest list and the managers list of their assigned events. Apply it to every endpoint built so far.
```

**Done when:** the tenant-isolation tests pass for every endpoint, including for staff without access to the event, and the route-coverage safeguard passes.

### P1.6 — API contract and conventions

```text
Write /api/docs/openapi.yaml covering every MVP endpoint, using the endpoints and paths in v1.md §5. Include request and response schemas for each.

Then implement the conventions in /api:
- One JSON error format for validation, authentication, authorization, not found, conflict, rate limit and server errors.
- One pagination format with a standard response envelope.
- A request correlation ID: accept X-Request-Id or generate one, return it in the response, and include it in every log line. Log lines never contain request bodies or personal details (decision #36); application logs are kept 90 days (decision #35).

Add any endpoint v1.md §5 doesn't list yet, under its prefix rule, and update v1.md §5 to match.

Guest buyers have no account, so an order must never be readable by its ID alone (hurdles.md H11). POST /api/v1/orders returns a random checkout token once, stored hashed; the buyer's checkout calls (order status, apply promotion, start payment) must send it. Tickets are reached only through the retrieval link. The managers of the order's event and admins view orders at GET /api/v1/organizer/orders/{id}.

Add a test that checks the implemented endpoints match the contract (route list against the OpenAPI paths).
```

**Done when:** the contract covers every MVP endpoint, and the error format, pagination and correlation ID are implemented and tested.

### P1.7 — Frontend foundation

```text
Build the frontend foundation described in implementation-plan.md Phase 1 steps 14–16, in the frontend repository's Next.js app.

- shadcn/ui components we'll need first: button, input, label, form, card, dialog, table, toast, dropdown menu, badge, select and tabs.
- A typed API client generated from openapi.yaml, usable from server components and the browser. In the browser it sends cookies and calls /sanctum/csrf-cookie before any state-changing request; on the server it forwards the user's session cookie and Origin header. It turns the standard error format into typed errors.
- A mock mode driven by the OpenAPI spec (for example with MSW), so screens can be built before their endpoints exist.
- Pages: "Sign in with Google"; "Guard sign-in" (email and password), linked from it, with the first-sign-in password change screen; an invitation page (/invite/{token}) that sends the user through Google sign-in and then accepts the invitation; a clear "no access" message when the API refuses a Google sign-in (error=no_access), telling the person to ask a platform admin or their organizer for an invitation (there is no organizer application; decision #7); and logout. Route users by role to the /organizer, /event-owner, /admin and /guard areas (the event owner area shows only the guest list of their assigned events), inside an app shell with a sidebar, a header and an organizer switcher for users who belong to more than one organizer. Protect each area in Next.js middleware as well as in the API.
- The public site layout, header, footer and a placeholder home page.
- Component tests for both sign-in pages, the password change screen, the invitation page and the no-access message.
```

**Done when:** you can sign in with Google to the Next.js app against the real local API (mock mode uses a fake signed-in user), and you land in the right area for your role.

### P1.8 — Local working skeleton

```text
Make the whole system run end to end locally, as Phase 1's working skeleton. implementation-plan.md Phase 1 step 17 describes it on staging; here it runs locally, and staging follows in P8.2.

- A /api/v1/health endpoint that checks the database connection and reports when the scheduler, the queue and the retention job (P1.5) last ran. Add it to the OpenAPI contract.
- One command in each repository starts what it needs: in the backend, the API server, the scheduler (`php artisan schedule:work`) and the queue worker; in the frontend, the Next.js dev server.
- A tunnel with two HTTPS hostnames on one domain: one for the API and one for the Next.js app (for example, Cloudflare Tunnel). With the session cookie domain, Sanctum's stateful domains, CORS and the Google callback URL set for those hostnames, sign-in and the session must work across them as they will across www and api in production. Make it easy to switch between the localhost and tunnel settings. Before setting it up, tell me which tunnel you chose and what it needs from us (an account, a domain).
- doc/local-development.md: every environment variable and where its value comes from (the Supabase development and test projects, the local Google OAuth client with its localhost and tunnel callback URLs, and the tunnel), how to start everything, how to create the first local platform admin with the P1.4a setup command, and how to reset the development database.
No real credentials in any file.
```

**Done when:** on a fresh clone, following doc/local-development.md starts everything; Google sign-in works on localhost and through the tunnel; the session holds across the two tunnel hostnames, including during server-side rendering; and the health endpoint shows the database connected and the scheduler and queue running.

**Then run the [phase exit check](#phase-exit-check) for Phase 1.**

---

## Phase 2 — Events and ticket configuration

### P2.1 — Event lifecycle and details

```text
Implement events in /api as described in implementation-plan.md Phase 2 steps 1–3.

- Create, update, list and show events for organizers; public list and show return published events only.
- Event access (decision #31): creating an event makes its creator a manager of it (an event_staff row with role manager, in the same transaction, audited). GET /api/v1/organizer/events lists every event of the organizer and marks the ones the user manages. GET /api/v1/organizer/events/{id} returns the full event to its managers, and to other staff of the organizer only the read-only summary: details and ticket setup, with no sold counts, orders, payments, guest data, reports, audit records, delegations, promotions or assignments. Every other organizer endpoint for an event requires a manager or a platform admin.
- Events created by a platform admin (decision #34): POST /api/v1/admin/organizers/{id}/events, for platform admins only, takes the same fields as POST /api/v1/organizer/events plus optional manager_user_ids. The organizer must not be suspended, and every manager must be an active staff member of that organizer with a Google sign-in (never a guard account or someone else's staff). In one transaction, create the draft event and a manager row for each chosen user; the admin gets no manager row. Audit the creation and each grant. Tests: the chosen staff manage the event and other staff of the organizer see only its summary; a user who isn't the organizer's staff, a guard account and staff of another organizer are refused as managers, and nothing is created; a suspended organizer is refused; organizer staff and event owners can't call the endpoint; with no managers chosen, only admins can change the event.
- States follow the event state model in v1.md §4.6 and doc/state-machines.md (draft, published, closed, cancelled, archived), with transitions enforced by an enum and a single service. Every transition is audited.
- A scheduled job runs every minute and moves published events to closed once their last gate closing time has passed, or their end time if they have no gates. No user can set or undo closed. Closed events can't be bought or scanned, and drop out of the public list.
- DELETE /api/v1/organizer/events/{id} deletes a draft that was never published, with an audit log entry. Any other event returns a conflict response: once published, an event can only be archived (from closed or cancelled), never deleted.
- Cancelling (hurdles.md H5): revoke every valid and pending-issuance ticket, cancel pending orders, end guard access, and write outbox records to email every buyer (P5.2 sends them). Admins refund the orders afterwards (P4.4). Changing a published event's dates is a postponement: tickets stay valid, staff confirm the change and enter refund_requests_until, guided by the event's refund policy (decision #48), and buyers are emailed a refund-request link.
- Name, slug (unique across the platform; it forms the event's link, /events/{slug}), description, venue, timezone (an IANA name), the identity field (none, phone or id_number; see P2.3), capacity, refund policy, a category from the admins' list, an optional presented_by name (such as a sponsor or host, printed with the organizer's name on tickets, receipts and ticket emails; decision #43), the captcha_required flag and the event image (next bullet).
- Event image (v1.md FR-03, decision #46, techstack.md §6 "Event images"): POST /api/v1/organizer/events/{id}/image (multipart) and DELETE /api/v1/organizer/events/{id}/image, for the event's managers and platform admins, audited. Accept JPEG, PNG or WebP of at most 10 MB and 25 megapixels, judged by decoding the file, never by its name or declared type. Never store the upload: re-encode it with Intervention Image on the GD driver (upright from EXIF, sRGB, transparency flattened onto white, longest side at most 1,920 px and never enlarged, all metadata stripped), as a progressive JPEG at quality 82, lowering the quality to 60 and then shrinking 20% at a time until it is under 500 KB (512,000 bytes); refuse it with a validation error if it still doesn't fit. Put the processing in one service class. Store the result in the public Supabase Storage bucket through Supabase Storage's HTTP API, not its S3-compatible endpoint: one small class uses Laravel's HTTP client to upload and delete objects and build their public URLs, authenticated with the project's secret key from the backend environment. Save it as events/{event_id}/{uuid}.jpg with Cache-Control "public, max-age=31536000, immutable", set image_path, and delete the previous image once the new one is saved. Add SUPABASE_URL, SUPABASE_SECRET_KEY and SUPABASE_STORAGE_BUCKET to .env.example without values, log intervention/image in doc/dependencies.md, and extend tools/hosting-check.php to check GD's JPEG support, a memory_limit of at least 256M, and upload_max_filesize and post_max_size of at least 10M.
  Tests, with Http::fake so no test reaches a real bucket: a large photo comes out as a JPEG under 500 KB and within 1,920 px; a small image isn't enlarged; EXIF data, including GPS, is gone; a PNG with transparency becomes a JPEG; a text file renamed .jpg, an SVG, a GIF, a file over 10 MB and an image over 25 megapixels are refused; replacing and deleting the image send a delete request for the old object; staff who don't manage the event and staff of another organizer are refused (tenant isolation).
- Separate time windows, each validated: sales start and end, gate opening and closing, event start and end. Store times in UTC and return them together with the event's timezone.
- Policies, tenant-isolation tests and feature tests for every rule, including: a staff member who didn't create an event and wasn't given access sees only its summary, which holds no sales figures, and can't change it.
```

**Done when:** the lifecycle and time-window rules are tested, events close automatically when their last gate closes (or at their end time without gates), the public endpoints never return draft or closed events, and every uploaded event image is stored as a JPEG under 500 KB.

### P2.2 — Ticket types, categories, offerings, waves and delegations

```text
Implement ticket configuration as described in implementation-plan.md Phase 2 steps 4–6.

- Ticket types and categories for each event. Seed the defaults (Early Bird, Normal, At the Gate, Group, Promoted; VVIP, VIP, Media) and let organizers add, rename and disable their own. Each category has sells_in_waves, which can change only while nothing in that category has been sold or held.
- Offerings: each allowed type × category combination, with a price in minor units that must be a whole number of shillings (a multiple of 100; decision #41), a currency, capacity, a sales window, a minimum and maximum per order, and a status. Group types define their required quantity. Media can be flagged "approval required".
- Ticket waves (decision #23, v1.md FR-04): POST /api/v1/organizer/events/{id}/waves, PATCH and DELETE /api/v1/organizer/waves/{id}, for categories with sells_in_waves on. A wave has a name, a position and start and end dates; the exclusion constraint from P1.3 refuses overlapping waves in the same category. A wave holds one or more offerings (type, number of tickets, price), created with POST /api/v1/organizer/events/{id}/offerings and a wave_id. Their sales window is the wave's dates and changes with it in the same transaction. Every public offering of a wave category must belong to a wave. A wave can be deleted only while nothing in it has been sold or held. Waves open on their start dates only: no automatic advance and no rollover.
- Moving unsold tickets: POST /api/v1/organizer/offerings/{id}/move-unsold, with the target offering and a number of tickets. Both offerings must be in the same event and category. In one transaction, lower the source's capacity with UPDATE event_ticket_offerings SET capacity = capacity - :n WHERE id = :from AND capacity - sold_count - reserved_count >= :n, refuse if no row changed, then raise the target's capacity. Audit every move.
- Delegations (hurdles.md H3 and H14, doc/sponsored.md): name and status (active or paused), and a private claim link and access code for each, stored as a hash plus an encrypted copy so staff can show them again. A delegation's tickets are its set-aside allocation: offerings created with its delegation_id (type, category, number of tickets and price), never in a wave. Public endpoints never list them, and public checkout can't buy them. Staff return unclaimed tickets to public sale with the move action.
- Validation: the total capacity of all offerings, public and set aside, can't exceed the event's capacity, and an offering's capacity can't drop below what has been sold plus what is held.
- At the Gate tickets (hurdles.md H4) are ordinary offerings that buyers buy on their own phones through the public site. No box office and no cash sales.
- Policies, tenant-isolation tests and feature tests, including: overlapping waves are refused; a wave's offerings can't be bought outside its dates; a move racing a hold never leaves an offering below what it has sold and holds; and a delegation's offerings never appear on public endpoints.
```

**Done when:** offerings, waves and delegations can be created and edited within their capacity rules, waves never overlap, and every rule is tested.

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
- Guard password accounts (techstack.md §3): POST /api/v1/organizer/guards creates the account (name and the guard's own email) with managed_by_organizer_id set to the creating organizer. Guards never use Google; the email may match a Google user's, and the two stay separate users. A password account is assigned to one event at a time. POST /api/v1/organizer/guards/{id}/password issues a temporary password for the guard's current assignment: it creates a guard_credentials row, returns the password once in the response and never again, and revokes any earlier credential and its sessions. PATCH /api/v1/organizer/guards/{id} disables or re-enables the account. Any staff member of the owning organizer, or a platform admin, can create or disable a password guard; only an event's managers, or a platform admin, can assign a guard to that event or issue a password for that assignment.
- Revocation when the event closes: a scheduled job runs every minute. For every event that has closed (entry ended, or cancelled or archived), it deletes the password hash of each of its guard credentials, sets revoked_at, deletes the guards' sessions, ends their assignments and writes an audit log entry. Cancelling or archiving an event triggers the same revocation immediately. Keep the guard's user record, so scan records still name them. Every guard assignment for the event ends.
- Assignments link a guard to an event and, optionally, a gate. An event can have several guards. The event's managers can remove assignments. Assignment changes are audited.
- Event owner assignments: accepting an event_owner invitation creates an event_staff row with role event_owner and no gate. The event's managers can list and remove its event owners; removal ends their access to that event immediately and is audited. Archiving an event ends every event owner assignment for it in the same way (doc/roleandprivileage.md §5.1.5); closing or cancelling it doesn't.
- An endpoint for a guard to list only their current assignments.
- Tests: a guard sees only their own assignments and can't reach any organizer endpoint; an event owner sees only their assigned events, is refused on every organizer endpoint except the guest list, and can't invite users or change any role; archiving an event ends its event owners' access; an expired or reused event owner invitation fails; signing in with a different Google email doesn't accept it; another organizer can't assign or issue a password for a password guard; a staff member who doesn't manage an event can't assign guards or event owners to it, or see them; an event owner sees the event's managers and no other users; a temporary password can't be retrieved after it's issued; a password account can't be assigned to a second open event; once the last gate closes, the password hash is gone, the guard can't sign in and their session has ended; cancelling an event revokes its guard passwords immediately; and the guard's past scans still show their name.
```

**Done when:** guard account creation and assignment work end to end, event owner invitations work, and guards are confined to their assignments.

### P2.5 — Publishing rules and admin controls

```text
Implement publishing rules and admin controls as described in implementation-plan.md Phase 2 steps 9–10.

- An event can be published only when it has at least one active offering, valid and consistent time windows, capacity that covers all allocations, and, if any offering has a price above zero, a verified organizer with a payout account on file (decision #33). If not, return every failing rule at once. A missing payout account is one of those rules: its message tells staff that a platform admin must add the payout details, and the event stays in draft until then. Adding a paid offering to a published event is refused on the same rule.
- After publishing: audit every change, and block or require explicit confirmation for changes that affect existing orders (lowering capacity below sold, changing the price of an offering that has sales, moving the event's dates).
- Organizer verification (hurdles.md H2): organizers submit business or ID details; an admin verifies or rejects them.
- The payout account (decision #33): only platform admins enter, change or read it, through PUT /api/v1/admin/organizers/{id}/payout-account (stored encrypted, every change audited). Staff send the details to an admin outside the platform. Organizer endpoints return only whether a verified payout account is on file, never its details. Verifying the organizer and entering its payout account are separate admin steps; publishing a paid event needs both.
- Event categories: admins create, rename and disable categories; the public list endpoint returns active ones.
- Admin endpoints: list organizers, suspend and reactivate organizers (suspension unpublishes nothing but blocks new sales and the sign-ins of its staff, event owners and guards, and ends their current sessions at once; doc/roleandprivileage.md §5.3), and view any event, with the access audited.
- Tests for each rule, including: suspending an organizer signs out its staff, event owners and guards immediately; organizer staff can't read or change the payout account, and no organizer endpoint, log line or audit entry contains its details; a paid event stays in draft until a platform admin enters the payout account and then publishes; a free event publishes without one; a paid offering can't be added to a published event while no payout account is on file.
```

**Done when:** publishing refuses incomplete events with clear reasons, and suspended organizers can't sell.

### P2.6 — Organizer and admin dashboards

```text
Build the organizer and admin areas of the Next.js app for Phase 2, using shadcn/ui components.

Organizer:
- Event list with status badges and filters, showing every event of the organizer. Events the user doesn't manage open as a read-only summary with no edit actions and no sales figures.
- The organizer's staff list, and on each managed event the list of its managers (read-only: only admins change them).
- A step-by-step event editor: details (including the event image: scale it down to at most 1,920 px and compress it to a JPEG under 500 KB in the browser before uploading, show a preview, and show the API's errors; the API checks everything again, decision #46) → times → tickets (an offerings grid of type × category, a "sell in waves" switch for each category, and a wave editor: each wave's dates, then its rows of type, number of tickets and price, with upcoming, on-sale and ended waves labelled) → attendee fields → gates and staff → review and publish. The publish step shows the API's failing rules if there are any.
- A "Move unsold tickets" dialog: choose the source, the target in the same category and the number to move.
- Delegation management with claim links, access codes and set-aside allocations (type, category, number of tickets and price); staff and event owner invitations, and each event's event owners with a remove action; guard password accounts (create with the guard's email, assign, "Issue password" with a one-time copy dialog, disable); and guard assignments, showing when each password guard's access ends.

Admin:
- A "New organizer" form (name, contact email and the first staff member's email; decision #7), and an organizer list with suspend and reactivate actions behind a confirmation dialog, the organizer verification queue, a payout account form on each organizer, and the event category list. The organizer area shows only whether a verified payout account is on file.
- Platform admin list, with an "Invite admin" form (email) and a remove action behind a confirmation dialog, disabled for the last admin.
- A user list with search by name or email, and on each event an "Event access" panel to grant a staff member of its organizer access, or remove it, behind a confirmation dialog.
- A "New event for an organizer" form: choose the organizer, pick its managers from that organizer's staff in the user list, and enter the event's details. It creates the draft and opens it in the event editor, which admins can use for any organizer.

Forms validate in the browser for convenience, but always show the API's validation errors. Add component tests for the editor steps.
```

**Done when:** an organizer can create and publish an event entirely through the user interface.

### P2.7 — Landing page and event pages

```text
Build the public landing page and event pages (v1.md FR-14, decision #24).

In /api:
- GET /api/v1/tickets lists every public ticket option of published events, from every organizer: the event (name, slug, date, venue, timezone, image and event category), the ticket type, category, wave (name and dates) where there is one, the price and currency, and a state: on_sale, upcoming or sold_out. Options whose sales have ended are left out, and so are a delegation's set-aside offerings. Filters: q (event name or venue), event_category, date_from and date_to (the event's date). Sort by event date, then category, then wave. Paginated.
- GET /api/v1/events/{slug} returns a published event with its public ticket options in the same shape.

In the Next.js app:
- The landing page: the ticket options as rows grouped by event, with search, an event category filter and a date filter. Sold-out options stay listed with a "Sold out" badge, and upcoming waves are greyed out with their start date and price. Each row's Buy button opens the event page with that option chosen.
- Event images (decision #46): render every event image with next/image. Add the Supabase Storage bucket's public path, built from NEXT_PUBLIC_SUPABASE_URL, to images.remotePatterns, allow AVIF and WebP in images.formats, and give each image a sizes attribute so phones fetch small widths. The event page's Open Graph image is the stored JPEG itself, for link previews.
- The event page at /events/{slug}: the image, description, venue, date and time in the event's timezone, and the public ticket options grouped by category, showing each category's current wave and its upcoming waves greyed out, with "sold out" and "sales not open yet" states.
- Server-side rendering with sensible caching, plus SEO metadata (title, description, Open Graph tags).
- Accessible markup: keyboard navigation and correct heading order.
No checkout yet; the "Buy" button can be a placeholder.
Tests: draft and closed events never appear; delegation offerings never appear; each filter works; a wave shows as upcoming before its start date and on sale after it; event images render through next/image with a sizes attribute.
```

**Done when:** published events and their ticket options appear with the correct times, waves and states, and draft events and delegation tickets don't appear.

### P2.8 — Event link, QR code and poster

```text
Implement each event's link, QR code and poster (v1.md FR-14, decision #25).

- The link is {public site URL}/events/{slug}, with the public site URL from config (http://localhost:3000 locally, the tunnel's Next.js hostname when testing with a phone). Slugs are unique across the platform (P2.1).
- GET /api/v1/organizer/events/{id}/share returns the link. GET /api/v1/organizer/events/{id}/qr-code?format=png|svg returns a QR code that encodes only the link. GET /api/v1/organizer/events/{id}/poster returns a printable A4 PDF with the event's name, date and venue in the event's timezone, the QR code, "Scan to buy tickets" and the link printed beneath it.
- Only the event's managers and platform admins can call them.
- Generate the QR code and the PDF in /api with maintained libraries; tell me which ones you chose and why.
- The platform's public name isn't decided (decision #28): read it from config, with a placeholder.
- In the organizer area of the Next.js app, a Share panel on each event: copy the link, and download the QR code (PNG or SVG) and the poster (PDF).
- Tests: the QR code decodes to the event's link; another organizer's staff, staff of the same organizer who don't manage the event, event owners and guards are refused; the poster renders for a draft event too, and its link shows the event once it's published.
```

**Done when:** organizer staff can download the QR code and the poster, and, with the public site URL set to the tunnel's Next.js hostname, scanning the printed QR code with a phone opens the event page.

**Then run the [phase exit check](#phase-exit-check) for Phase 2.**

---

## Phase 3 — Checkout, orders and inventory

### P3.1 — Orders and inventory holds

```text
Implement order creation with inventory holds as described in implementation-plan.md Phase 3 steps 1–2.

- Starting point: POST /api/v1/orders, GET /api/v1/orders/{id}, apply-promotion and payments already exist from P1.6 as placeholders. The create endpoint only stores the purchaser and returns a checkout token (stored hashed); it checks no inventory, holds nothing and prices nothing. Replace its body, and keep the checkout token behaviour and the organizer-suspended refusal, which have tests.
- POST /api/v1/orders (guest checkout allowed) accepts only a published event and validates the offering, the sales window (a wave's dates for a wave's offering), quantity limits, group rules (the quantity must be a multiple of the offering's group_size; decision #50), delegation eligibility (a delegation's offerings only with its claim token or access code; public checkout can't buy them) and, when the event has captcha_required on, the CAPTCHA response.
- Hold the inventory inside one database transaction, using this atomic conditional update for each offering:
    UPDATE event_ticket_offerings
    SET reserved_count = reserved_count + :qty
    WHERE id = :id AND status = 'active'
      AND capacity - sold_count - reserved_count >= :qty;
  If 0 rows are updated, roll back and return a "sold out" conflict response. A delegation claim holds its set-aside offering the same way; there is no separate delegation counter (hurdles.md H14). On success, insert inventory_reservations rows with expires_at.
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
- Locally it runs on the Supabase test project, which limits connections. Check the limit first; if 50 parallel connections exceed it, tell me before lowering the numbers.
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
- Group discount setup (decisions #50 and #51): add group_discount_type (percentage or fixed, nullable) and group_discount_value to event_ticket_offerings, as in v1.md §4.2 and doc/diagrams/erd/02-ticket-configuration-and-inventory.md. A group discount needs a group_size above 1. Validate it on offering create and update (POST /api/v1/organizer/events/{id}/offerings, PATCH /api/v1/organizer/offerings/{id}): the discounted price per ticket, rounded down to a whole shilling, must stay above zero. Treat a change to the discount like a price change once the offering has sales (refused). Add the two fields to the offering dialogs in the organizer's tickets step, where group size already lives, show the resulting price per ticket and per group, and update the OpenAPI contract.
- Pricing service (backend only), per ticket and in this order: the offering's price, then the group discount off each ticket (a percentage or a fixed amount, rounded down to a whole shilling; decision #51), then the promo code off each eligible ticket (decisions #52 and #53). A fixed code takes its amount off each eligible ticket; a percentage code is rounded down to a whole shilling per ticket. A code that would bring any ticket to zero or below is refused. A code's minimum order value is checked against the total before any discount. Every amount stays whole shillings (decision #41). Order items keep a snapshot of the unit price, the group discount, the promo discount, fees and currency.
- Platform fee (decision #54): a configurable percentage (an environment variable in .env.example), defaulting to 0, snapshotted on each order item and deducted from the organizer, never added to the buyer's total (decision #2). The real formula is still for the client to set (hurdles.md H1); changing the percentage must need no code change. Ask me how to round the fee before choosing.
- Orders that total zero (complimentary tickets or a free delegation claim) skip payment (hurdles.md H3): the order goes pending → paid → completed in one transaction, with the same checks and audit trail, and no Pesapal payment. A promo code can never produce a zero total (decision #53).
- Core promotions: percentage or fixed discount; eligible offerings; start and end dates; a total usage limit; a per-buyer limit (by email); a minimum quantity or order value. Count usage against the hold so concurrent orders can't exceed a limit, and release the usage when the hold expires.
- POST /api/v1/orders/{id}/apply-promotion, which recalculates the totals. Codes apply only to a pending order whose hold is still active (decision #27).
- Order states follow doc/state-machines.md.
- Tests: every promotion rule; group pricing (KES 200 off a group of 4 at KES 1,000 gives KES 3,200; a percentage that leaves a fraction is rounded down per ticket; a quantity that isn't a multiple of group_size is refused); a promo code after a group discount; a code refused because it would make a ticket free; the minimum order value checked before discounts; a concurrent test on the last use of a promotion; and a snapshot that stays unchanged after an organizer edits the price or the group discount.
```

**Done when:** totals come only from the backend, group and promo discounts follow decisions #50–#53, and promotion limits hold under concurrent orders.

### P3.5 — Checkout screens

```text
Build checkout in the Next.js app.
- From the landing page's Buy button or the event page: choose an offering and quantity → purchaser and attendee forms generated from the event's setup (per-ticket forms only in "every ticket" mode) → the order is created and the tickets held → promo code → order summary from the API → a "Continue to payment" placeholder.
- A visible countdown for the hold. When it expires, explain clearly and offer to start again.
- Group offerings: the quantity steps in multiples of the group size, and the price shows per ticket and per group, with the group discount where there is one. The amounts shown always come from the API.
- Handle sold-out and sales-closed responses gracefully.
- A CAPTCHA step when the event has captcha_required on (hurdles.md H13). Tell me which CAPTCHA provider you chose and why.
- A delegation claim page (/claim/{token}, or an access code entry) that shows the delegation's set-aside tickets (category, type, price and how many are left) and opens checkout for them.
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
- Payment records: provider, provider reference, amount, currency, status and idempotency key. Each attempt is its own payments row, so that table is the payment attempt history (decision #39); there's no separate attempts table.
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
- IPN registration: an artisan command that registers our IPN URL (RegisterIPN) for the current environment and stores the returned IPN ID in config. Run it once per environment and document it. Locally, Pesapal can't reach localhost: register the tunnel's API hostname from P1.8, and register again if that hostname changes.
- initiate(): submit the order (SubmitOrderRequest) with the amount, currency, description, buyer email and name (never a phone or ID number from our checkout), the IPN ID and our callback URL. Use the payment attempt's ID, not the order ID, as the merchant reference, so a buyer can retry a failed payment on the same order. Save the order tracking ID Pesapal returns as the payment's provider reference, and return the hosted checkout URL.
- queryStatus(): call the transaction status endpoint (GetTransactionStatus) with the order tracking ID and map Pesapal's statuses (completed, failed, invalid, reversed) to our payment states. Keep only the payment method and confirmation code it returns. Never store, log or audit the paying account (Pesapal's payment_account field: the M-Pesa phone number or masked card number); strip it from the response before anything is saved.
- parseNotification(): read the order tracking ID, merchant reference and notification type from the IPN, then confirm with queryStatus(). Build the IPN reply in the exact format Pesapal requires.
- refund(): use Pesapal's refund request (RefundRequest) where Pesapal supports it for the payment method, and track the refund's status.
- Order totals are always whole shillings (decision #41). Send them to Pesapal as whole amounts, and treat a status result whose amount isn't a whole number of shillings as a mismatch.
- Tests with recorded sandbox responses for every method, with the paying account removed from the recordings, plus a test that no stored record or log line contains it. Also write doc/payments-pesapal.md: how to get sandbox credentials, register the IPN, run a test M-Pesa payment and a test card payment, and switch to production.
```

**Done when:** a sandbox M-Pesa payment and a sandbox card payment both complete end to end locally, through the IPN (reaching the local API through the tunnel) and the status check, and the recorded-response tests pass.

### P4.4 — Reconciliation and refunds

```text
Implement implementation-plan.md Phase 4 steps 6–8.
- Reconciliation job: every few minutes (configurable), query the provider about payments that have been pending longer than a threshold. Resolve them through the same transactional path as notifications, or flag them. Record each run in reconciliation_runs (decision #39), with its counts and the flagged payments and reasons, and add GET /api/v1/admin/reconciliation-runs for platform admins.
- Refunds (decision #12): only platform admins start them, naming the tickets each covers; the system starts them only for rejected payments. Submit through Pesapal where it supports the payment method, or let an admin record a manual refund with its reference. Track their status. Revoke the covered tickets and move the order to partially refunded or refunded; a never-redeemed ticket's place goes back on sale if the event is still published. Audit every refund.
- Cancelled and postponed events (hurdles.md H5): for a cancelled event, admins see a list of its orders awaiting refund and refund them one by one. For a postponed event, ticket holders request a refund through the link in the postponement email (POST /api/v1/ticket-access/{token}/refund-requests) until refund_requests_until; each request lands in an admin queue to approve or decline.
- Payment history endpoints: staff see the events they manage; platform admins see everything, with filters by status, provider and date.
- Live payment table (decision #45), for platform admins only: GET /api/v1/admin/payments returns every payment attempt, newest first and paginated: order reference, receipt number, event, organizer, buyer name and email, amount and currency, Pesapal payment method and confirmation code, payment status, order status, the order's tickets counted by status, when it started and when it last changed. It flags payments pending longer than the reconciliation threshold, and rejected payments whose automatic refund hasn't succeeded. Filters: payment status, order status, organizer, event, method and date range; search by order reference, receipt number, confirmation code, Pesapal tracking ID or buyer email. An updated_since parameter returns only the rows whose payment, order or tickets changed after that time, so the screen can poll cheaply; index what it uses. GET /api/v1/admin/payments/{id} returns the attempt history, the notifications and status checks received, the refunds and the order's tickets. Tests: staff, event owners and guards are refused; updated_since returns exactly the changed rows, including a ticket revoked after payment; no response contains the paying account.
- Tests for each.
```

**Done when:** stuck pending payments get resolved by reconciliation, and refunds update the order and ticket states correctly.

### P4.5 — Payment screens

```text
Build the payment step in the Next.js app.
- "Pay now" starts the payment and sends the buyer to Pesapal's hosted checkout, where they choose M-Pesa or card. Use a full-page redirect unless I ask for an embedded frame.
- The callback page Pesapal returns the buyer to shows "Confirming your payment…" and checks the order status from the API every few seconds until it's final. It shows clear outcomes for paid, failed and still pending (with a note that the email will follow), plus a retry button for failed payments while the hold is still active.
- The result always comes from the API's status, never from redirect parameters. The status check sends the order's checkout token (P1.6).
In the organizer and admin areas, add payment history and reconciliation screens (each reconciliation run, with its flagged payments), the admin refund screens and the refund-request queue, showing the Pesapal payment method and confirmation code.
- A live payment table in the admin area (decision #45), with the P4.4 columns, filters and search. While the page is visible it polls GET /api/v1/admin/payments with updated_since every 5 seconds (configurable), highlights rows that changed, and shows counters at the top: pending, stuck, rejected awaiting refund, and succeeded and failed today. A row opens the payment's detail. Use polling only: the cPanel host can't run a WebSocket server.
Add a Playwright test of the payment flow against the FakeProvider, and one where a payment's row in the live table changes from pending to succeeded without a page reload.
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
- Email: a queued Laravel notification, sent from the outbox record written in P4.2, with retries and logged failures. Outbox payloads hold record IDs only: the job reads the email address and other details when it sends (decision #36). Until Phase 8, use a local mail catcher that runs without Docker (for example Mailpit). From Phase 8, mail goes through the cPanel host's own mail server over SMTP (decision #9): make the SMTP settings environment variables, and throttle the email queue below the host's hourly sending limit (a config value), so a big sale delays emails instead of losing them.
- Invitation emails (decision #49): staff, event owner and platform admin invitations, including a new link an admin or staff member issues. Until now the inviter is shown the link once and passes it on; once these emails work, decide with me whether the API keeps returning the link. Invitation tokens are stored only as a hash, so the email job can't read one back: agree with me how it gets the link (for example, issuing the token when the email is sent, or sending outside the outbox at creation) before building it.
- Buyer emails (hurdles.md H9, H10): the order confirmation with the retrieval link, a receipt for every paid order (with orders.receipt_number, the event name, the organizer's name and the event's presented_by name where set; decision #43), postponement emails with the refund-request link, cancellation emails, and transfer emails. No SMS.
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
- The ticket page in the Next.js app at /t/{token}: event details in the event's timezone, the organizer's name and the event's "Presented by" name where set, category, attendee name (where collected), ticket number, seat or delegation, the QR code rendered from the token, and the 6-character short code printed clearly beside it (grouped as three and three characters, for example K7P 3QX). Add a download as PDF or image that includes the short code, a link to request a new retrieval link, a transfer form, and, after a postponement, a refund-request form.
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
- Short code entry is rate-limited per guard (for example, 10 per minute). After 5 invalid codes in a row, lock short code entry for that guard (event_staff.short_code_locked_at) until one of the event's managers unlocks it with POST /api/v1/organizer/events/{id}/staff/{staffId}/unlock-short-codes, and notify the organizer. QR scanning keeps working during a lock.
- Log every attempt in ticket_scans, including rejected ones: result, gate, guard, time and method (qr or short_code).
- The response contains only: the result, the reason, the category, the attendee name where collected, the last 4 digits of the holder's phone or ID number when the event has an identity field, the seat or delegation, and, for a ticket already used, the earlier time and gate.
- Rate-limit by guard.
- Gate entry counts for guards (v1.md §3, "gate session only"; doc/roleandprivileage.md §2.4): GET /api/v1/events/{id}/scans/summary returns how many people have been admitted at the guard's assigned gate (or the whole event, if the assignment names no gate). Counts only, no names or ticket details. Test that a guard can't read another gate's or event's count.
- The guard's own scans (decision #40): GET /api/v1/events/{id}/scans/mine lists the signed-in guard's attempts at their current event: time, result, reason, ticket category, gate and method, without attendee names, phone or ID digits. It works only while the guard's access lasts. Test that a guard never sees another guard's scans, and that no name appears in the response.
- Re-entry is not supported: a redeemed ticket never admits again (hurdles.md H7, limitation.md rule 9.10).
```

**Done when:** valid tickets are admitted once, and every rejection reason is returned correctly.

### P6.2 — Simultaneous-scan and rejection tests

```text
Write tests for implementation-plan.md Phase 6 steps 9–10:
- Simultaneous scans: 10 parallel requests from different guards for the same ticket. Exactly one is admitted and nine get "already redeemed". Run against PostgreSQL with real parallel processes.
- Short codes: a valid short code admits once; a QR scan and a short code entry for the same ticket at the same moment admit exactly once; a short code from another event is rejected; 5 invalid codes in a row lock short code entry for that guard, while QR scanning still works.
- One test for each rejection case: wrong event, wrong gate, revoked, unpaid, before the gate opens, after it closes, unknown token, a guard not assigned, a guard whose assignment was removed, a password guard scanning a ticket from a different event, a password guard whose event has closed (their session must already be gone), a Google-signed-in staff member, event owner or admin, and the old QR code or short code of a transferred ticket.
- Every attempt appears in ticket_scans.
Show me the output.
```

**Done when:** the simultaneous-scan test passes repeatedly, and every rejection case is covered.

### P6.3 — Guard scanner screens

```text
Build the guard area of the Next.js app, designed for phones first:
- Sign in with the guard email and password → list of assigned events → choose a gate (skipped for events without gates) → full-screen camera scanner. Include a visible "Sign out" button for shift changes on shared phones. When a password guard's event closes, show that their access has ended. Use a maintained browser QR-scanning library and tell me which one you chose and why. Note that camera access requires HTTPS.
- A "My scans" list from GET /api/v1/events/{id}/scans/mine, showing the guard's own scans at this event without names.
- An "Enter code" button for when a QR code won't scan. The guard asks the attendee to show their downloaded or printed ticket and types its 6-character short code on a large keypad with letters and numbers. The result screen shows the attendee name (where collected) and category, so the guard can compare it with the ticket shown. When the event has an identity field, it also shows the last 4 digits of the holder's phone or ID number with a "Check" prompt. If short code entry is locked, say so clearly and point the guard back to QR scanning.
- Result screens: large and high-contrast, readable at arm's length, each with a distinct color and icon, plus a sound or vibration where the device supports it. The four states are VALID (admit), ALREADY USED (show the time and gate), INVALID (show the reason) and UNABLE TO VERIFY.
- Fail closed: a timeout (about 5 seconds, configurable) or a network error shows "Unable to verify — do not admit". It must never show success.
- After each result, the scanner returns to scanning with one tap.
- The scanner shows the live admission count for the guard's gate, from the P6.1 count endpoint.
- Organizer view, for the event's managers: live gate activity and attendance counts for each gate.
Add component tests for each result state, including the network-error state.
```

**Done when:** a guard can scan on a real phone over HTTPS through the tunnel (P1.8), and switching off the network shows "Unable to verify".

**Then run the [phase exit check](#phase-exit-check) for Phase 6.** The field rehearsal from implementation-plan.md Phase 6 step 11 needs the hosted system, so it moves to Phase 8, after P8.2.

---

## Phase 7 — Reserved seating, promotions and reporting

Reserved seating comes last in this phase because it is the first item to defer if the schedule slips (SOW §4.1). If it has been deferred by change control, skip P7.3 and P7.4.

### P7.1 — Remaining promotion rules and reports (backend)

```text
Implement implementation-plan.md Phase 7 steps 1–4a.
- Complete any promotion rules from SOW §2.8 that P3.4 didn't cover, plus redemption tracking.
- Organizer report endpoints (only for the events the staff member manages): sales by offering, payment status, remaining capacity, promotion performance, attendance, and gate activity by time and gate.
- Admin report endpoints: platform-wide summaries, transaction oversight, and an audit log search filtered by actor, organizer, action (including sign-ins) and date.
- Settlement (hurdles.md H1): a report per organizer and event (sales, refunds, Pesapal fees, platform fee and amount owed) for admins, and for organizer staff only for the events they manage (organizer-wide totals are for admins); and a payout log where admins record each manual payout. The platform fee calculation is still open (decision #2): ask me before building that column.
- A read-only audit list for organizer staff, for the events they manage (GET /api/v1/organizer/events/{id}/audit-logs).
- Guest list endpoints (v1.md §5): GET /api/v1/organizer/events/{id}/attendees with a search by holder name and filters for ticket category, delegation, purchase date and check-in status, and GET /api/v1/organizer/events/{id}/attendees/export, which streams the same filtered list as CSV. Organizer staff can use them for the events they manage and event owners for their assigned events only (doc/roleandprivileage.md §5.1). For event owners, every row and CSV export contains only the holder's name, ticket category, delegation, check-in status and entry time (the ticket's redeemed_at, empty until it's scanned in; decision #29): no ticket type, contact details, ID numbers, prices or order amounts. Event owners get no other report endpoint. Every guest list view, search, filter and export by an event owner writes an audit log entry with the event, the filters used and, for exports, the number of rows (doc/roleandprivileage.md §5.1.6). A name search is recorded as a name search, never with its text (decision #36).
- A sales list CSV export for organizer staff (GET /api/v1/organizer/events/{id}/reports/sales/export).
- Data deletion on request (implementation-plan.md Phase 7 step 4a, v1.md FR-15, hurdles.md H12): POST /api/v1/admin/data-deletions, for platform admins only. Given an email address or phone number, find the person across every organizer and remove their name, email, phone number and ID digits from orders, attendees, ticket transfers and promotion redemptions, keeping amounts, references, tickets and scan records. Audit it. Tests: the details are gone everywhere, report totals don't change, no log (audit entries, scan records, outbox messages, application logs) held them in the first place, and organizer staff and event owners are refused.
- Reports must match the order and payment records exactly. Write tests that compare report totals with direct sums.
- Tenant-isolation tests for every report endpoint, including that an event owner can export only their assigned events' guest lists, that their rows and CSV carry no other column, and that each of their guest list requests is audited.
```

**Done when:** report figures match the records, and staff only ever see the events they manage.

### P7.2 — Report screens

```text
Build report screens in the Next.js app for organizers, event owners and admins, using the P7.1 endpoints:
- Summary cards, tables with filters, and simple charts where they help: sales over time and attendance by gate.
- An audit log viewer for admins, with sign-ins filterable, and a read-only audit list per event for organizer staff, which includes the sign-ins of that event's guards.
- Settlement screens for admins and organizer staff, and a payout log form for admins.
- A data deletion screen for admins: search by email address or phone number, review what will be removed, then confirm.
- A guest list screen with the P7.1 name search, filters and an "Export CSV" button, in the organizer area for each managed event and as the only screen in the event owner area, which shows no edit, revoke, refund or other write action. The event owner area also shows the names and email addresses of each assigned event's managers.
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

This is the first deployment: until now everything ran locally. Run P8.1 and P8.2 first, because the rest of this phase runs against staging.

### P8.1 — Hosting check script

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

### P8.2 — Deploy staging

```text
Deploy staging, the first hosted environment, as described in implementation-plan.md Phase 1 step 17, using the results of the P8.1 hosting check.

- A new Supabase project for staging, separate from the development and test projects. Connect to it the way the hosting check found working (direct or pooler), and run the migrations on it.
- /api: a deploy script, or documented steps, for cPanel: update the code, composer install --no-dev, migrate --force, cache config, routes and views. The document root points to /api/public, and the .env file sits outside the web root.
- Cron: a single entry runs `php artisan schedule:run` every minute. Unless the hosting check showed that long-running processes are allowed, the scheduler processes the database queue with `queue:work --stop-when-empty`, without overlapping runs.
- The Next.js app on Vercel (decision #5), on the www domain, with the API's URL and the allowed domains in its environment variables.
- The staging Google OAuth settings: document the callback URL to register in the staging OAuth client.
- Pesapal sandbox on staging: register the staging IPN URL with the P4.3 command.
- Create the first staging platform admin with the P1.4a setup command, and document the step.
- Email through the cPanel host's own mail server (decision #9): set up SPF and DKIM in cPanel's Email Deliverability page, add the DMARC record from doc/email-setup.md, and set the queue throttle to the host's hourly sending limit. If the client hasn't got that limit from the host yet, ask me.
- doc/deployment.md listing every step and every environment variable.
No real credentials in any file.
```

**Done when:** staging serves the API and the Next.js app over HTTPS; Google sign-in works and the session holds across both subdomains, including during server-side rendering; the health endpoint shows the scheduler and queue running; a sandbox M-Pesa payment and a sandbox card payment complete through the staging IPN; the ticket email arrives through the real provider; and a guard can scan on a real phone.

Then hold the field rehearsal from implementation-plan.md Phase 6 step 11 against staging, with real phones on mobile data.

### P8.3 — End-to-end tests

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

### P8.4 — Security review

```text
Review the whole codebase for security, as described in implementation-plan.md Phase 8 step 3, then fix what you find.
Check: authorization on every route (compare against v1.md §3); tenant isolation; event owners' read-only scope, the columns they can see and the auditing of their guest list access (doc/roleandprivileage.md §5.1); staff event access, so no event data reaches staff who don't manage the event and only admins grant access, and that no endpoint lists people beyond decision #32 (§5.2); the payout account readable and changeable only by platform admins; the Google sign-in flow (state check, verified emails only, linking by Google account ID, session regeneration, no open redirects); platform admin rights granted only through admin invitations and the one-time setup command, and never the last admin removed; guard password sign-in (hashing, rate limiting, lockout, identical errors, forced password change, guard-only scope, and deletion of the password when the event closes); that only guard accounts can scan; that the paying account from Pesapal is never stored or logged; mass assignment; input validation; SQL injection; XSS in both frontends; CSRF; CORS; session and cookie flags; rate limits on the sign-in callback, resend, scan and webhook endpoints; webhook forgery; secrets in the repository or logs; personal data in logs, and that the audit log really is append-only in the database; security headers; and dependency vulnerabilities (composer audit, pnpm audit).
First report your findings ranked by severity, each with its location and the fix. Wait for my go-ahead, then fix them one by one, adding a test for each.
```

**Done when:** no critical or high findings remain open.

### P8.5 — Performance tests

```text
Write k6 scripts for the performance targets recorded in implementation-plan.md Phase 0 step 6, sized to the client's largest expected event, 5,000 tickets, and its peak buyer numbers (hurdles.md H13; ask me for the buyers-per-minute figure if it still isn't recorded):
- A checkout burst: many buyers ordering from one event at the same moment (FakeProvider for payments).
- Scan throughput: several guards scanning in parallel.
Report response times (median and 95th percentile) against the targets, and identify the slowest queries. Suggest indexes or fixes and apply them after I approve.
```

**Done when:** the targets are met on staging, or the gaps are documented with a plan.

### P8.6 — Monitoring and operations

```text
Set up production monitoring as described in implementation-plan.md Phase 8 steps 5–6:
- Error tracking for the API and the Next.js app, with personal data scrubbed and events kept 90 days (decision #35). Log rotation keeps application logs for 90 days.
- Health and heartbeat checks suitable for an external uptime monitor.
- Alerts for: failed queued jobs, a scheduler or retention job that has stopped, payments stuck in pending longer than a threshold, reconciliation mismatches, and email send failures.
- doc/runbook.md: how to respond to each alert, how to restore a backup into staging, and how to rotate payment and email credentials.
```

**Done when:** each alert has been triggered on staging and reached the right people, and the runbook covers each one.

### P8.7 — Go-live checklist and handover documents

```text
Prepare the go-live and handover documents described in implementation-plan.md Phase 8 steps 7–11:
- doc/go-live-checklist.md: production environment variables, cron, HTTPS on all subdomains, Sanctum production domains, the production Google OAuth client with its callback URL and published consent screen, production Pesapal keys and the production IPN registration, the first production platform admin created with the one-time setup command, email DNS records, backups confirmed, monitoring active, the retention job running and the 5-year period confirmed by the client's accountant, the privacy notice and the deletion-on-request procedure in place (hurdles.md H12), every hurdle in hurdles.md Decided or accepted, limitation.md signed off, the smoke test, and one real low-value payment with each method followed by a refund.
- Update doc/deployment.md and the OpenAPI contract so they match the final code.
- A known issues list for the handover, starting from the known limitations in hurdles.md §5 and limitation.md Part B.
- doc/admin-guide.md and doc/organizer-guide.md: plain-language guides with the main tasks step by step.
- A test results summary for deliverable D11, mapping each SOW §5 acceptance criterion to the tests that prove it.
```

**Done when:** every SOW §5 criterion maps to evidence, and the guides cover every main task.

**Then run the [phase exit check](#phase-exit-check) for Phase 8.** UAT, client sign-off and payment milestone M4 follow.
