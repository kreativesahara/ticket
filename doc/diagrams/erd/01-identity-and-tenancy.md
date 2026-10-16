# Database ERD: identity and tenancy

**Type:** Entity relationship diagram  
**Based on:** [v1.md §4.1 Identity and tenancy](../../../v1.md#41-identity-and-tenancy)  
**Files:** [SVG](01-identity-and-tenancy.svg) · [PNG](01-identity-and-tenancy.png) · [Mermaid source](01-identity-and-tenancy.mmd)

Users, organizers and their staff, events and their categories, gates, event assignments for guards and event owners, invitations and one-event guard passwords.

```mermaid
erDiagram
    users {
        key id PK
        string name
        string email UK
        string phone
        string sign_in_method "google or password"
        string google_id UK "Google users only"
        key managed_by_organizer_id FK "guard password accounts only"
        string platform_role "marks platform admins"
        string status
    }
    organizers {
        key id PK
        string name
        string slug UK
        string status
        string contact_email
        string verification_status "unverified, pending, verified, rejected"
        json business_details
        text payout_account_encrypted "encrypted, admins only"
        key verified_by FK
        timestamp verified_at
    }
    organizer_members {
        key organizer_id PK, FK
        key user_id PK, FK
        string role "staff"
        string status
    }
    event_categories {
        key id PK
        string name
        string slug UK
        string status
    }
    events {
        key id PK
        key organizer_id FK
        key category_id FK
        string name
        string slug UK "unique across platform"
        text description
        string presented_by "optional, printed on tickets"
        string image_path
        string venue
        string timezone
        timestamp starts_at
        timestamp ends_at
        int capacity
        string attendee_collection_mode "purchaser or per_ticket"
        string identity_field "none, phone, id_number; fixed once published"
        boolean captcha_required
        text refund_policy
        timestamp refund_requests_until "set on postponement"
        timestamp cancelled_at "set on cancellation"
        text cancellation_reason
        timestamp postponed_at "set on postponement"
        timestamp original_starts_at
        timestamp original_ends_at
        string status "draft, published, closed, cancelled, archived"
    }
    event_gates {
        key id PK
        key event_id FK
        string name
        timestamp opens_at
        timestamp closes_at
        string status
    }
    event_staff {
        key id PK
        key event_id FK
        key gate_id FK "optional, guards only"
        key user_id FK
        string role "manager, guard or event_owner"
        string status
        int short_code_failures
        timestamp short_code_locked_at "locks after 5 invalid codes"
    }
    invitations {
        key id PK
        key organizer_id FK "empty for platform_admin invitations"
        key event_id FK "event owner invitations only"
        string email
        string role "staff, event_owner or platform_admin"
        string token_hash UK "hash only"
        key invited_by FK "empty only for the first admin"
        timestamp expires_at
        timestamp accepted_at
    }
    guard_credentials {
        key id PK
        key user_id FK
        key event_id FK
        string password_hash "deleted when the event closes"
        boolean password_change_required
        key issued_by FK
        timestamp issued_at
        timestamp revoked_at
    }

    organizers ||--o{ organizer_members : "has staff"
    users ||--o{ organizer_members : "belongs to"
    organizers |o--o{ users : "owns guard accounts"
    users |o--o{ organizers : "verifies"
    organizers ||--o{ events : "owns"
    event_categories ||--o{ events : "categorizes"
    events ||--o{ event_gates : "has"
    events ||--o{ event_staff : "assigns"
    users ||--o{ event_staff : "is assigned"
    event_gates |o--o{ event_staff : "limits guard to"
    organizers |o--o{ invitations : "sends"
    events |o--o{ invitations : "event owner invite for"
    users |o--o{ invitations : "invited_by"
    users ||--o{ guard_credentials : "holds password"
    events ||--o{ guard_credentials : "valid only for"
    users ||--o{ guard_credentials : "issued_by"
```

## How to read it

- Table and column names are exactly those in [v1.md §4](../../../v1.md#4-database-design). Only the columns v1.md lists are shown; timestamps and any other columns P1.3 adds are not.
- **Types are logical.** `key` is a primary or foreign key (UUID for records referenced from outside the system, chosen in P1.3). `money` is a `bigint` amount in minor units, with its currency in a separate `char(3)` column. Status columns are strings backed by the PHP enums in [state-machines.md](../../state-machines.md).
- **Keys:** `PK` primary key, `FK` foreign key, `UK` unique. Composite or scoped uniqueness, such as a seat number within its event, is in the column comment.
- **Cardinality:** `||--o{` one to zero or more; `||--|{` one to one or more; `|o--o{` optional foreign key (nullable).
- Tables from another part of the schema appear as stubs that show only `id`.

## Assumptions to confirm in P1.3

- v1.md lists no `id` for `organizer_members`, so `(organizer_id, user_id)` is shown as its key.
- `events.category_id` is shown as required.

P1.3 writes the migrations from v1.md §4 and updates these diagrams to match the final schema (prompts.md P1.3).
