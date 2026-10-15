# Buyer journey: delegation claim

**Type:** Activity diagram (Mermaid flowchart)  
**Based on:** [doc/sponsored.md](../../sponsored.md), [v1.md FR-05](../../../v1.md#fr-05--delegations-and-inventory), [hurdles.md H3](../../../hurdles.md#h3-no-path-for-free-tickets-or-delegation-claims--decided)  
**Files:** [SVG](06-delegation-claim.svg) · [PNG](06-delegation-claim.png) · [Mermaid source](06-delegation-claim.mmd)

A sponsor's guest claims tickets through the delegation's private link or access code.

```mermaid
%%{init: {"flowchart": {"wrappingWidth": 320}}}%%
flowchart TD
    classDef startNode fill:#111827,stroke:#111827,color:#ffffff
    classDef endNode fill:#111827,stroke:#111827,color:#ffffff
    classDef buyer fill:#eef2ff,stroke:#4f46e5,color:#1e1b4b
    classDef system fill:#ecfdf5,stroke:#059669,color:#064e3b
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-dasharray:4 3,color:#374151
    classDef decision fill:#fff7ed,stroke:#ea580c,color:#7c2d12
    classDef stop fill:#fef2f2,stroke:#dc2626,color:#7f1d1d

    start(("Start")):::startNode
    a1["Open the private claim link,<br/>or enter the access code"]:::buyer
    s1["Look up the delegation by<br/>the hashed token or code"]:::system
    d1{"Delegation active and<br/>event on sale?"}:::decision
    x1["Show 'link or code not valid'"]:::stop
    s2["Show the allowed categories<br/>and remaining quota"]:::system
    a2["Choose an allowed category<br/>and quantity"]:::buyer
    a3["Enter the attendee details<br/>the event asks for"]:::buyer
    d2{"CAPTCHA required?"}:::decision
    a4["Complete the CAPTCHA"]:::buyer
    m1{" "}:::decision
    a5["Submit the claim"]:::buyer
    s3["In one transaction, hold the offering's capacity<br/>and the delegation's quota with atomic<br/>conditional updates"]:::system
    d3{"Both holds<br/>succeed?"}:::decision
    x2["Show 'allocation exhausted'"]:::stop
    s4["Create the pending order and checkout token;<br/>price from the delegation's price rule"]:::system
    d4{"Total is zero?"}:::decision
    s5["Order paid and completed with no payment;<br/>tickets issued with the delegation"]:::system
    s6["Show the ticket link at once;<br/>email it through the outbox"]:::system
    a6[["Pay by M-Pesa or card on Pesapal<br/>see 03"]]:::external
    done(["End: tickets issued"]):::endNode
    failed((("End"))):::endNode

    start --> a1 --> s1 --> d1
    d1 -->|"no"| x1 --> failed
    d1 -->|"yes"| s2 --> a2 --> a3 --> d2
    d2 -->|"yes"| a4 --> m1
    d2 -->|"no"| m1
    m1 --> a5 --> s3 --> d3
    d3 -->|"no"| x2 --> failed
    d3 -->|"yes"| s4 --> d4
    d4 -->|"yes"| s5 --> s6 --> done
    d4 -->|"no"| a6 --> done
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

- A delegation ticket uses its offering's capacity and the delegation's quota, both held in one transaction, so it's never counted twice.
- A free claim skips Pesapal entirely and issues tickets straight away. A claim with a price goes through the normal payment flow.

## The buyer journey

1. [Buyer journey overview](01-buyer-journey-overview.md)
2. [Choose tickets and place a hold](02-choose-tickets-and-hold.md)
3. [Promo code, review and payment](03-promo-review-and-payment.md)
4. [Payment confirmation and ticket issuance](04-payment-confirmation-and-issuance.md)
5. [Hold expiry and late payment](05-hold-expiry-and-late-payment.md)
6. [Delegation claim](06-delegation-claim.md)
7. [Ticket retrieval and transfer](07-ticket-retrieval-and-transfer.md)
