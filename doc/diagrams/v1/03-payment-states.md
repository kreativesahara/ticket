# Payment states

**Type:** State diagram  
**Source:** [v1.md › 4.6 Important state models](../../../v1.md#46-important-state-models)  
**Files:** [SVG](03-payment-states.svg) · [PNG](03-payment-states.png) · [Mermaid source](03-payment-states.mmd)

> **Short version.** The authoritative machine, with every transition, trigger and actor, is [state-machines/03-payment-states](../state-machines/03-payment-states.md) (doc/state-machines.md §3).

```mermaid
stateDiagram-v2
    [*] --> Pending
    Pending --> Succeeded
    Pending --> Rejected
    Pending --> Failed
    Pending --> Cancelled
    Pending --> Expired
    Expired --> Rejected : late confirmation
    Rejected --> Refunded : automatic refund
    Succeeded --> Refunded
    Succeeded --> Reversed
```

This is a copy of the Mermaid block in [v1.md](../../../v1.md#46-important-state-models). When it changes, update the source document, this file and `03-payment-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
