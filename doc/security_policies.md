# Security Policies

**Multi-Organizer Event Ticketing Platform · Version 1.0 · 20 October 2026**
**References:** [v1.md](../v1.md) (FR-01, FR-02, FR-08–FR-10, FR-13, FR-15, §3, §4.7, §8), [techstack.md](../techstack.md) (§3–§6), [roleandprivileage.md](roleandprivileage.md) (§5), [sow.md](../sow.md) (§10), [hurdles.md](../hurdles.md) (H11–H13), [implementation-plan.md](../implementation-plan.md) (§10, §11). If this document disagrees with v1.md, v1.md wins.

This document sets out the security rules the platform follows: who can sign in and how, who can see what, how tickets, payments and personal data are protected, and what is logged. It collects rules that are spread across the specification into one place. It doesn't add new requirements.

Each section shows where the platform stands today:

- **In place**: built and covered by automated tests.
- **Planned (Phase N)**: required, and delivered in that phase of implementation-plan.md.

---

## 1. Principles

1. **Deny by default.** Every request to a protected endpoint is checked against the signed-in user's current roles, read from the database. Anything not explicitly allowed is refused.
2. **The backend decides.** Laravel calculates every price and decides every status. It never trusts a role, organizer, event, gate, price or payment result sent by the browser or by a payment redirect.
3. **Separate tenants.** No organizer, or anyone working for one, can see or change another organizer's events, sales, guest lists or payouts.
4. **Least privilege.** Each role gets only what it needs. Being signed in, or holding a role, never grants access to every record of a type.
5. **Collect little, store safely.** The platform collects as little personal data as it can, never stores secrets in readable form, and keeps logs free of buyers' and attendees' personal details.

---

## 2. Signing in

### 2.1 Admins, organizer staff and event owners: Google sign-in

**Status: In place**

- They sign in with their Google account. The platform has no passwords for these roles.
- Only Google accounts with a **verified email address** are accepted.
- Users are matched by their **Google account ID**, not by email address.
- The platform asks Google only for identity (`openid`, `email`, `profile`). It **doesn't store Google's access or refresh tokens**.
- **No self-registration.** A Google sign-in is accepted only from a platform admin, active organizer staff, an active event owner, or someone with a pending, unexpired invitation sent to that verified email. Anyone else is refused, and no account is created.
- **Admins are invited, never self-appointed.** Only an existing platform admin can invite another. The first admin is created by a one-time setup command that refuses to run once any admin exists. The last admin can't be removed.
- **Suspended users stay out**, even with a valid Google account.
- The sign-in callback checks Google's `state` value, sends users only to allowed frontend addresses (no open redirects), and is limited to 10 attempts per minute per IP address.

### 2.2 Guards: individual password accounts

**Status: In place**

Guards are the only users with passwords, and the only users who can scan tickets.

- **One account per person.** Each guard has their own account, created with their own email address by organizer staff or a platform admin. Accounts are never shared, because every scan is recorded against the guard who made it.
- **Temporary password first.** The platform generates a temporary password and shows it once. The organizer passes it to the guard in person or by direct message, never in a group chat. The guard must set their own password at first sign-in before scanning unlocks.
- **No email reset.** A guard who forgets their password gets a new temporary password from their organizer. Issuing one revokes the old password and ends the guard's sessions.
- **Guard-only.** A password account can never become staff, an event owner or an admin, and is never linked to a Google sign-in. A leaked guard password exposes at most the scanner for one event.
- **Owned by one organizer.** Only the organizer that created the account, or a platform admin, can assign it, issue its password or disable it.
- **One event per password.** Each password works only for one event, only at the gates assigned, and only while that event is open.
- **Deleted when the event closes.** When the event closes, is cancelled or is archived, the password hash is deleted, the guard's sessions end and the assignment ends. A job checks every minute, and every guard request also checks that the event is still open, so there is no gap. The account itself is kept, without a password, so scan records still show who admitted whom.
- **Protected sign-in.** Passwords are hashed with Laravel's default hasher. Sign-in is limited by email and IP address and locks for 15 minutes after 5 failed attempts. An unknown email and a wrong password get the same error.

