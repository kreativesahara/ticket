# Organizer: set up ticket waves

**Type:** Activity diagram (Mermaid flowchart)  
**Based on:** [v1.md FR-04](../../../v1.md#fr-04--ticket-types-and-categories), [limitation.md 4.12–4.14](../../../limitation.md#4-tickets-prices-and-capacity), [state-machines.md §6](../../state-machines.md#6-inventory-reservation)  
**Files:** [SVG](08-set-up-ticket-waves.svg) · [PNG](08-set-up-ticket-waves.png) · [Mermaid source](08-set-up-ticket-waves.mmd)

How organizer staff set up a category's ticket waves while building an event, and how they move a wave's unsold tickets to a later wave.

```mermaid
%%{init: {"flowchart": {"wrappingWidth": 320}}}%%
flowchart TD
    classDef startNode fill:#111827,stroke:#111827,color:#ffffff
    classDef endNode fill:#111827,stroke:#111827,color:#ffffff
    classDef system fill:#ecfdf5,stroke:#059669,color:#064e3b
    classDef staff fill:#fdf4ff,stroke:#a21caf,color:#4a044e
    classDef decision fill:#fff7ed,stroke:#ea580c,color:#7c2d12
    classDef stop fill:#fef2f2,stroke:#dc2626,color:#7f1d1d

    start(["Start: event setup,<br/>tickets step"]):::startNode
    a1["Choose a ticket category"]:::staff
    d1{"Sell this category<br/>in waves?"}:::decision
    a2["Add ordinary ticket options: type,<br/>number of tickets, price and sales window"]:::staff
    a3["Add a wave: name,<br/>start date and end date"]:::staff
    s1["Check the wave doesn't overlap<br/>the category's other waves"]:::system
    d2{"Overlaps?"}:::decision
    a4["Add the wave's ticket options:<br/>type, number of tickets and price"]:::staff
    s2["Check that all ticket options together,<br/>public and set aside, stay within<br/>the event's capacity"]:::system
    d3{"Within capacity?"}:::decision
    s3["Save the wave; its options take<br/>the wave's dates as their sales window"]:::system
    d4{"Another wave?"}:::decision
    m1{" "}:::decision
    d5{"Another category?"}:::decision
    done(["End: publish the event<br/>when it's ready"]):::endNode

    ended(["Start: a wave has ended<br/>with tickets unsold"]):::startNode
    b1["Choose the ended option, a later wave's<br/>option in the same category, and how many<br/>tickets to move"]:::staff
    s4["Lower the ended option's capacity:<br/>UPDATE … WHERE capacity - sold_count<br/>- reserved_count >= :n"]:::system
    d6{"Row updated?"}:::decision
    s5["Raise the later option's capacity,<br/>record the move in the audit log<br/>and commit"]:::system
    x1["Roll back: fewer unsold tickets<br/>than requested"]:::stop
    moved(["End: the tickets go on sale<br/>with the later wave"]):::endNode
    refused((("End"))):::endNode

    start --> a1 --> d1
    d1 -->|"no"| a2 --> m1
    d1 -->|"yes"| a3 --> s1 --> d2
    d2 -->|"yes: change the dates"| a3
    d2 -->|"no"| a4 --> s2 --> d3
    d3 -->|"no: lower the numbers"| a4
    d3 -->|"yes"| s3 --> d4
    d4 -->|"yes"| a3
    d4 -->|"no"| m1
    m1 --> d5
    d5 -->|"yes"| a1
    d5 -->|"no"| done

    ended --> b1 --> s4 --> d6
    d6 -->|"yes"| s5 --> moved
    d6 -->|"no"| x1 --> refused
```

## How to read it

Mermaid has no activity diagram type, so this is drawn as a flowchart that follows UML activity conventions.

| Element | Meaning |
|---|---|
| Black circle or pill | Start or end; a label says where the flow continues |
| Diamond with text | Decision |
| Small empty diamond | Merge: alternative paths join again |
| Black bar | Fork or join: the paths between two bars run in parallel |
| Double-edged box | A sub-activity drawn in its own diagram |
| Blue box | Buyer action |
| Green box | Platform action (website or API) |
| Grey dashed box | Pesapal or the email service |
| Purple box | Organizer staff or platform admin action |
| Red box | The journey stops here |

## Notes

- Waves open on their start dates only. There is no automatic advance when a wave sells out, and no automatic rollover when it ends (decision #23).
- The move takes tickets only if the conditional update finds enough unsold ones, so it can never take tickets that are sold or held, even while buyers are checking out.
- The same move action returns a delegation's unclaimed tickets to public sale ([v1.md FR-05](../../../v1.md#fr-05--delegations-and-inventory)).
- A category's waves setting can change only while nothing in that category has been sold or held.

## Activity diagrams

1. [Buyer journey overview](01-buyer-journey-overview.md)
2. [Choose tickets and place a hold](02-choose-tickets-and-hold.md)
3. [Promo code, review and payment](03-promo-review-and-payment.md)
4. [Payment confirmation and ticket issuance](04-payment-confirmation-and-issuance.md)
5. [Hold expiry and late payment](05-hold-expiry-and-late-payment.md)
6. [Delegation claim](06-delegation-claim.md)
7. [Ticket retrieval and transfer](07-ticket-retrieval-and-transfer.md)
8. [Organizer: set up ticket waves](08-set-up-ticket-waves.md)
