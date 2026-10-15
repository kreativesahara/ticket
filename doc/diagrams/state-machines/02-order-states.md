# Order state machine

**Type:** State diagram  
**Source:** [doc/state-machines.md › 2. Order](../../../doc/state-machines.md#2-order)  
**Files:** [SVG](02-order-states.svg) · [PNG](02-order-states.png) · [Mermaid source](02-order-states.mmd)

```mermaid
stateDiagram-v2
    [*] --> pending : order created, holds placed
    pending --> paid : payment confirmed / zero total
    pending --> expired : hold expires
    pending --> cancelled : voided before payment
    paid --> completed : all tickets issued
    paid --> partially_refunded : partial refund
    paid --> refunded : full refund
    completed --> partially_refunded : partial refund
    completed --> refunded : full refund or reversal
    partially_refunded --> refunded : remaining tickets refunded
```

This is a copy of the Mermaid block in [doc/state-machines.md](../../../doc/state-machines.md#2-order). When it changes, update the source document, this file and `02-order-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
