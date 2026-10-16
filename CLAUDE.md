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
