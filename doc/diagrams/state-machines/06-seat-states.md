# Seat state machine

**Type:** State diagram  
**Source:** [doc/state-machines.md › 5. Seat](../../../doc/state-machines.md#5-seat)  
**Files:** [SVG](06-seat-states.svg) · [PNG](06-seat-states.png) · [Mermaid source](06-seat-states.mmd)

```mermaid
stateDiagram-v2
    [*] --> available : created
    available --> held : chosen at checkout
    held --> available : hold expires or order cancelled
    held --> sold : order paid
    sold --> available : ticket refunded or revoked
    available --> [*] : removed by organizer
```

This is a copy of the Mermaid block in [doc/state-machines.md](../../../doc/state-machines.md#5-seat). When it changes, update the source document, this file and `06-seat-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
