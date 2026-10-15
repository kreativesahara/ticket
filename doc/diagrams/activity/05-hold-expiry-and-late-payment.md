# Buyer journey: hold expiry and late payment

**Type:** Activity diagram (Mermaid flowchart)  
**Based on:** [state-machines.md §2, §3, §6](../../state-machines.md#6-inventory-reservation), [limitation.md 5.4, 7.7](../../../limitation.md#7-payments)  
**Files:** [SVG](05-hold-expiry-and-late-payment.svg) · [PNG](05-hold-expiry-and-late-payment.png) · [Mermaid source](05-hold-expiry-and-late-payment.mmd)

What happens when a hold runs out or the order is cancelled before payment, and when Pesapal confirms a payment after that.

```mermaid
%%{init: {"flowchart": {"wrappingWidth": 320}}}%%
flowchart TD
    classDef startNode fill:#111827,stroke:#111827,color:#ffffff
    classDef endNode fill:#111827,stroke:#111827,color:#ffffff
    classDef buyer fill:#eef2ff,stroke:#4f46e5,color:#1e1b4b
    classDef system fill:#ecfdf5,stroke:#059669,color:#064e3b
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-dasharray:4 3,color:#374151
    classDef staff fill:#fdf4ff,stroke:#a21caf,color:#4a044e
    classDef decision fill:#fff7ed,stroke:#ea580c,color:#7c2d12

    expiry(["Start: hold-expiry job,<br/>every minute"]):::startNode
    cancel(["Start: buyer cancels checkout,<br/>event cancelled or<br/>organizer suspended"]):::startNode
    s1["Find active reservations<br/>past expires_at"]:::system
    s2["Expire the order with<br/>UPDATE … WHERE status = 'pending'"]:::system
    s3["Cancel the order with<br/>UPDATE … WHERE status = 'pending'"]:::system
    d1{"Row updated?"}:::decision
    raced(["End: payment was<br/>confirmed first"]):::endNode
    s4["Reservations expired or released;<br/>reserved counts reduced; seats available;<br/>promo use released; pending payments expired"]:::system
    a1["Buyer sees the time ran out<br/>and can order again"]:::buyer
    s5["Reconciliation keeps checking the<br/>expired payment with Pesapal"]:::system
    d2{"Pesapal reports<br/>completed?"}:::decision
    released(["End: places back on sale"]):::endNode
    s6["Payment rejected:<br/>late confirmation"]:::system
    s7["Create and submit an automatic refund,<br/>with no approval step; alert an admin"]:::system
    p1["Pesapal processes the refund"]:::external
    d3{"Refund succeeded?"}:::decision
    t1["A platform admin<br/>retries the refund"]:::staff
    s8["Payment refunded"]:::system
    refunded(["End: buyer refunded;<br/>may order again"]):::endNode

    expiry --> s1 --> s2 --> d1
    cancel --> s3 --> d1
    d1 -->|"no"| raced
    d1 -->|"yes"| s4 --> a1 --> s5 --> d2
    d2 -->|"no: failed, cancelled<br/>or never paid"| released
    d2 -->|"yes"| s6 --> s7 --> p1 --> d3
    d3 -->|"no"| t1 --> p1
    d3 -->|"yes"| s8 --> refunded
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

- A late payment never creates a new hold. It is refunded automatically, and the buyer can order again.
- The expiry job is safe to run twice: its conditional update changes a row only once.

## Activity diagrams

1. [Buyer journey overview](01-buyer-journey-overview.md)
2. [Choose tickets and place a hold](02-choose-tickets-and-hold.md)
3. [Promo code, review and payment](03-promo-review-and-payment.md)
4. [Payment confirmation and ticket issuance](04-payment-confirmation-and-issuance.md)
5. [Hold expiry and late payment](05-hold-expiry-and-late-payment.md)
6. [Delegation claim](06-delegation-claim.md)
7. [Ticket retrieval and transfer](07-ticket-retrieval-and-transfer.md)
8. [Organizer: set up ticket waves](08-set-up-ticket-waves.md)
