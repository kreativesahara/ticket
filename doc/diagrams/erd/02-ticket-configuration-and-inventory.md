# Database ERD: ticket configuration and inventory

**Type:** Entity relationship diagram  
**Based on:** [v1.md §4.2 Ticket configuration and inventory](../../../v1.md#42-ticket-configuration-and-inventory)  
**Files:** [SVG](02-ticket-configuration-and-inventory.svg) · [PNG](02-ticket-configuration-and-inventory.png) · [Mermaid source](02-ticket-configuration-and-inventory.mmd)

Ticket types and categories, ticket waves, the offerings that combine a type and category with a price and capacity (public, in a wave, or set aside for a delegation), delegations, seating, and the inventory reservations that hold capacity during checkout.

```mermaid
erDiagram
    events {
        key id PK "see 01 identity and tenancy"
    }
    orders {
        key id PK "see 03 orders, attendees and tickets"
    }
    ticket_types {
        key id PK
        key event_id FK
        string name
        string type_code "early bird, normal, gate, group, promoted"
    }
    ticket_categories {
        key id PK
        key event_id FK
        string name
        string category_code "VVIP, VIP, Media or organizer-defined"
        json access_rules
        boolean sells_in_waves "chosen at event setup"
    }
    ticket_waves {
        key id PK
        key event_id FK
        key category_id FK
        string name "Wave 1, Wave 2 and so on"
        int position
        timestamp starts_at "a category's waves never overlap"
        timestamp ends_at
    }
    event_ticket_offerings {
        key id PK
        key event_id FK
        key ticket_type_id FK
        key category_id FK
        key wave_id FK "optional: a ticket option in a wave"
        key delegation_id FK "optional: set aside for a delegation"
        money price "bigint minor units"
        char(3) currency "KES"
        int capacity
        int sold_count
        int reserved_count "active holds"
        timestamp sale_starts_at "the wave's dates when wave_id is set"
        timestamp sale_ends_at
        int min_per_order
        int max_per_order
        int group_size
        string group_discount_type "percentage or fixed, nullable"
        int group_discount_value "off each ticket"
        boolean requires_approval "Media approval flow"
        string status
    }
    delegations {
        key id PK
        key event_id FK
        string name
        string claim_token_hash UK
        text claim_token_encrypted
        string access_code_hash
        text access_code_encrypted
        string status "active or paused"
    }
    seating_sections {
        key id PK
        key event_id FK
        string name
        json layout_config
    }
    seats {
        key id PK
        key event_id FK
        key section_id FK
        string row_label
        string seat_number "unique within the event"
        string status "available, held, sold"
    }
    inventory_reservations {
        key id PK
        key order_id FK
        key offering_id FK
        key seat_id FK "optional: holds one seat"
        int quantity
        timestamp expires_at "now + 15 minutes"
        string status "active, converted, expired, released"
    }

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
```

## How to read it

- Table and column names are exactly those in [v1.md §4](../../../v1.md#4-database-design). Only the columns v1.md lists are shown; timestamps and any other columns P1.3 adds are not.
- **Types are logical.** `key` is a primary or foreign key (UUID for records referenced from outside the system, chosen in P1.3). `money` is a `bigint` amount in minor units, with its currency in a separate `char(3)` column. Status columns are strings backed by the PHP enums in [state-machines.md](../../state-machines.md).
- **Keys:** `PK` primary key, `FK` foreign key, `UK` unique. Composite or scoped uniqueness, such as a seat number within its event, is in the column comment.
- **Cardinality:** `||--o{` one to zero or more; `||--|{` one to one or more; `|o--o{` optional foreign key (nullable).
- Tables from another part of the schema appear as stubs that show only `id`.

## Notes

- An offering belongs to at most one of a wave (`wave_id`) and a delegation (`delegation_id`). An offering in a wave takes the wave's dates as its sales window ([v1.md FR-04](../../../v1.md#fr-04--ticket-types-and-categories)).
- A delegation's offerings are its set-aside tickets: public endpoints never list them and public checkout can't buy them ([hurdles.md H14](../../../hurdles.md#h14-sponsor-tickets-werent-set-aside-from-public-sale--decided)). A claim holds the offering like any other, so a ticket is never counted twice ([state-machines.md §6](../../state-machines.md#6-inventory-reservation)).
- A PostgreSQL exclusion constraint stops a category's waves from overlapping. Waves have no stored status: they are upcoming, on sale or ended by their dates.
- Partial unique indexes keep pricing unambiguous: one offering per type in each wave, per type and category among an event's ordinary public offerings, and per type and category in each delegation.
- `seats.section_id` is shown as required; confirm in P1.3.

P1.3 writes the migrations from v1.md §4 and updates these diagrams to match the final schema (prompts.md P1.3).
