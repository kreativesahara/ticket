# Order states

**Type:** State diagram  
**Source:** [v1.md › 4.6 Important state models](../../../v1.md#46-important-state-models)  
**Files:** [SVG](04-order-states.svg) · [PNG](04-order-states.png) · [Mermaid source](04-order-states.mmd)

> **Short version.** The authoritative machine, with every transition, trigger and actor, is [state-machines/02-order-states](../state-machines/02-order-states.md) (doc/state-machines.md §2).

```mermaid
stateDiagram-v2
    PartiallyRefunded : Partially refunded
    [*] --> Pending
    Pending --> Paid
    Pending --> Expired
    Pending --> Cancelled
    Paid --> Completed
    Paid --> PartiallyRefunded
    Paid --> Refunded
    Completed --> PartiallyRefunded
    Completed --> Refunded
    PartiallyRefunded --> Refunded
```

This is a copy of the Mermaid block in [v1.md](../../../v1.md#46-important-state-models). When it changes, update the source document, this file and `04-order-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
