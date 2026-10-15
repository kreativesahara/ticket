# Use cases: buyer and ticket holder

**Type:** Use case diagram (Mermaid flowchart)  
**Based on:** [v1.md FR-06 to FR-09](../../../v1.md#fr-06--purchasing-and-orders), [limitation.md §5–§8](../../../limitation.md#5-buying-tickets), [doc/sponsored.md](../../sponsored.md)  
**Files:** [SVG](01-buyer-and-ticket-holder.svg) · [PNG](01-buyer-and-ticket-holder.png) · [Mermaid source](01-buyer-and-ticket-holder.mmd)

What buyers, delegation members and ticket holders do on the public website. None of them sign in.

```mermaid
flowchart LR
    classDef actor fill:#ffffff,stroke:#1f2937,stroke-width:2px,color:#111827
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-width:1px,stroke-dasharray:4 3,color:#374151
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1px,color:#1e1b4b

    Buyer["«actor»<br/><b>Buyer</b><br/>guest, no sign-in"]:::actor
    Member["«actor»<br/><b>Delegation member</b><br/>has claim link or code"]:::actor
    Holder["«actor»<br/><b>Ticket holder</b><br/>has retrieval link"]:::actor

    subgraph SYS["Ticketing platform: public website"]
        UC1(["Browse on-sale events and their public tickets on the landing page"]):::usecase
        UC1F(["Filter events by category"]):::usecase
        UC16(["Open an event from its link or gate QR code"]):::usecase
        UC2(["View an event page and its public tickets"]):::usecase
        UC3(["Buy tickets"]):::usecase
        UC4(["Choose offering and quantity"]):::usecase
        UC5(["Choose seats"]):::usecase
        UC6(["Enter purchaser or attendee details"]):::usecase
        UC7(["Complete CAPTCHA"]):::usecase
        UC8(["Apply promo code"]):::usecase
        UC9(["Pay by M-Pesa or card"]):::usecase
        UC10(["Claim delegation tickets"]):::usecase
        UC11(["Receive confirmation, receipt and ticket link"]):::usecase
        UC12(["View and download tickets"]):::usecase
        UC13(["Request a new ticket link"]):::usecase
        UC14(["Transfer a ticket"]):::usecase
        UC15(["Request a refund after postponement"]):::usecase
    end

    Pesapal["«system»<br/><b>Pesapal</b><br/>hosted checkout"]:::external
    Email["«system»<br/><b>Email service</b>"]:::external

    Buyer --- UC1
    Buyer --- UC16
    Buyer --- UC2
    Buyer --- UC3
    Buyer --- UC13
    Buyer --- UC15
    Member --- UC10
    Holder --- UC12
    Holder --- UC14

    UC1F -.->|"«extend»"| UC1
    UC16 -.->|"«include»"| UC2
    UC3 -.->|"«include»"| UC4
    UC3 -.->|"«include»"| UC6
    UC5 -.->|"«extend» reserved seating"| UC3
    UC7 -.->|"«extend» high-demand event"| UC3
    UC8 -.->|"«extend» buyer has a code"| UC3
    UC9 -.->|"«extend» total above zero"| UC3
    UC10 -.->|"«include»"| UC6
    UC9 -.->|"«extend» price rule not free"| UC10
    UC3 -.->|"«include»"| UC11
    UC10 -.->|"«include»"| UC11
    UC14 -.->|"«include»"| UC11

    %% layout only: keep actors in the leftmost column
    Buyer ~~~ UC1F
    Buyer ~~~ UC5
    Buyer ~~~ UC7
    Buyer ~~~ UC8
    Buyer ~~~ UC9
    Member ~~~ UC9
    Holder ~~~ UC9
    UC11 ~~~ Pesapal

    UC9 --- Pesapal
    UC11 --- Email
    UC13 --- Email
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

- The landing page lists every published event that is on sale, with its public tickets. Delegation (sponsored) tickets are never listed: they are reached only through the delegation's private claim link or access code ([doc/sponsored.md](../../sponsored.md)).
- Each event has its own link, and a gate QR code that opens it, so buyers can buy At the Gate tickets on their own phones ([hurdles.md H4](../../../hurdles.md#h4-at-the-gate-tickets-have-no-way-to-be-sold--decided)). There are no cash sales.
- Buyers reach an order only through its checkout token, and their tickets only through the retrieval link ([hurdles.md H11](../../../hurdles.md#h11-guest-buyers-have-no-defined-way-to-view-their-order--decided)).
- A ticket can be transferred until the event starts, except at events that ask for ID numbers.
- A refund request is possible only after a postponement, until `refund_requests_until`. A platform admin approves or declines it.
- The step-by-step journey is in the [activity diagrams](../activity/01-buyer-journey-overview.md).
