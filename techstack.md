# Architecture Blueprint: Multi-Organizer Event Ticketing SaaS

We're locking in **Laravel + Sanctum + Supabase PostgreSQL**, with Laravel initially hosted on **cPanel**. This gives you a practical starting point without forcing you to build the entire infrastructure on AWS before the product is ready.

> **Key architectural principle:** Laravel owns the business rules, Supabase PostgreSQL owns the persistent data, and the frontends communicate with Laravel through a versioned API.

---

## 1. Recommended technology stack

| Layer                   | Technology                 | Responsibilities                                                                                                    |
| ----------------------- | -------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Public frontend**     | Next.js + TypeScript       | Event discovery, event pages, guest checkout and ticket retrieval                                                   |
| **Management frontend** | React + Vite + TypeScript  | Organizer dashboard, platform admin and guard interface                                                             |
| **UI and styling**      | shadcn/ui + Tailwind CSS   | Shared component library and design system for both frontends                                                       |
| **Backend**             | Laravel REST API           | Sanctum, policies, event management, orders, payments, tickets, QR validation and reporting                         |
| **Database**            | Supabase PostgreSQL        | Persistent records, relationships, transactions, constraints and inventory                                          |
| **Payment gateway**     | Pesapal (API 3.0)          | M-Pesa and card payments through Pesapal's hosted checkout, with IPN notifications and transaction status checks    |
| **Initial deployment**  | cPanel                     | Laravel API, provided the hosting supports the required PHP version, extensions, HTTPS, database connectivity and background tasks |

### UI and styling with shadcn/ui

Both frontends use **shadcn/ui** for components, styled with **Tailwind CSS**. shadcn/ui supports both Next.js and Vite.

- **Components live in your code.** shadcn/ui is not installed as a package. Its CLI copies each component's source into the project, where you own and can modify it.
- **One theme for both frontends.** Define the colors, radius and typography once as CSS variables, and use the same values in the Next.js website and the React dashboard so they look like one product.
- **Keep the two copies in sync.** Each frontend gets its own copy of the components. To avoid drift, either agree on one theme file that both projects copy, or move the shared components into a common package if the frontends live in one repository.
- **Accessible by default.** The components are built on accessible primitives, which supports the keyboard-accessible public forms required in the specification.
- **Design the gate scanner for the job.** Guards work fast, often outdoors, on phones. Use large, high-contrast result states (valid, already used, invalid, unavailable) rather than default component sizes.

---

## 2. System architecture

```mermaid
flowchart LR
    A[Attendees] --> W[Next.js Website]
    O[Organizers] --> D[React Dashboard]
    G[Platform Admins and Guards] --> D

    W -- HTTPS REST API --> API[Laravel API on cPanel]
    D -- HTTPS REST API --> API

    API --> S[Laravel Sanctum]
    API --> P[Policies and Tenant Authorization]
    API --> DB[(Supabase PostgreSQL)]
    API --> PAY[Pesapal: M-Pesa and cards]
    API --> QR[Ticket and QR Services]
    API --> E[Email Service]
    API --> Q[Queue and Scheduler]

    PAY -- IPN, confirmed by status check --> API
```

The frontends must never write directly to ticket inventory, payment records, orders or redemptions. All those operations go through Laravel, so the platform has one place to enforce authorization and transactional rules.

---

## 3. Authentication and role design

Use Laravel Sanctum with **cookie-based SPA authentication** for your first-party web applications, rather than creating long-lived bearer tokens for the browsers. Laravel's documentation recommends this approach for first-party SPAs and explains the CSRF and cookie configuration requirements. See [Laravel Sanctum — SPA Authentication](https://laravel.com/docs/sanctum#spa-authentication).

A clean domain setup would look like this:

- `www.yourdomain.com` — public event website
- `manage.yourdomain.com` — organizer and admin dashboard
- `api.yourdomain.com` — Laravel API

These are illustrative domains. Using a common top-level domain makes Sanctum's cookie-based approach practical, provided the session cookie, CORS, CSRF and stateful-domain settings are configured correctly. Preview deployments on unrelated Vercel domains need separate consideration.

### Roles and permissions

| Role                | Access                                                                                                        |
| ------------------- | ------------------------------------------------------------------------------------------------------------- |
| **Platform admin**  | Manage platform users, organizer accounts and configuration; oversee events and platform-wide reports         |
| **Organizer owner** | Full control of their organizer account: profile, members and roles, events, pricing, guards and reports      |
| **Organizer staff** | Only the capabilities an organizer owner grants them; never members, roles or the organizer profile           |
| **Guard**           | Scan and redeem tickets only for assigned events and gates                                                    |
| **Attendee**        | Buy tickets as a guest and view their own orders and tickets through a secure retrieval link; accounts are not required for the initial release |

