# Event state machine

**Type:** State diagram  
**Source:** [doc/state-machines.md › 1. Event](../../../doc/state-machines.md#1-event)  
**Files:** [SVG](01-event-states.svg) · [PNG](01-event-states.png) · [Mermaid source](01-event-states.mmd)

```mermaid
stateDiagram-v2
    [*] --> draft
    draft --> [*] : deleted (never published)
    draft --> published : publish
    published --> closed : entry ends (system)
    published --> cancelled : cancel
    closed --> archived : archive
    cancelled --> archived : archive
```

This is a copy of the Mermaid block in [doc/state-machines.md](../../../doc/state-machines.md#1-event). When it changes, update the source document, this file and `01-event-states.mmd` together, then re-render ([how](../../README.md#regenerating)).
