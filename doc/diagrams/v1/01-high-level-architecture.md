# High-level architecture

**Type:** Flowchart  
**Source:** [v1.md › Recommended high-level architecture](../../../v1.md#recommended-high-level-architecture)  
**Files:** [SVG](01-high-level-architecture.svg) · [PNG](01-high-level-architecture.png) · [Mermaid source](01-high-level-architecture.mmd)

```mermaid
flowchart LR
    Attendees --> PW[Public Website]
    Organizers --> OD[Organizer Dashboard]
    EO[Event Owners] --> OD
    Guards --> GS[Guard Scanner]
    PA[Platform Admin] --> AD[Admin Dashboard]

    PW --> API[Backend API]
    OD --> API
    GS --> API
    AD --> API

    API --> AUTH[Authentication and Permissions]
    API --> DB[(Relational Database)]
    API --> PAY[Payment Service]
    API --> EMAIL[Email Service]
    API --> QR[Ticket and QR Service]

    PAY --> PESAPAL[Pesapal: M-Pesa and card]
    PESAPAL -- IPN, confirmed by status check --> API
```

This is a copy of the Mermaid block in [v1.md](../../../v1.md#recommended-high-level-architecture). When it changes, update the source document, this file and `01-high-level-architecture.mmd` together, then re-render ([how](../../README.md#regenerating)).