### 2.3 Sessions

**Status: In place**, except the production cookie settings (**Planned (Phase 8)**).

- Signed-in users get a **cookie session** (Laravel Sanctum). The browser never holds long-lived bearer tokens.
- Session cookies are `HttpOnly` and `SameSite=Lax`, and protected against cross-site request forgery with Sanctum's CSRF token.
- The session ID is **regenerated at sign-in** and the session is **invalidated at sign-out**.
- Sessions are stored in the database, so the platform can end a specific person's sessions immediately. This happens when a user is suspended, when an organizer is suspended (for all its staff, event owners and guards), when staff lose event access, and when a guard's password is reissued or deleted.
- Guard sessions also expire after a fixed time (for example, 12 hours), and guards sign out at the end of each shift because scanning phones may be shared.
- **Before launch:** session cookies are marked `Secure` (sent over HTTPS only) and scoped to the production domains.

---

## 3. Access control and tenant isolation

**Status: In place** for every resource built so far. Each new resource gets a tenant-isolation test before it ships.

Every organizer-owned resource is authorized through a Laravel policy. The full permissions matrix is in v1.md §3, and the detailed rules are in [roleandprivileage.md](roleandprivileage.md) §5.

| Role | What they can reach |
| --- | --- |
| **Platform admin** | Everything, including every user, every organizer, payout accounts, refunds and the full audit log. |
| **Organizer staff** | Their organizer's profile, staff and guard accounts. They fully manage only the events they **manage**: the ones they created, or that a platform admin gave them access to. For their organizer's other events they see a read-only summary only: no sales, orders, guest data, reports, audit records or assignments. |
| **Event owner** | For their assigned events only: each ticket holder's name, ticket category, delegation, check-in status and entry time, which they can search, filter and export as CSV. They also see the names and emails of the staff who manage those events. Nothing else: no prices, contact details, ID numbers, money or settings. |
| **Guard** | Scanning at their assigned event and gates, and their own scans there, without attendee names. |
| **Buyer** | Their own order and tickets, through secret tokens only (§4). No account. |

Rules that apply across all roles:

- **Event access is granted by admins only.** Staff get access automatically to events they create. Only a platform admin can give them access to any other event, or take it away, and every change is audited.
- **No role escalation.** Staff can't give themselves or anyone else event access. Event owners can't invite users, change roles or grant permissions.
- **Who sees whom.** Admins see every user. Staff see their own organizer's people, plus the managers, event owners and guards of the events they manage, and never another organizer's users. Event owners see only their events' managers. Guards see no other users.
- **Read-only means read-only.** A write request outside a user's rights returns `403 Forbidden`.
- **Removal is immediate.** Removing an event owner, archiving an event or suspending an organizer ends the related access at once.

---

## 4. Buyers, orders and tickets

Buyers check out as guests. They reach their order and tickets only through secret tokens. **Knowing an order ID or ticket ID never gives access.**

| Credential | Purpose | How it is stored | Status |
| --- | --- | --- | --- |
| **Checkout token** | Lets the buyer continue their own checkout | Hash only | In place |
| **Retrieval link** | Emailed to the buyer; opens their order and tickets | Hash only, with expiry and revocation. A lost link is replaced, never shown again | Planned (Phase 5) |
| **QR token** | Admits one ticket at the gate | Hash for lookup, plus an encrypted copy so the ticket can be shown again | Planned (Phase 5) |
| **Short code** (6 characters) | Fallback when a QR code won't scan | Keyed hash with a server-side secret, plus an encrypted copy | Planned (Phase 5) |
| **Delegation link and access code** | Lets a sponsor's guests claim their set-aside tickets | Hash, plus an encrypted copy for staff | In place |

