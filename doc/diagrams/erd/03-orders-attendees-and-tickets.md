# Database ERD: orders, attendees and tickets

**Type:** Entity relationship diagram  
**Based on:** [v1.md §4.3 Orders, attendees and tickets](../../../v1.md#43-orders-attendees-and-tickets)  
**Files:** [SVG](03-orders-attendees-and-tickets.svg) · [PNG](03-orders-attendees-and-tickets.png) · [Mermaid source](03-orders-attendees-and-tickets.mmd)

Orders and their items, attendee details and answers, tickets with their hashed and encrypted credentials, retrieval tokens, scans and transfers.

```mermaid
erDiagram
    organizers {
        key id PK "see 01 identity and tenancy"
    }
    events {
        key id PK "see 01 identity and tenancy"
    }
    event_gates {
        key id PK "see 01 identity and tenancy"
    }
    users {
        key id PK "see 01 identity and tenancy"
    }
    event_ticket_offerings {
        key id PK "see 02 ticket configuration"
    }
    delegations {
        key id PK "see 02 ticket configuration"
    }
    seats {
        key id PK "see 02 ticket configuration"
    }
    orders {
        key id PK
        key organizer_id FK
        key event_id FK
        string purchaser_name
        string purchaser_email
        string checkout_token_hash UK "buyer calls need the token"
        money subtotal "bigint minor units"
        money discount_total
        money total
        char(3) currency "KES"
        string receipt_number UK
        string status "pending, paid, completed, expired, cancelled, partially_refunded, refunded"
    }
    order_items {
        key id PK
        key order_id FK
        key offering_id FK
        int quantity
        money unit_price "price snapshot"
        money discount_amount
        money platform_fee_amount
        money line_total
        char(3) currency
    }
    attendees {
        key id PK
        key order_id FK
        string name
        string email
        string phone "CHECK: phone or id_number_last4, never both"
        string id_number_last4
    }
    ticket_field_definitions {
        key id PK
        key event_id FK
        string field_key
        string label
        string field_type "select, checkbox, date; no free text"
        json options
        boolean required
        string scope
    }
    attendee_field_values {
        key id PK
        key attendee_id FK
        key field_definition_id FK
        string value
    }
    tickets {
        key id PK
        key order_item_id FK
        key event_id FK
        key offering_id FK
        key attendee_id FK "optional"
        key seat_id FK "optional: reserved seating"
        key delegation_id FK "optional"
        string ticket_number UK
        string qr_token_hash UK
        text qr_token_encrypted
        string short_code_hash "keyed hash, unique per event"
        text short_code_encrypted
        string status "pending_issuance, valid, redeemed, revoked, expired"
        timestamp redeemed_at
    }
    ticket_access_tokens {
        key id PK
        key ticket_id FK "ticket or order"
        key order_id FK "ticket or order"
        string token_hash UK "hash only"
        timestamp expires_at
        timestamp revoked_at
    }
    ticket_scans {
        key id PK
        key ticket_id FK "optional: unknown codes match no ticket"
        key event_id FK
        key gate_id FK "optional: events without gates"
        key guard_user_id FK
        string method "qr or short_code"
        string result "admitted, already_redeemed, revoked, ..."
        timestamp scanned_at
    }
    ticket_transfers {
        key id PK
        key ticket_id FK
        key from_attendee_id FK
        key to_attendee_id FK
        timestamp transferred_at
    }

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
    attendees ||--o{ ticket_transfers : "from"
    attendees ||--o{ ticket_transfers : "to"
```

## How to read it

- Table and column names are exactly those in [v1.md §4](../../../v1.md#4-database-design). Only the columns v1.md lists are shown; timestamps and any other columns P1.3 adds are not.
- **Types are logical.** `key` is a primary or foreign key (UUID for records referenced from outside the system, chosen in P1.3). `money` is a `bigint` amount in minor units, with its currency in a separate `char(3)` column. Status columns are strings backed by the PHP enums in [state-machines.md](../../state-machines.md).
- **Keys:** `PK` primary key, `FK` foreign key, `UK` unique. Composite or scoped uniqueness, such as event slug per organizer, is in the column comment.
- **Cardinality:** `||--o{` one to zero or more; `||--|{` one to one or more; `|o--o{` optional foreign key (nullable).
- Tables from another part of the schema appear as stubs that show only `id`.

## Notes

- A seat can belong to several tickets over time: it returns to sale when its ticket is refunded or revoked ([state-machines.md §5](../../state-machines.md#5-seat)). A partial unique index allows one active ticket per seat. The v1.md §4.5 diagram shows this as one-to-one, which is wrong.
- An order item has no tickets while its order is pending, so `order_items ||--o{ tickets` is zero or more. The v1.md §4.5 diagram says one or more.
- `ticket_access_tokens` has both `ticket_id` and `order_id`. v1.md says "`ticket_id` or `order_id`", so exactly one is set.

## Assumptions to confirm in P1.3

- `ticket_scans.ticket_id` is optional, because an attempt with an unknown code matches no ticket and every attempt is recorded.

P1.3 writes the migrations from v1.md §4. When it does, update these diagrams to match the final schema (prompts.md P1.3 asks for an ERD of the final schema).
