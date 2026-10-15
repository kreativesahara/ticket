# Refund state machine

**Type:** State diagram  
**Source:** [doc/state-machines.md › 3.1 Refund](../../../doc/state-machines.md#31-refund)  
**Files:** [SVG](04-refund-states.svg) · [PNG](04-refund-states.png) · [Mermaid source](04-refund-states.mmd)

```mermaid
stateDiagram-v2
    [*] --> requested : buyer request after postponement
    [*] --> approved : started by an admin or the system
    requested --> approved : admin approves
    requested --> rejected : admin declines
    approved --> processing : submitted to Pesapal
    processing --> succeeded
    processing --> failed
    failed --> processing : admin retries
```

This is a copy of the Mermaid block in [doc/state-machines.md](../../../doc/state-machines.md#31-refund). When it changes, update the source document, this file and `04-refund-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
