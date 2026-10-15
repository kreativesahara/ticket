# Use cases: guard

**Type:** Use case diagram (Mermaid flowchart)  
**Based on:** [v1.md FR-01, FR-10](../../../v1.md#fr-10--online-gate-access-control), [limitation.md §9](../../../limitation.md#9-gate-entry)  
**Files:** [SVG](04-guard.svg) · [PNG](04-guard.png) · [Mermaid source](04-guard.mmd)

Guards sign in with an email and password account their organizer created, valid for one event, and scan tickets at their assigned gates.

```mermaid
flowchart LR
    classDef actor fill:#ffffff,stroke:#1f2937,stroke-width:2px,color:#111827
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-width:1px,stroke-dasharray:4 3,color:#374151
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1px,color:#1e1b4b

    Guard["«actor»<br/><b>Guard</b><br/>guard password account,<br/>one event at a time"]:::actor

    subgraph SYS["Ticketing platform: guard scanner"]
        U1(["Sign in with email and password"]):::usecase
        U2(["Set own password"]):::usecase
        U3(["Select assigned event and gate"]):::usecase
        U4(["Scan a ticket QR code"]):::usecase
        U5(["Type a 6-character short code"]):::usecase
        U6(["Validate and redeem the ticket once"]):::usecase
        U7(["Check holder name and last 4 digits"]):::usecase
        U8(["View gate session attendance"]):::usecase
        U9(["View own scan activity"]):::usecase
    end

    Guard --- U1
    Guard --- U3
    Guard --- U4
    Guard --- U8
    Guard --- U9

    U2 -.->|"«extend» first sign-in"| U1
    U5 -.->|"«extend» QR code can't be read"| U4
    U4 -.->|"«include»"| U6
    U5 -.->|"«include»"| U6
    U7 -.->|"«extend» event has an identity field"| U6

    %% layout only: keep the actor in the leftmost column
    Guard ~~~ U2
    Guard ~~~ U5
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

- Only guard password accounts scan. Each scan, valid or not, is recorded with the guard who made it.
- Short-code entry locks after 5 invalid codes in a row, until organizer staff unlock it; QR scanning keeps working.
- Each ticket admits once. If the scanner can't reach the platform, it shows "Unable to verify, do not admit".
- The guard's password is deleted when the event closes, and the assignment ends.
