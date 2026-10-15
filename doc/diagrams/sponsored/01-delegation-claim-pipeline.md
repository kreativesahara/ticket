# Delegation claim pipeline

**Type:** Sequence diagram  
**Source:** [doc/sponsored.md › 4. End-to-End Pipeline Architecture](../../../doc/sponsored.md#4-end-to-end-pipeline-architecture)  
**Files:** [SVG](01-delegation-claim-pipeline.svg) · [PNG](01-delegation-claim-pipeline.png) · [Mermaid source](01-delegation-claim-pipeline.mmd)

> The buyer's side of a claim, with every branch, is in [activity 06](../activity/06-delegation-claim.md).

```mermaid
sequenceDiagram
    autonumber
    actor Organizer as Organizer
    actor SponsorUser as Sponsor Attendee
    participant Web as Web Frontend (/claim)
    participant API as Backend Pipeline (Laravel)
    participant DB as Database (PostgreSQL)
    participant Outbox as Queue Worker / Outbox
    participant Email as Email Service
    actor Guard as Gate Guard

    Note over Organizer, DB: 1. Setup & Configuration
    Organizer->>API: POST /api/v1/organizer/events/{id}/delegations
    API->>DB: Create delegation with claim token & access code
    Organizer->>API: POST /api/v1/organizer/events/{id}/offerings (with delegation_id)
    API->>DB: Create the set-aside allocation (type, category, quantity, price)

    Note over SponsorUser, DB: 2. Claim & Order Submission
    SponsorUser->>Web: Navigate to /claim/{token} or input Access Code
    Web->>API: GET /api/v1/delegation-claims/{token}
    API-->>Web: Return the allocation (category, type, price, tickets left)
    SponsorUser->>Web: Choose from the allocation, enter attendee details & submit
    Web->>API: POST /api/v1/orders (with delegation context)

    Note over API, DB: 3. Allocation Hold & Zero-Cost Pipeline
    critical Atomic Database Transaction
        API->>DB: Check & hold the allocation (UPDATE event_ticket_offerings reserved_count)
        API->>API: Calculate order total -> KES 0
        Note over API: BYPASS PESAPAL (Hurdle H3)
        API->>DB: Order pending -> paid -> completed (no payment row)
        API->>DB: Convert the allocation hold to sold_count
        API->>DB: Insert tickets (linked to delegation_id, QR hash, short code)
        API->>DB: Insert outbox delivery event (`ticket.issued`)
    end
    API-->>Web: Return Order Success & Immediate Ticket Link

    Note over Outbox, Email: 4. Asynchronous Delivery Pipeline
    Outbox->>DB: Pull pending outbox record
    Outbox->>DB: Generate secure access token (`ticket_access_tokens`)
    Outbox->>Email: Send ticket delivery email with link (/t/{token})
    Email-->>SponsorUser: Delivery email received

    Note over SponsorUser, Guard: 5. Gate Verification & Entry
    SponsorUser->>Guard: Present QR code or 6-char short code
    Guard->>API: Scan credential (POST /api/v1/events/{id}/scans/validate)
    API->>DB: Validate ticket, check open gate, atomic mark redeemed
    API-->>Guard: Valid Ticket: Display Attendee Name + "Delegation: [Sponsor Name]"
```

This is a copy of the Mermaid block in [doc/sponsored.md](../../../doc/sponsored.md#4-end-to-end-pipeline-architecture). When it changes, update the source document, this file and `01-delegation-claim-pipeline.mmd` together, then re-render ([how](../../README.md#regenerating)).