- A QR code contains **no personal information**. The retrieval link and the QR code are separate credentials with separate rules.
- **Each ticket admits once.** Redemption is a single atomic database update. If two guards scan the same ticket at the same moment, only one succeeds. Re-entry isn't supported. *(Planned, Phase 6)*
- **Short codes are rate-limited.** Short code entry locks after 5 invalid codes in a row until organizer staff unlock it; QR scanning keeps working. *(Planned, Phase 6)*
- **Scanning needs a connection.** If the scanner can't reach the backend, it shows a connection error and never claims a ticket was verified. *(Planned, Phase 6)*
- **Transfers replace credentials.** When a holder transfers a ticket, the new holder gets a new QR code, short code and retrieval link, and the old ones stop working. *(Planned, Phase 5)*
- **Holds and stock are concurrency-safe.** Holding tickets, confirming payments, issuing tickets and redeeming them all use database transactions and atomic conditional updates, never a read followed by a separate write. *(Planned, Phases 3–6)*

---

## 5. Payments

**Status: Planned (Phase 4)**

- Buyers pay by M-Pesa or card on **Pesapal's hosted checkout**. Card details never touch the platform.
- The platform **never stores** card numbers, CVV codes, or the paying account Pesapal reports (the M-Pesa phone number or masked card number). It keeps only the payment method and confirmation code.
- **Payment notifications aren't trusted on their own.** Every Pesapal notification (IPN) and every return from the checkout page is confirmed with Pesapal's transaction status endpoint before anything changes.
- **Processed once.** Duplicate notifications are recognised and ignored, and every notification is recorded.
- **Amounts are checked.** A confirmed payment with the wrong amount or currency, or for an order that is no longer pending, is refunded automatically.
- **Money is exact.** Amounts are stored as whole numbers in minor units plus a currency code, never as floating-point numbers.
- **Only platform admins start refunds.**
- **Payout accounts are admin-only.** Only platform admins enter, change or read an organizer's payout account. It is stored encrypted and every change is audited. Organizer staff only see whether a verified account is on file, so a compromised staff login can't redirect payouts. *(In place)*

---

## 6. Personal data

### 6.1 What is never stored

**Status: In place** for everything built so far. The rule applies to every future feature.

The platform never stores:

- raw card data or the paying account reported by Pesapal;
- plaintext passwords (guard passwords are hashed, and the hash is deleted when the event closes);
- Google access or refresh tokens;
- plaintext QR tokens, short codes, retrieval tokens or checkout tokens;
- secrets in the source code repository.

### 6.2 Collecting as little as possible

- Each event collects at most **one identity field** (phone or ID number), with no free-text questions.
- For ID numbers, only the **last 4 digits** are kept.
- Guards see only what they need to verify entry: the attendee's name, and the last 4 digits of the identity field where the event collects one.
- Event owners see names, ticket categories, delegations and check-in status, but no contact details or ID numbers.

### 6.3 Deletion on request

**Status: Planned (Phase 7)**

Personal data is kept until the person asks for it to be deleted (hurdles.md H12). A platform admin handles each request:

- The admin finds the person by email or phone number across every organizer.
- Their name, email, phone number and ID digits are removed from orders, attendees, ticket transfers and promotion redemptions.
- Amounts, references, tickets and scan records are kept, so financial and audit records stay intact.
- The deletion is audited. Organizer staff and event owners can't delete personal data.

### 6.4 Uploaded images

**Status: In place**

Event images are never stored as uploaded. The backend checks the type (JPEG, PNG or WebP), size (10 MB) and dimensions (25 megapixels), then re-encodes the image as a new JPEG. This strips all metadata, including GPS location, and anything hidden in the original file. Only an event's managers and platform admins can upload.

---

## 7. Audit logging and retention

**Status: In place**, except audit entries for event owners' guest-list views, filters and exports, which aren't recorded yet. The guest list endpoint exists but writes no audit entry, and the CSV export isn't built.

