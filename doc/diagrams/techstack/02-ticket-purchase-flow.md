# Ticket purchase flow

**Type:** Flowchart  
**Source:** [techstack.md › A. Ticket purchase](../../../techstack.md#a-ticket-purchase)  
**Files:** [SVG](02-ticket-purchase-flow.svg) · [PNG](02-ticket-purchase-flow.png) · [Mermaid source](02-ticket-purchase-flow.mmd)

> The complete buyer journey is in the [activity diagrams](../activity/01-buyer-journey-overview.md).

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
    F -- Failed or cancelled --> J[Buyer may retry while the hold lasts]
    J --> K[Hold expires: release inventory and expire order]
    F -- Pending --> L[Await callback or reconciliation]
```

This is a copy of the Mermaid block in [techstack.md](../../../techstack.md#a-ticket-purchase). When it changes, update the source document, this file and `02-ticket-purchase-flow.mmd` together, then re-render ([how](../../README.md#regenerating)).
