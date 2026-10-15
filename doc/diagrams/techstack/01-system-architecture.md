# System architecture

**Type:** Flowchart  
**Source:** [techstack.md › 2. System architecture](../../../techstack.md#2-system-architecture)  
**Files:** [SVG](01-system-architecture.svg) · [PNG](01-system-architecture.png) · [Mermaid source](01-system-architecture.mmd)

```mermaid
flowchart LR
    A[Attendees] --> W[Next.js app on Vercel]
    O[Organizer staff] --> W
    EO[Event owners] --> W
    G[Platform admins and guards] --> W

    W -- HTTPS REST API --> API[Laravel API on cPanel]

    API --> S[Laravel Sanctum]
    API --> GO[Google OAuth sign-in]
    API --> P[Policies and Tenant Authorization]
    API --> DB[(Supabase PostgreSQL)]
    API --> PAY[Pesapal: M-Pesa and cards]
    API --> QR[Ticket and QR Services]
    API --> E[Email Service]
    API --> Q[Queue and Scheduler]

    PAY -- IPN, confirmed by status check --> API
```

This is a copy of the Mermaid block in [techstack.md](../../../techstack.md#2-system-architecture). When it changes, update the source document, this file and `01-system-architecture.mmd` together, then re-render ([how](../../README.md#regenerating)).
