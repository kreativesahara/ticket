# Use cases: organizer staff

**Type:** Use case diagram (Mermaid flowchart)  
**Based on:** [v1.md §3](../../../v1.md#3-permissions-matrix), [v1.md FR-02, FR-03](../../../v1.md#fr-02--multi-organizer-tenancy), [doc/roleandprivileage.md §2.2](../../roleandprivileage.md#22-organizer-staff)  
**Files:** [SVG](02-organizer-staff.svg) · [PNG](02-organizer-staff.png) · [Mermaid source](02-organizer-staff.mmd)

Organizer staff have full control of their own organizer: team, events, ticketing, guard accounts and reports.

```mermaid
flowchart LR
    classDef actor fill:#ffffff,stroke:#1f2937,stroke-width:2px,color:#111827
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-width:1px,stroke-dasharray:4 3,color:#374151
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1px,color:#1e1b4b

    Staff["«actor»<br/><b>Organizer staff</b><br/>own organizer only"]:::actor

    subgraph SYS["Ticketing platform: organizer area"]
        subgraph P1["Account and team"]
            S1(["Sign in with Google"]):::usecase
            S2(["Accept a staff invitation"]):::usecase
            S3(["Apply for an organizer account"]):::usecase
            S4(["Manage organizer profile and verification details"]):::usecase
            S5(["Invite and manage staff"]):::usecase
            S6(["Invite and assign event owners"]):::usecase
        end
        subgraph P2["Events and ticketing"]
            E1(["Create, edit or delete draft events"]):::usecase
            E2(["Publish, postpone, cancel or archive events"]):::usecase
            E11(["Share the event link, QR code and poster"]):::usecase
            E3(["Set identity field, collection mode and questions"]):::usecase
            E4(["Manage gates and entry times"]):::usecase
            E5(["Configure ticket types, categories and offerings"]):::usecase
            E12(["Set up ticket waves for a category"]):::usecase
            E13(["Move unsold tickets"]):::usecase
            E6(["Manage delegations and their set-aside tickets"]):::usecase
            E7(["Manage seating sections and seats"]):::usecase
            E8(["Configure promotions"]):::usecase
            E9(["Approve or issue Media tickets"]):::usecase
            E10(["Revoke or reinstate tickets"]):::usecase
        end
        subgraph P3["Gate access"]
            G1(["Create guard accounts"]):::usecase
            G2(["Assign guards to events and gates"]):::usecase
            G3(["Issue a one-event temporary password"]):::usecase
            G4(["Unlock short-code entry"]):::usecase
        end
        subgraph P4["Guest data and reports"]
            R1(["View, filter and export the guest list"]):::usecase
            R2(["View and export sales reports"]):::usecase
            R3(["View attendance and gate activity"]):::usecase
            R4(["View the settlement report"]):::usecase
            R5(["View event audit records"]):::usecase
        end
    end

    Google["«system»<br/><b>Google</b><br/>OAuth"]:::external
    Email["«system»<br/><b>Email service</b>"]:::external

    Staff --- S1
    Staff --- S2
    Staff --- S3
    Staff --- S4
    Staff --- S5
    Staff --- S6
    Staff --- E1
    Staff --- E2
    Staff --- E11
    Staff --- E3
    Staff --- E4
    Staff --- E5
    Staff --- E12
    Staff --- E13
    Staff --- E6
    Staff --- E7
    Staff --- E8
    Staff --- E9
    Staff --- E10
    Staff --- G1
    Staff --- G2
    Staff --- G4
    Staff --- R1
    Staff --- R2
    Staff --- R3
    Staff --- R4
    Staff ---|"read-only"| R5

    S2 -.->|"«include»"| S1
    S3 -.->|"«include»"| S1
    G2 -.->|"«include»"| G3

    S1 --- Google
    S5 --- Email
    S6 --- Email
    E2 ---|"change and cancellation emails"| Email
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

- Staff can't start refunds or scan tickets (v1.md §3). A staff member who works a gate uses a guard account.
- Every organizer must keep at least one active staff member.
- **Ticket waves:** for each category, staff choose whether it sells in waves. Each wave has its own dates and its own ticket options (type, number of tickets, price), and a category's waves never overlap. Staff move a wave's unsold tickets by hand ([v1.md FR-04](../../../v1.md#fr-04--ticket-types-and-categories), [activity 08](../activity/08-set-up-ticket-waves.md)).
- **Event link, QR code and poster:** staff copy the event's link and download its QR code as PNG or SVG, or as a printable A4 poster for the gate ([v1.md FR-14](../../../v1.md#fr-14--landing-page-and-event-links)).
- **Delegations:** each delegation's tickets are set aside as its own offerings; staff can return unclaimed ones to public sale with the same move action.
- Platform admins can do all of this for any organizer ([05 platform admin](05-platform-admin.md)).
