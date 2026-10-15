# Buyer journey: payment confirmation and ticket issuance

**Type:** Activity diagram (Mermaid flowchart)  
**Based on:** [v1.md §6](../../../v1.md#6-payment-to-ticket-issuance-workflow), [state-machines.md §2–§4](../../state-machines.md#2-order), [limitation.md §7, §8](../../../limitation.md#8-tickets-and-delivery)  
**Files:** [SVG](04-payment-confirmation-and-issuance.svg) · [PNG](04-payment-confirmation-and-issuance.png) · [Mermaid source](04-payment-confirmation-and-issuance.mmd)

How the platform turns a confirmed payment, or a zero total, into tickets, and how staff approve Media tickets.

```mermaid
%%{init: {"flowchart": {"wrappingWidth": 320}}}%%
flowchart TD
    classDef startNode fill:#111827,stroke:#111827,color:#ffffff
    classDef endNode fill:#111827,stroke:#111827,color:#ffffff
    classDef system fill:#ecfdf5,stroke:#059669,color:#064e3b
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-dasharray:4 3,color:#374151
    classDef staff fill:#fdf4ff,stroke:#a21caf,color:#4a044e
    classDef decision fill:#fff7ed,stroke:#ea580c,color:#7c2d12
    classDef bar fill:#111827,stroke:#111827,color:#111827

    ipn(["Start: IPN<br/>received"]):::startNode
    rec(["Start: reconciliation finds<br/>a pending payment"]):::startNode
    zero(["Start: order<br/>total is zero"]):::startNode
    s1["Record the notification<br/>in payment_webhook_events"]:::system
    m1{" "}:::decision
    s2["Ask Pesapal's transaction<br/>status endpoint"]:::system
    d1{"Pesapal status?"}:::decision
    s3["Change nothing;<br/>check again later"]:::system
    s4["Mark this attempt failed or cancelled;<br/>the buyer may retry while the hold lasts"]:::system
    s5["Begin a transaction;<br/>lock the order row"]:::system
    d2{"This payment already<br/>succeeded?"}:::decision
    s6["Change nothing:<br/>duplicate notification"]:::system
    d3{"Order still pending, amount and<br/>currency match, event published?"}:::decision
    s7["Payment rejected; create and submit<br/>an automatic refund; alert an admin"]:::system
    s8["Payment succeeded; order paid;<br/>reservations converted; seats sold;<br/>promo redemption recorded"]:::system
    s9["Begin a transaction; lock the order row;<br/>order paid with no payment row;<br/>reservations converted; seats sold"]:::system
    m2{" "}:::decision
    d4{"Offering needs<br/>organizer approval?"}:::decision
    s10["Create tickets as pending issuance;<br/>their places count as sold"]:::system
    s11["Create valid tickets: unique number,<br/>QR token and short code, each stored<br/>hashed and encrypted; order completed"]:::system
    m3{" "}:::decision
    s12["Write outbox messages: confirmation<br/>with the ticket link, and a receipt if paid"]:::system
    c1["Commit"]:::system
    c2["Commit"]:::system
    fork[" "]:::bar
    ack["Acknowledge Pesapal<br/>(IPN only)"]:::system
    q1["Queue worker sends the emails"]:::system
    e1["Email service delivers them"]:::external
    join[" "]:::bar
    ack2["Acknowledge Pesapal<br/>(IPN only)"]:::system
    issued(["End: tickets issued"]):::endNode
    noChange((("End"))):::endNode

    review(["Start: staff review a<br/>pending Media ticket"]):::startNode
    d5{"Approve?"}:::decision
    t1["Generate QR token and short code;<br/>ticket valid; email queued; order<br/>completed when every ticket is valid"]:::staff
    t2["Revoke with a reason; the place returns<br/>to sale; buyer emailed; a platform<br/>admin starts the refund"]:::staff
    reviewed((("End"))):::endNode

    ipn --> s1 --> m1
    rec --> m1
    m1 --> s2 --> d1
    d1 -->|"pending"| s3 --> ack2
    d1 -->|"failed or cancelled"| s4 --> ack2
    d1 -->|"completed"| s5 --> d2
    d2 -->|"yes"| s6 --> c2
    d2 -->|"no"| d3
    d3 -->|"no"| s7 --> c2
    c2 --> ack2 --> noChange
    d3 -->|"yes"| s8 --> m2
    zero --> s9 --> m2
    m2 --> d4
    d4 -->|"yes: Media"| s10 --> m3
    d4 -->|"no"| s11 --> m3
    m3 --> s12 --> c1 --> fork
    fork --> ack --> join
    fork --> q1 --> e1 --> join
    join --> issued

    review --> d5
    d5 -->|"yes"| t1 --> reviewed
    d5 -->|"no"| t2 --> reviewed
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

- Locking the order row decides the race between payment confirmation and hold expiry: exactly one wins ([05 hold expiry](05-hold-expiry-and-late-payment.md)).
- Ticket issuance is idempotent, so a repeated notification creates no extra tickets.
- Emails go through the transactional outbox after the commit. No transaction stays open while waiting for Pesapal or email.

## Activity diagrams

1. [Buyer journey overview](01-buyer-journey-overview.md)
2. [Choose tickets and place a hold](02-choose-tickets-and-hold.md)
3. [Promo code, review and payment](03-promo-review-and-payment.md)
4. [Payment confirmation and ticket issuance](04-payment-confirmation-and-issuance.md)
5. [Hold expiry and late payment](05-hold-expiry-and-late-payment.md)
6. [Delegation claim](06-delegation-claim.md)
7. [Ticket retrieval and transfer](07-ticket-retrieval-and-transfer.md)
8. [Organizer: set up ticket waves](08-set-up-ticket-waves.md)
