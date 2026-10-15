# Use cases: platform admin

**Type:** Use case diagram (Mermaid flowchart)  
**Based on:** [v1.md §3](../../../v1.md#3-permissions-matrix), [v1.md FR-08, FR-13](../../../v1.md#fr-08--payments), [doc/roleandprivileage.md §2.1](../../roleandprivileage.md#21-platform-admin)  
**Files:** [SVG](05-platform-admin.svg) · [PNG](05-platform-admin.png) · [Mermaid source](05-platform-admin.mmd)

Platform admins run the platform: users, organizers, categories, refunds, settlements and oversight.

```mermaid
flowchart LR
    classDef actor fill:#ffffff,stroke:#1f2937,stroke-width:2px,color:#111827
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-width:1px,stroke-dasharray:4 3,color:#374151
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1px,color:#1e1b4b

    Admin["«actor»<br/><b>Platform admin</b><br/>all organizers"]:::actor

    subgraph SYS["Ticketing platform: admin area"]
        subgraph P1["Platform"]
            A1(["Sign in with Google"]):::usecase
            A2(["Manage platform users and suspensions"]):::usecase
            A3(["Designate platform admins"]):::usecase
            A4(["Manage event categories"]):::usecase
        end
        subgraph P2["Organizers"]
            A5(["Approve organizer applications"]):::usecase
            A6(["Verify organizer details and payout account"]):::usecase
            A7(["Suspend organizers"]):::usecase
            A8(["Act on any organizer's events and team"]):::usecase
        end
        subgraph P3["Money"]
            A9(["Start refunds, ticket by ticket"]):::usecase
            A10(["Approve or decline postponement refund requests"]):::usecase
            A11(["Retry failed refunds"]):::usecase
            A12(["Submit refund to Pesapal"]):::usecase
            A13(["Search payments and reconcile"]):::usecase
            A14(["View settlement reports"]):::usecase
            A15(["Record manual payouts"]):::usecase
        end
        subgraph P4["Oversight"]
            A16(["View platform-wide reports"]):::usecase
            A17(["View all audit records"]):::usecase
        end
    end

    Google["«system»<br/><b>Google</b><br/>OAuth"]:::external
    Pesapal["«system»<br/><b>Pesapal</b><br/>refunds API"]:::external

    Admin --- A1
    Admin --- A2
    Admin --- A3
    Admin --- A4
    Admin --- A5
    Admin --- A6
    Admin --- A7
    Admin ---|"every organizer staff use case"| A8
    Admin --- A9
    Admin --- A10
    Admin --- A11
    Admin --- A13
    Admin --- A14
    Admin --- A15
    Admin --- A16
    Admin --- A17

    A9 -.->|"«include»"| A12
    A10 -.->|"«include»"| A12
    A11 -.->|"«include»"| A12

    A1 --- Google
    A12 --- Pesapal
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

- Admins can do everything organizer staff can, for any organizer ([02 organizer staff](02-organizer-staff.md)), but they don't scan tickets.
- Only admins start refunds, ticket by ticket. The automatic refunds of late or mismatched payments are system actions ([06 automated processes](06-automated-processes.md)).
- Platform admins are designated by an existing admin or a system command, never through sign-in.
