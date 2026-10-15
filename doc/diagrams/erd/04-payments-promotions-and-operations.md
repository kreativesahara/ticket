# Database ERD: payments, promotions and operations

**Type:** Entity relationship diagram  
**Based on:** [v1.md §4.4 Payments and promotions](../../../v1.md#44-payments-and-promotions)  
**Files:** [SVG](04-payments-promotions-and-operations.svg) · [PNG](04-payments-promotions-and-operations.png) · [Mermaid source](04-payments-promotions-and-operations.mmd)

Payments and their refunds, Pesapal notifications, promotions and their redemptions, the audit log, manual payouts and the transactional outbox.

```mermaid
erDiagram
    organizers {
        key id PK "see 01 identity and tenancy"
    }
    events {
        key id PK "see 01 identity and tenancy"
    }
    users {
        key id PK "see 01 identity and tenancy"
    }
    event_ticket_offerings {
        key id PK "see 02 ticket configuration"
    }
    orders {
        key id PK "see 03 orders, attendees and tickets"
    }
    tickets {
        key id PK "see 03 orders, attendees and tickets"
    }
    payments {
        key id PK
        key order_id FK
        string provider "pesapal"
        string provider_reference "Pesapal tracking ID; unique per provider"
        money amount "bigint minor units"
        char(3) currency
        string payment_method "M-Pesa or card; never the paying account"
        string confirmation_code
        string status "pending, succeeded, rejected, failed, cancelled, expired, refunded, reversed"
        string idempotency_key UK
    }
    payment_webhook_events {
        key id PK
        string provider
        string order_tracking_id "no unique key: Pesapal sends no event ID"
        string notification_type
        json payload
        timestamp received_at
        timestamp processed_at
        string status
    }
    refunds {
        key id PK
        key payment_id FK
        money amount
        string provider_reference
        string source "admin, system, postponement_request"
        key requested_by FK "optional"
        key approved_by FK "optional"
        string status "requested, approved, processing, succeeded, failed, rejected"
        text reason
    }
    refund_tickets {
        key refund_id PK, FK
        key ticket_id PK, FK
    }
    promotions {
        key id PK
        key event_id FK
        string code
        string discount_type "percentage or fixed"
        int discount_value "percent, or minor units if fixed"
        timestamp starts_at
        timestamp ends_at
        int usage_limit
        int per_buyer_limit "by purchaser email"
        int min_quantity
        money min_order_value
        int used_count
        string status
    }
    promotion_offerings {
        key promotion_id PK, FK
        key offering_id PK, FK
    }
    promotion_redemptions {
        key id PK
        key promotion_id FK
        key order_id FK
        string purchaser_email
        money discount_amount
        string status
    }
    audit_logs {
        key id PK
        key actor_user_id FK "optional: system actions"
        key organizer_id FK "optional"
        key event_id FK "optional"
        string action
        string resource_type
        key resource_id "polymorphic, no FK"
        json metadata
        string ip_address
        string user_agent
        timestamp created_at
    }
    organizer_payouts {
        key id PK
        key organizer_id FK
        money amount
        char(3) currency
        string reference
        timestamp paid_at
        key recorded_by FK
    }
    outbox_messages {
        key id PK
        string type
        json payload
        timestamp available_at
        timestamp processed_at
        int attempts
        text last_error
    }

    orders ||--o{ payments : "paid through"
    payments ||--o{ refunds : "refunded by"
    refunds ||--|{ refund_tickets : "covers"
    tickets ||--o{ refund_tickets : "refunded in"
    users |o--o{ refunds : "requested_by"
    users |o--o{ refunds : "approved_by"
    events ||--o{ promotions : "configures"
    promotions ||--o{ promotion_offerings : "applies to"
    event_ticket_offerings ||--o{ promotion_offerings : "discounted by"
    promotions ||--o{ promotion_redemptions : "redeemed as"
    orders ||--o{ promotion_redemptions : "uses"
    users |o--o{ audit_logs : "acts in"
    organizers |o--o{ audit_logs : "scopes"
    events |o--o{ audit_logs : "scopes"
    organizers ||--o{ organizer_payouts : "is paid by"
    users ||--o{ organizer_payouts : "recorded_by"
```

## How to read it

- Table and column names are exactly those in [v1.md §4](../../../v1.md#4-database-design). Only the columns v1.md lists are shown; timestamps and any other columns P1.3 adds are not.
- **Types are logical.** `key` is a primary or foreign key (UUID for records referenced from outside the system, chosen in P1.3). `money` is a `bigint` amount in minor units, with its currency in a separate `char(3)` column. Status columns are strings backed by the PHP enums in [state-machines.md](../../state-machines.md).
- **Keys:** `PK` primary key, `FK` foreign key, `UK` unique. Composite or scoped uniqueness, such as event slug per organizer, is in the column comment.
- **Cardinality:** `||--o{` one to zero or more; `||--|{` one to one or more; `|o--o{` optional foreign key (nullable).
- Tables from another part of the schema appear as stubs that show only `id`.

## Notes

- `payments.payment_method` records M-Pesa or card only, never the paying account Pesapal reports (v1.md FR-08).
- `payment_webhook_events` has no unique key: Pesapal notifications carry no event ID, so idempotency comes from the payment and order checks (v1.md §4.4).

## Assumptions to confirm in P1.3

- `refunds.requested_by` is optional: postponement requests come from guest buyers with no user account, and system refunds have no requester.
- `audit_logs.actor_user_id`, `organizer_id` and `event_id` are optional, for system actions and platform-level actions.

P1.3 writes the migrations from v1.md §4. When it does, update these diagrams to match the final schema (prompts.md P1.3 asks for an ERD of the final schema).
