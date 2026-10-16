# Database ERD: overview

**Type:** Entity relationship diagram  
**Based on:** [v1.md §4.1–§4.4](../../../v1.md#4-database-design)  
**Files:** [SVG](00-overview.svg) · [PNG](00-overview.png) · [Mermaid source](00-overview.mmd)

Every table in the logical schema and the foreign keys between them. The column-level diagrams for each part of the schema are [01 identity and tenancy](01-identity-and-tenancy.md), [02 ticket configuration and inventory](02-ticket-configuration-and-inventory.md), [03 orders, attendees and tickets](03-orders-attendees-and-tickets.md) and [04 payments, promotions and operations](04-payments-promotions-and-operations.md). [v1.md §4.5](../../../v1.md#45-core-relationships) links here.

```mermaid
erDiagram
    direction LR
    %% Identity and tenancy (v1.md §4.1)
    organizers ||--o{ organizer_members : "has staff"
    users ||--o{ organizer_members : "belongs to"
    organizers |o--o{ users : "owns guard accounts"
    organizers ||--o{ events : "owns"
    event_categories ||--o{ events : "categorizes"
    events ||--o{ event_gates : "has"
    events ||--o{ event_staff : "assigns"
    users ||--o{ event_staff : "is assigned"
    event_gates |o--o{ event_staff : "limits guard to"
    organizers |o--o{ invitations : "sends"
    events |o--o{ invitations : "event owner invite for"
    users ||--o{ guard_credentials : "holds password"
    events ||--o{ guard_credentials : "valid only for"

    %% Ticket configuration and inventory (v1.md §4.2)
    events ||--o{ ticket_types : "defines"
    events ||--o{ ticket_categories : "defines"
    events ||--o{ ticket_waves : "schedules"
    ticket_categories ||--o{ ticket_waves : "sold in"
    events ||--o{ event_ticket_offerings : "sells"
    ticket_types ||--o{ event_ticket_offerings : "sold as"
    ticket_categories ||--o{ event_ticket_offerings : "sold as"
    ticket_waves |o--o{ event_ticket_offerings : "offers"
    events ||--o{ delegations : "allocates"
    delegations |o--o{ event_ticket_offerings : "sets aside"
    events ||--o{ seating_sections : "lays out"
    seating_sections ||--o{ seats : "contains"
    events ||--o{ seats : "contains"
    orders ||--o{ inventory_reservations : "holds"
    event_ticket_offerings ||--o{ inventory_reservations : "capacity held"
    seats |o--o{ inventory_reservations : "seat held"

    %% Orders, attendees and tickets (v1.md §4.3)
    organizers ||--o{ orders : "receives"
    events ||--o{ orders : "receives"
    orders ||--|{ order_items : "contains"
    event_ticket_offerings ||--o{ order_items : "bought as"
    orders ||--o{ attendees : "records"
    events ||--o{ ticket_field_definitions : "asks"
    attendees ||--o{ attendee_field_values : "answers"
    ticket_field_definitions ||--o{ attendee_field_values : "answered by"
    order_items ||--o{ tickets : "issues"
    events ||--o{ tickets : "admits"
    event_ticket_offerings ||--o{ tickets : "issued from"
    attendees |o--o{ tickets : "holds"
    seats |o--o{ tickets : "assigned (one active)"
    delegations |o--o{ tickets : "claimed through"
    tickets |o--o{ ticket_access_tokens : "retrieved by"
    orders |o--o{ ticket_access_tokens : "retrieved by"
    tickets |o--o{ ticket_scans : "scanned as"
    events ||--o{ ticket_scans : "logs"
    event_gates |o--o{ ticket_scans : "at"
    users ||--o{ ticket_scans : "scanned by guard"
    tickets ||--o{ ticket_transfers : "transferred"
    attendees ||--o{ ticket_transfers : "from / to"

    %% Payments, promotions and operations (v1.md §4.4)
    orders ||--o{ payments : "paid through"
    payments ||--o{ refunds : "refunded by"
    refunds ||--|{ refund_tickets : "covers"
    tickets ||--o{ refund_tickets : "refunded in"
    events ||--o{ promotions : "configures"
    promotions ||--o{ promotion_offerings : "applies to"
    event_ticket_offerings ||--o{ promotion_offerings : "discounted by"
    promotions ||--o{ promotion_redemptions : "redeemed as"
    orders ||--o{ promotion_redemptions : "uses"
    users |o--o{ audit_logs : "acts in"
    organizers |o--o{ audit_logs : "scopes"
    events |o--o{ audit_logs : "scopes"
    organizers ||--o{ organizer_payouts : "is paid by"

    %% Standalone tables: no foreign keys
    payment_webhook_events {
        string order_tracking_id "matches payments.provider_reference"
    }
    outbox_messages {
        string type "email and follow-up jobs"
    }
```

## How to read it

- Table and column names are exactly those in [v1.md §4](../../../v1.md#4-database-design). Only the columns v1.md lists are shown; timestamps and any other columns P1.3 adds are not.
- **Types are logical.** `key` is a primary or foreign key (UUID for records referenced from outside the system, chosen in P1.3). `money` is a `bigint` amount in minor units, with its currency in a separate `char(3)` column. Status columns are strings backed by the PHP enums in [state-machines.md](../../state-machines.md).
- **Keys:** `PK` primary key, `FK` foreign key, `UK` unique. Composite or scoped uniqueness, such as a seat number within its event, is in the column comment.
- **Cardinality:** `||--o{` one to zero or more; `||--|{` one to one or more; `|o--o{` optional foreign key (nullable).
- Tables from another part of the schema appear as stubs that show only `id`.

## Notes

- To stay readable, the overview leaves out the "who did it" links to `users` (`verified_by`, `invited_by`, `issued_by`, `requested_by`, `approved_by`, `recorded_by`). The domain diagrams show them.
- `payment_webhook_events` and `outbox_messages` have no foreign keys. A webhook event is matched to its payment by `order_tracking_id`, which is the payment's `provider_reference`.

P1.3 writes the migrations from v1.md §4 and updates these diagrams to match the final schema (prompts.md P1.3).
