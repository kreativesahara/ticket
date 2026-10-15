# Payment state machine

**Type:** State diagram  
**Source:** [doc/state-machines.md › 3. Payment](../../../doc/state-machines.md#3-payment)  
**Files:** [SVG](03-payment-states.svg) · [PNG](03-payment-states.png) · [Mermaid source](03-payment-states.mmd)

```mermaid
stateDiagram-v2
    [*] --> pending : attempt started
    pending --> succeeded : confirmed and accepted
    pending --> rejected : confirmed but can't be accepted
    pending --> failed
    pending --> cancelled
    pending --> expired : order stopped waiting
    expired --> rejected : late confirmation
    rejected --> refunded : automatic refund succeeds
    succeeded --> refunded : refunds reach full amount
    succeeded --> reversed : Pesapal reports reversal
```

This is a copy of the Mermaid block in [doc/state-machines.md](../../../doc/state-machines.md#3-payment). When it changes, update the source document, this file and `03-payment-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
