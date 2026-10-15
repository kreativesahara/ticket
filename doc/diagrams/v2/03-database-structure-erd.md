# Database structure (ERD)

**Type:** Entity relationship diagram  
**Source:** [v2.md › 10. Recommended database structure](../../../v2.md#10-recommended-database-structure)  
**Files:** [SVG](03-database-structure-erd.svg) · [PNG](03-database-structure-erd.png) · [Mermaid source](03-database-structure-erd.mmd)

> **Replaced.** v2.md is kept for reference only. [v1.md](../../../v1.md) replaces it, and the names and decisions in this diagram are out of date.
>
> The current schema is in the [complete ERD](../erd/00-overview.md).

```mermaid
erDiagram
    USERS ||--o{ EVENTS : organizes
    EVENTS ||--o{ TICKET_TYPES : offers
    EVENTS ||--o{ TICKET_CATEGORIES : defines
    EVENTS ||--o{ DELEGATIONS : contains
    EVENTS ||--o{ EVENT_GUARDS : assigns
    EVENTS ||--o{ ORDERS : receives
    USERS ||--o{ ORDERS : places
    ORDERS ||--|{ TICKETS : contains
    ORDERS ||--o{ PAYMENTS : has
    TICKET_TYPES ||--o{ TICKETS : classifies
    TICKET_CATEGORIES ||--o{ TICKETS : categorizes
    DELEGATIONS ||--o{ TICKETS : allocates
    TICKETS ||--o{ TICKET_SCANS : records
    EVENTS ||--o{ SEATS : defines
    SEATS |o--o| TICKETS : reserves
    EVENTS ||--o{ PROMOTIONS : configures
    PROMOTIONS |o--o{ ORDERS : discounts
```

This is a copy of the Mermaid block in [v2.md](../../../v2.md#10-recommended-database-structure). When it changes, update the source document, this file and `03-database-structure-erd.mmd` together, then re-render ([how](../../README.md#regenerating)).
