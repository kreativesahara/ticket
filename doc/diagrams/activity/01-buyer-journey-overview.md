# Buyer journey: overview

**Type:** Activity diagram (Mermaid flowchart)  
**Based on:** [v1.md FR-06 to FR-09](../../../v1.md#fr-06--purchasing-and-orders), [limitation.md §5–§8](../../../limitation.md#5-buying-tickets), [doc/state-machines.md](../../state-machines.md)  
**Files:** [SVG](01-buyer-journey-overview.svg) · [PNG](01-buyer-journey-overview.png) · [Mermaid source](01-buyer-journey-overview.mmd)

How a buyer gets a ticket, from finding the event to showing the ticket at the gate. Buyers find events on the landing page, which lists every on-sale event and its public tickets, or through the event's own link or gate QR code. Each double-edged box is drawn in detail in its own diagram.

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
    classDef stop fill:#fef2f2,stroke:#dc2626,color:#7f1d1d

    start(("Start")):::startNode
    a1["Find an event on the landing page, or open<br/>the event link or scan its gate QR code"]:::buyer
    d1{"Arrived with a delegation<br/>claim link or code?"}:::decision
    a2["Open the event page"]:::buyer
    s1["Show the published event and its public<br/>offerings in their sales window;<br/>delegation tickets are never listed"]:::system
    d2{"On sale with<br/>tickets left?"}:::decision
    x1["Not on sale or sold out"]:::stop
    a3[["Choose tickets, seats and details<br/>see 02"]]:::buyer
    s2["Hold the tickets for 15 minutes;<br/>create a pending order"]:::system
    a4[["Apply a promo code and review the total<br/>see 03"]]:::buyer
    a5[["Claim delegation tickets<br/>see 06"]]:::buyer
    d3{"Total is zero?"}:::decision
    a6[["Pay by M-Pesa or card on Pesapal<br/>see 03"]]:::external
    d4{"Pesapal result,<br/>confirmed by status check"}:::decision
    d5{"Hold still active?"}:::decision
    s3["Order expires; places go back on sale;<br/>a late payment is refunded automatically<br/>see 05"]:::system
    s4[["Issue tickets<br/>see 04"]]:::system
    d6{"Offering needs<br/>organizer approval?"}:::decision
    t1["Organizer staff review<br/>the Media request"]:::staff
    d7{"Approved?"}:::decision
    x2["Request rejected;<br/>a platform admin refunds it"]:::stop
    s5["Email the order confirmation,<br/>ticket link and receipt"]:::system
    a7[["Open the ticket link: view, download<br/>or transfer tickets, see 07"]]:::buyer
    a8["Show the QR code or short code<br/>to a guard at the gate"]:::buyer
    done((("End"))):::endNode
    stopEnd((("End"))):::endNode

    start --> a1 --> d1
    d1 -->|"no"| a2 --> s1 --> d2
    d2 -->|"no"| x1 --> stopEnd
    d2 -->|"yes"| a3 --> s2 --> a4 --> d3
    d1 -->|"yes"| a5 --> d3
    d3 -->|"yes: free ticket, free claim<br/>or 100% discount"| s4
    d3 -->|"no"| a6 --> d4
    d4 -->|"completed and accepted"| s4
    d4 -->|"failed or cancelled"| d5
    d5 -->|"yes: try again"| a6
    d5 -->|"no"| s3
    d4 -->|"hold ended first"| s3
    s3 --> stopEnd
    s4 --> d6
    d6 -->|"no"| s5
    d6 -->|"yes: Media"| t1 --> d7
    d7 -->|"yes"| s5
    d7 -->|"no"| x2 --> stopEnd
    s5 --> a7 --> a8 --> done
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

## The buyer journey

1. [Buyer journey overview](01-buyer-journey-overview.md)
2. [Choose tickets and place a hold](02-choose-tickets-and-hold.md)
3. [Promo code, review and payment](03-promo-review-and-payment.md)
4. [Payment confirmation and ticket issuance](04-payment-confirmation-and-issuance.md)
5. [Hold expiry and late payment](05-hold-expiry-and-late-payment.md)
6. [Delegation claim](06-delegation-claim.md)
7. [Ticket retrieval and transfer](07-ticket-retrieval-and-transfer.md)