The full permissions matrix is in v1.md §3. Use Laravel **Policies** and **Gates** to authorize operations. A role alone should never grant access to every record of that type.

> **Example:** An organizer requesting `GET /api/v1/organizer/events/123` must not see event 123 simply because they are logged in. Laravel must verify that the event belongs to an organizer they are a member of, and that their role allows the action.

---

## 4. Database architecture in Supabase PostgreSQL

Start with a relational schema. PostgreSQL is a good fit because ticketing requires reliable relationships, transactions, uniqueness and concurrency controls.

### Core database modules

Table names follow v1.md §4, which lists every column.

| Module                                 | Tables                                                                                                           |
| -------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| **Identity and tenancy**               | `users`, `organizers`, `organizer_members`                                                                       |
| **Events and access**                  | `events`, `event_gates`, `event_staff`                                                                           |
| **Ticket configuration and inventory** | `ticket_types`, `ticket_categories`, `event_ticket_offerings`, `delegations`, `inventory_reservations`, `seating_sections` and `seats` (optional) |
| **Orders and tickets**                 | `orders`, `order_items`, `attendees`, `ticket_field_definitions`, `attendee_field_values`, `tickets`, `ticket_access_tokens`, `ticket_scans` |
| **Payments and promotions**            | `payments`, `payment_webhook_events`, `refunds`, `promotions`, `promotion_redemptions`                           |
| **Audit**                              | `audit_logs`                                                                                                     |

### Key design decisions

- Use UUIDs for externally referenced records where appropriate.
- Store monetary amounts as integer minor units (for example, cents), with an explicit currency code.
- Keep orders, payments and tickets as separate entities. One order may contain multiple tickets, and each ticket must have its own QR credential. Store the QR token as a hash (to find the ticket at the gate) and as an encrypted copy (to show the QR code again), never in plain text.
- Store payment-provider references and idempotency keys with appropriate uniqueness constraints.
- Model reserved seating separately from general admission; only events that use seating need seat inventory.
- Store event-specific attendee fields in a structured format where appropriate, while keeping critical fields and reporting dimensions well defined.
- Use explicit event and ticket statuses rather than inferring availability from whether a record exists.

### Supabase connection

Configure Laravel's PostgreSQL connection using the connection details supplied by your Supabase project, enforce SSL, and test connectivity from the actual cPanel hosting environment. Supabase offers direct and pooled connection options; which one works best depends on network support, connection limits and pooling mode. See [Supabase — Connecting to Postgres](https://supabase.com/docs/guides/database/connecting-to-postgres).

> **Security:** Keep the database password out of source control. Laravel should access PostgreSQL through Eloquent and controlled SQL operations; don't expose database credentials to either frontend.

---

## 5. Critical transaction flows

### A. Ticket purchase

```mermaid
flowchart TD
    A[Select event and ticket type] --> B[Validate price and availability]
    B --> C[Reserve inventory transactionally]
    C --> D[Create pending order]
    D --> E[Redirect to Pesapal checkout: M-Pesa or card]
    E --> F{Verified payment result}
    F -- Successful --> G[Mark payment paid]
    G --> H[Issue unique tickets]
    H --> I[Send secure ticket retrieval link]
    F -- Failed --> J[Fail or expire order]
    J --> K[Release reserved inventory]
    F -- Pending --> L[Await callback or reconciliation]
```

Use temporary inventory reservations with an expiry. Otherwise, abandoned payment sessions can block ticket availability indefinitely.

Pesapal notifies Laravel of payment changes through IPN (instant payment notification) calls. Treat each IPN only as a signal: confirm the actual result with Pesapal's transaction status endpoint before marking anything paid. Processing must also be **idempotent**: a repeated IPN must not issue duplicate tickets or process the same payment twice. A scheduled reconciliation process should ask Pesapal about payments that stay pending or look inconsistent.

Buyers choose M-Pesa or card on Pesapal's hosted checkout, so card details never reach the platform. The buyer's return to the callback URL only shows a status page; it never marks the order as paid.

### B. QR scanning at the gate

