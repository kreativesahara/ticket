# Use cases: overview

**Type:** Use case diagram (Mermaid flowchart)  
**Based on:** [v1.md §3 Permissions matrix](../../../v1.md#3-permissions-matrix), [v1.md §2](../../../v1.md#2-functional-requirements), [doc/roleandprivileage.md](../../roleandprivileage.md)  
**Files:** [SVG](00-overview.svg) · [PNG](00-overview.png) · [Mermaid source](00-overview.mmd)

Every actor and the main things each one does. Diagrams 01–06 break each actor down.

```mermaid
flowchart LR
    classDef actor fill:#ffffff,stroke:#1f2937,stroke-width:2px,color:#111827
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-width:1px,stroke-dasharray:4 3,color:#374151
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1px,color:#1e1b4b

    Buyer["«actor»<br/><b>Buyer / ticket holder</b><br/>guest, no sign-in"]:::actor
    Guard["«actor»<br/><b>Guard</b>"]:::actor
    Staff["«actor»<br/><b>Organizer staff</b>"]:::actor
    Owner["«actor»<br/><b>Event owner</b>"]:::actor
    Admin["«actor»<br/><b>Platform admin</b>"]:::actor
    Scheduler["«actor»<br/><b>Scheduler</b><br/>cron jobs"]:::actor

    subgraph SYS["Event ticketing platform"]
        subgraph PUB["Public website"]
            A(["Browse ticket options and buy tickets"]):::usecase
            B(["Claim delegation tickets"]):::usecase
            C(["Retrieve, download and transfer tickets"]):::usecase
        end
        subgraph GATE["Guard scanner"]
            E(["Sign in with a guard account"]):::usecase
            I(["Scan and redeem tickets at the gate"]):::usecase
        end
        subgraph ORG["Organizer area"]
            D(["Sign in with Google"]):::usecase
            F(["Manage organizer, team and guard accounts"]):::usecase
            G(["Manage events, tickets, waves, seats and promotions"]):::usecase
            H(["View, filter and export the guest list"]):::usecase
            J(["View sales, attendance and settlement reports"]):::usecase
        end
        subgraph ADM["Admin area"]
            K(["Administer organizers, users, categories and personal data"]):::usecase
            L(["Refund tickets"]):::usecase
        end
        subgraph BG["Background processing"]
            M(["Confirm payments and issue tickets"]):::usecase
            N(["Expire holds and close events"]):::usecase
        end
    end

    Google["«system»<br/><b>Google</b><br/>OAuth"]:::external
    Pesapal["«system»<br/><b>Pesapal</b><br/>M-Pesa and card"]:::external
    Email["«system»<br/><b>Email service</b>"]:::external

    Buyer --- A
    Buyer --- B
    Buyer --- C
    Buyer ---|"request after postponement"| L
    Guard --- E
    Guard ---|"assigned events and gates"| I
    Staff --- D
    Staff --- F
    Staff --- G
    Staff --- J
    Staff --- H
    Owner --- D
    Owner ---|"assigned events only"| H
    Admin --- D
    Admin ---|"any organizer"| F
    Admin ---|"any organizer"| G
    Admin --- J
    Admin --- H
    Admin --- K
    Admin ---|"starts every refund"| L
    Scheduler --- M
    Scheduler --- N

    C --- Email
    A --- Pesapal
    L --- Pesapal
    M --- Pesapal
    M --- Email
    D --- Google
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

- Platform admins can act on any organizer, but they don't scan tickets: only guard accounts scan.
- Platform admins also carry out personal data deletion requests ([v1.md FR-15](../../../v1.md#fr-15--personal-data-deletion)).

## Use case diagrams

0. [Overview](00-overview.md)
1. [Buyer and ticket holder](01-buyer-and-ticket-holder.md)
2. [Organizer staff](02-organizer-staff.md)
3. [Event owner](03-event-owner.md)
4. [Guard](04-guard.md)
5. [Platform admin](05-platform-admin.md)
6. [Automated processes](06-automated-processes.md)
