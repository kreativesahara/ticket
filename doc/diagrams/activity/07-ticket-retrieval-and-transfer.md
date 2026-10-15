# Buyer journey: ticket retrieval and transfer

**Type:** Activity diagram (Mermaid flowchart)  
**Based on:** [v1.md FR-09](../../../v1.md#fr-09--digital-tickets-and-qr-codes), [limitation.md §8](../../../limitation.md#8-tickets-and-delivery), [state-machines.md §4](../../state-machines.md#4-ticket)  
**Files:** [SVG](07-ticket-retrieval-and-transfer.svg) · [PNG](07-ticket-retrieval-and-transfer.png) · [Mermaid source](07-ticket-retrieval-and-transfer.mmd)

How the holder gets to their tickets, downloads them, transfers one, and shows it at the gate.

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

    start(["Start: tickets issued"]):::startNode
    d1{"Has the ticket link<br/>from the email?"}:::decision
    a1["Ask for a new link by<br/>entering the email address"]:::buyer
    s1["If that email has orders,<br/>email a new retrieval link"]:::system
    s2["Show the same message either way,<br/>so no order is revealed"]:::system
    e1["Email service delivers the link"]:::external
    a2["Open the link"]:::buyer
    s3["Look up the token by its hash"]:::system
    d2{"Token valid, not<br/>revoked or expired?"}:::decision
    s4["Show each ticket: event, category, holder name,<br/>ticket number, QR code, short code,<br/>seat or delegation"]:::system
    d3{"What next?"}:::decision
    a3["Download the ticket"]:::buyer
    d4{"Event published, before it starts,<br/>identity field not ID number?"}:::decision
    x1["Transfer not available"]:::stop
    a4["Enter the recipient's name and email,<br/>and phone number for phone events"]:::buyer
    s5["In one transaction: new attendee; new QR token<br/>and short code, old ones stop working;<br/>transfer recorded"]:::system
    s6["Email the recipient their own link;<br/>the old link no longer shows the ticket"]:::system
    transferred(["End: the recipient<br/>holds the ticket"]):::endNode
    m1{" "}:::decision
    a5["At the gate, show the QR code,<br/>or the short code if it won't scan"]:::buyer
    done((("End"))):::endNode

    start --> d1
    d1 -->|"no"| a1 --> s1 --> s2 --> e1 --> a2
    d1 -->|"yes"| a2
    a2 --> s3 --> d2
    d2 -->|"no"| a1
    d2 -->|"yes"| s4 --> d3
    d3 -->|"download"| a3 --> m1
    d3 -->|"transfer"| d4
    d4 -->|"no"| x1 --> m1
    d4 -->|"yes"| a4 --> s5 --> s6 --> transferred
    d3 -->|"go to the event"| m1
    m1 --> a5 --> done
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

- Retrieval tokens are stored only as hashes, so a lost link is replaced, never shown again.
- The QR code holds no personal information.
- Transferring gives the ticket new credentials. Reinstating a revoked ticket keeps them.

## The buyer journey

1. [Buyer journey overview](01-buyer-journey-overview.md)
2. [Choose tickets and place a hold](02-choose-tickets-and-hold.md)
3. [Promo code, review and payment](03-promo-review-and-payment.md)
4. [Payment confirmation and ticket issuance](04-payment-confirmation-and-issuance.md)
5. [Hold expiry and late payment](05-hold-expiry-and-late-payment.md)
6. [Delegation claim](06-delegation-claim.md)
7. [Ticket retrieval and transfer](07-ticket-retrieval-and-transfer.md)