- **What is recorded:** every sign-in, sign-out, failed attempt and lockout (with time, method, IP address and browser), role and access changes, event cancellations, ticket revocations, refunds, payment adjustments, payout account changes, data deletions, and every view, filter and export of a guest list by an event owner.
- **Append-only.** A database trigger refuses any change to an audit entry. Only the retention job, connecting as its own database role, can delete entries, and only once they are past their retention period.
- **No personal details in logs.** Audit entries and application logs never hold a buyer's or attendee's name, email, phone number or ID digits, payment account details, payout accounts, passwords, tokens, or the text of a search. They refer to records by ID. A guest-list name search is logged as "a name search", without the text. IP addresses and browsers are recorded only for signed-in users, never for buyers.
- **Who can read them:** platform admins see the full audit log. An event's managers see the audit records of that event, including its guards' sign-ins.

| Log | Kept for |
| --- | --- |
| Audit log (including sign-ins) | 5 years |
| Payment notifications and reconciliation runs | 5 years |
| Gate scan history | 12 months after the event ends |
| Application logs and error tracking | 90 days |
| Processed outbox messages and failed queue jobs | 90 days |

A scheduled job deletes entries past their period every day. The 5-year period follows the usual Kenyan period for tax records, and the client's accountant confirms it before launch.

---

## 8. Abuse protection

| Control | Status |
| --- | --- |
| Google sign-in callback: 10 attempts per minute per IP address | In place |
| Guard sign-in: limited by email and IP, 15-minute lock after 5 failures | In place |
| Short code entry: locks after 5 invalid codes until staff unlock it | Planned (Phase 6) |
| CAPTCHA at checkout, switched on per event by staff or admins | Planned (Phase 3) |
| Per-order ticket limits | Planned (Phase 3) |
| Rate limits on the rest of the public API (checkout, retrieval link resend) | Planned (Phase 3) |
| Load test sized for a 5,000-ticket event | Planned (Phase 8) |

---

## 9. Infrastructure and secrets

**Status: Planned (Phase 8)**, except where marked.

- **HTTPS everywhere** in production, on both the website and the API.
- **Secrets stay out of the code.** Database passwords, the Pesapal keys, the Supabase secret key and the Google OAuth secret live only in environment configuration outside the web root, and are never sent to the browser. Production credentials are handed over securely. *(The rule is in place now.)*
- **The database** is reached only by the Laravel backend, over SSL. The frontend never receives database credentials.
- **Separate environments.** Local, staging and production each have their own database and their own Google OAuth client. Automated tests run against a separate test database and refuse to start if pointed at the development or production one. *(The test safeguard is in place.)*
- **Cross-site requests.** The API accepts browser requests with credentials only from the platform's own website addresses (CORS and Sanctum stateful domains). *(In place for local development; production domains are configured in Phase 8.)*
- **Security headers** on both the website and the API are reviewed as part of the Phase 8 security review.
- **Backups.** The backups included in the Supabase plan are confirmed, a restore is rehearsed into staging, and the recovery procedure is documented.
- **Monitoring.** Error tracking, uptime checks on the API and website, and alerts for failed jobs, payments stuck in pending, email failures and a retention job that has stopped.

---

## 10. Development practices

**Status: In place**

- Every organizer-owned resource is authorized through a Laravel policy, and every new resource gets a **tenant-isolation test** proving another organizer can't reach it.
- Tests run against **PostgreSQL**, never SQLite, and never against the development database.
- Sensitive actions write to the audit log, and tests check that they do.
- The API contract (`/api/docs/openapi.yaml`) is kept up to date with every endpoint change.
- **Before launch (Phase 8)**, a security review covers authorization on every endpoint, the Google sign-in flow, guard sign-in, the OWASP Top 10, rate limits, secrets in the repository, security headers, a dependency audit, and attempts to forge payment notifications. No critical or high-severity defect may remain open at launch.

---

## 11. Responsibilities before launch

These items need the client as well as the development team (sow.md §10):

- The client approves the **privacy notice**, retention periods and refund policy.
- Both parties agree their **data protection roles** (controller and processor) and confirm that Kenya's Data Protection Act requirements are met.
- Both parties confirm who is responsible after handover for **hosting, backups, security patches, monitoring and incident response**.
- Pesapal confirms in writing that the platform may collect payments as the legal seller of every ticket (hurdles.md H2).
