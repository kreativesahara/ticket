# Buyer journey: promo code, review and payment

**Type:** Activity diagram (Mermaid flowchart)  
**Based on:** [v1.md FR-06, FR-08, FR-11](../../../v1.md#fr-08--payments), [limitation.md §6, §7](../../../limitation.md#7-payments), [state-machines.md §2, §3](../../state-machines.md#3-payment)  
**Files:** [SVG](03-promo-review-and-payment.svg) · [PNG](03-promo-review-and-payment.png) · [Mermaid source](03-promo-review-and-payment.mmd)

The buyer applies a promo code, reviews the total and pays on Pesapal. The platform confirms the result with Pesapal while the buyer waits.

```mermaid
%%{init: {"flowchart": {"wrappingWidth": 320}}}%%
flowchart TD
    classDef startNode fill:#111827,stroke:#111827,color:#ffffff
    classDef endNode fill:#111827,stroke:#111827,color:#ffffff
    classDef buyer fill:#eef2ff,stroke:#4f46e5,color:#1e1b4b
    classDef system fill:#ecfdf5,stroke:#059669,color:#064e3b
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-dasharray:4 3,color:#374151
    classDef decision fill:#fff7ed,stroke:#ea580c,color:#7c2d12
    classDef bar fill:#111827,stroke:#111827,color:#111827

    start(["Start: pending order,<br/>hold active"]):::startNode
    d1{"Has a promo code?"}:::decision
    a1["Enter the code"]:::buyer
    s1["Check the code: event, offerings, dates,<br/>usage limit, per-buyer limit by email,<br/>minimum quantity or order value"]:::system
    d2{"Eligible?"}:::decision
    s2["Count the use against the hold;<br/>recalculate discount and totals"]:::system
    m1{" "}:::decision
    a2["Review the order: every amount<br/>calculated by the backend"]:::buyer
    d3{"Total is zero?"}:::decision
    free(["End: tickets issued<br/>without payment, see 04"]):::endNode
    a3["Choose to pay"]:::buyer
    s3["Create the payment with an idempotency key;<br/>submit the order to Pesapal; save the tracking ID"]:::system
    p1["Redirect to Pesapal's hosted checkout"]:::system
    p2["Choose M-Pesa or card and pay<br/>on Pesapal's page"]:::external
    fork[" "]:::bar
    b1["Pesapal returns the buyer<br/>to the website"]:::external
    w1["Show 'Confirming your payment';<br/>the return itself changes nothing"]:::system
    w2["Check the order status<br/>with the checkout token"]:::system
    b2["Pesapal sends an IPN, or reconciliation<br/>runs every few minutes"]:::external
    s4["Confirm with Pesapal's transaction<br/>status endpoint, see 04"]:::system
    join[" "]:::bar
    d4{"Confirmed result?"}:::decision
    s5["Keep checking while<br/>the hold lasts"]:::system
    d5{"Hold still active?"}:::decision
    paid(["End: tickets issued, see 04"]):::endNode
    expired(["End: order expired, see 05"]):::endNode
    refunded(["End: refunded<br/>automatically, see 05"]):::endNode

    start --> d1
    d1 -->|"yes"| a1 --> s1 --> d2
    d2 -->|"no: show the reason"| d1
    d2 -->|"yes"| s2 --> m1
    d1 -->|"no"| m1
    m1 --> a2 --> d3
    d3 -->|"yes"| free
    d3 -->|"no"| a3 --> s3 --> p1 --> p2 --> fork
    fork --> b1 --> w1 --> w2 --> join
    fork --> b2 --> s4 --> join
    join --> d4
    d4 -->|"completed and accepted"| paid
    d4 -->|"no result yet"| s5 --> d4
    d4 -->|"failed or cancelled"| d5
    d5 -->|"yes: try again"| a3
    d5 -->|"no"| expired
    d4 -->|"completed, but late,<br/>mismatched or a duplicate"| refunded
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

- A promo code applies to the held order, and its use is counted against the hold ([limitation.md 6.4](../../../limitation.md#6-promotions-and-discount-codes), `POST /api/v1/orders/{id}/apply-promotion`). v1.md FR-06 lists it after the hold too, since version 1.8.
- Returning from Pesapal never changes the order. Only the confirmed status check does.
- Calls about the order (promo, payment, status) need the order's checkout token.

## Activity diagrams

1. [Buyer journey overview](01-buyer-journey-overview.md)
2. [Choose tickets and place a hold](02-choose-tickets-and-hold.md)
3. [Promo code, review and payment](03-promo-review-and-payment.md)
4. [Payment confirmation and ticket issuance](04-payment-confirmation-and-issuance.md)
5. [Hold expiry and late payment](05-hold-expiry-and-late-payment.md)
6. [Delegation claim](06-delegation-claim.md)
7. [Ticket retrieval and transfer](07-ticket-retrieval-and-transfer.md)
8. [Organizer: set up ticket waves](08-set-up-ticket-waves.md)
