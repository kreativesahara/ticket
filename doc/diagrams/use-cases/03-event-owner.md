# Use cases: event owner

**Type:** Use case diagram (Mermaid flowchart)  
**Based on:** [v1.md §3](../../../v1.md#3-permissions-matrix), [v1.md FR-13](../../../v1.md#fr-13--reporting-and-auditability), [doc/roleandprivileage.md §2.3](../../roleandprivileage.md#23-event-owner)  
**Files:** [SVG](03-event-owner.svg) · [PNG](03-event-owner.png) · [Mermaid source](03-event-owner.mmd)

Event owners can only view, filter and export the guest list of the events they are assigned to.

```mermaid
flowchart LR
    classDef actor fill:#ffffff,stroke:#1f2937,stroke-width:2px,color:#111827
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-width:1px,stroke-dasharray:4 3,color:#374151
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1px,color:#1e1b4b

    Owner["«actor»<br/><b>Event owner</b><br/>assigned events only"]:::actor

    subgraph SYS["Ticketing platform: organizer area, read-only"]
        O1(["Sign in with Google"]):::usecase
        O2(["Accept an event owner invitation"]):::usecase
        O3(["View the guest list of an assigned event"]):::usecase
        O4(["Filter by category, delegation, purchase date or check-in status"]):::usecase
        O5(["Export the guest list as CSV"]):::usecase
    end

    Google["«system»<br/><b>Google</b><br/>OAuth"]:::external

    Owner --- O1
    Owner --- O2
    Owner --- O3
    Owner --- O4
    Owner --- O5

    O2 -.->|"«include»"| O1
    O4 -.->|"«extend»"| O3
    O5 -.->|"«extend»"| O3

    O1 --- Google
```

## How to read it

Mermaid has no use case diagram type, so this is drawn as a flowchart that follows UML use case conventions. Roles and permissions come from [v1.md §3](../../../v1.md#3-permissions-matrix).

| Element | Meaning |
|---|---|
| Box marked «actor» | A role that uses the system |
| Dashed box marked «system» | An external system |
| Oval | A use case |
| Large box | The system boundary; inner boxes group related use cases |
| Solid line | The actor takes part in the use case |
| Dashed arrow «include» | The base use case always includes the target |
| Dashed arrow «extend» | The source use case adds to the target, under the stated condition |

## Notes

- The guest list shows each ticket holder's name, ticket category, delegation and check-in status only: no contact details, ID numbers, sales, payment or settlement figures.
- Event owners never change anything, and never trigger a state transition ([state-machines.md](../../state-machines.md#conventions)).
