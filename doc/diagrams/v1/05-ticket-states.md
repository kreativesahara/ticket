# Ticket states

**Type:** State diagram  
**Source:** [v1.md › 4.6 Important state models](../../../v1.md#46-important-state-models)  
**Files:** [SVG](05-ticket-states.svg) · [PNG](05-ticket-states.png) · [Mermaid source](05-ticket-states.mmd)

> **Short version.** The authoritative machine, with every transition, trigger and actor, is [state-machines/05-ticket-states](../state-machines/05-ticket-states.md) (doc/state-machines.md §4).

```mermaid
stateDiagram-v2
    PendingIssuance : Pending issuance
    [*] --> PendingIssuance
    [*] --> Valid
    PendingIssuance --> Valid : approved
    PendingIssuance --> Revoked : rejected
    Valid --> Redeemed
    Valid --> Revoked
    Revoked --> Valid : reinstated
    Valid --> Expired : event closes
```

This is a copy of the Mermaid block in [v1.md](../../../v1.md#46-important-state-models). When it changes, update the source document, this file and `05-ticket-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