```mermaid
flowchart TD
    A[Guard signs in] --> B[Validate assigned event and gate]
    B --> C[Scan QR code]
    C --> D[Laravel validates ticket credential]
    D --> E{Ticket eligible?}
    E -- No --> F[Reject with reason]
    E -- Yes --> G[Atomically redeem ticket]
    G --> H{Redemption committed?}
    H -- Yes --> I[Admit attendee]
    H -- Already redeemed --> J[Reject duplicate scan]
    H -- Conflict --> K[Retry or report unavailable]
```

The redemption must happen in a database transaction with appropriate locking or a conditional atomic update. A frontend check followed by a separate database update is not enough: two guards could scan the same ticket almost simultaneously.

For the MVP, online-only scanning is sensible. The scanner should clearly distinguish **valid**, **already-used**, **invalid** and **unavailable** results.

---

## 6. cPanel deployment strategy

cPanel is a reasonable initial deployment target if your hosting plan supports the requirements below. Don't assume that every shared hosting plan supports a production Laravel workload equally well.

| Component                | Initial approach                                                                            |
| ------------------------ | ------------------------------------------------------------------------------------------- |
| **Laravel API**          | Deploy the application with the public web root pointing to Laravel's `public` directory    |
| **Database**             | Supabase PostgreSQL over a secured connection                                               |
| **Scheduler**            | cPanel cron invoking Laravel's scheduler every minute                                       |
| **Queues**               | Laravel database queue initially, if supported by your plan                                 |
| **Long-running workers** | Verify whether the host permits them; otherwise use a controlled cron-based worker strategy |
| **Assets**               | Supabase Storage or another object-storage service                                          |
| **Secrets**              | Environment configuration outside public access                                             |
| **Monitoring**           | Laravel logs, payment/webhook audit records and external uptime monitoring                  |

For a shared host that does not allow persistent queue workers, a cron-triggered `queue:work --stop-when-empty` approach may work for lightweight workloads, subject to hosting limits. Time-sensitive work must not depend on a queue configuration you have not tested.

> **Warning:** Do not process lengthy payment reconciliation or email batches inside the user's checkout request. Keep the HTTP request fast and move appropriate work to queues.

---

## 7. API structure

All endpoints are versioned under `/api/v1` and follow v1.md §5, which has the full list. Public endpoints sit at the top level, organizer endpoints under `/api/v1/organizer` and platform admin endpoints under `/api/v1/admin`. The main endpoints:

| Module               | Example endpoints                                                                                          |
| -------------------- | ---------------------------------------------------------------------------------------------------------- |
| **Authentication**   | `POST /api/v1/auth/register`, `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me` |
| **Organizers**       | `POST /api/v1/organizers`, `GET /api/v1/organizers/me`                                                     |
| **Events**           | `GET /api/v1/events`, `POST /api/v1/organizer/events`, `PATCH /api/v1/organizer/events/{id}`               |
| **Ticket offerings** | `GET /api/v1/events/{id}/tickets`, `POST /api/v1/organizer/events/{id}/offerings`                          |
| **Orders**           | `POST /api/v1/orders`, `GET /api/v1/orders/{id}`                                                           |
| **Payments**         | `POST /api/v1/orders/{id}/payments`, `POST /api/v1/webhooks/payments/{provider}`                           |
| **Ticket retrieval** | `GET /api/v1/ticket-access/{token}`, `POST /api/v1/ticket-access/resend`                                   |
| **Gate scanning**    | `POST /api/v1/events/{id}/scans/validate`                                                                  |
| **Reports**          | `GET /api/v1/organizer/events/{id}/reports/sales`                                                          |

Public event discovery and guest checkout can be accessible without a login where appropriate. Ticket retrieval must use secure, expiring links or authenticated access, and must not expose sequential IDs as the sole authorization mechanism.

---

## 8. Implementation phases

The phase plan, with steps, exit criteria and the 12-week timeline, is in [implementation-plan.md](implementation-plan.md).

Avoid microservices for the first version. A modular Laravel application is simpler to deploy, test and maintain on cPanel. Keep payments, ticket inventory and redemption isolated into clear service classes so you can scale or extract them later if the product requires it.

---

## 9. Recommendation

Start with the **database and tenancy foundation**, not the dashboard UI. If the organizer boundaries, inventory transactions and redemption rules are sound, the frontend can evolve without compromising the ticketing engine.

The first implementation deliverable should be a **reviewed ERD and Laravel migration plan**, followed by feature tests for:

- Tenant isolation
- Overselling prevention
- Payment idempotency
- Duplicate QR redemption
