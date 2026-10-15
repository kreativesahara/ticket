# Gate QR scanning flow

**Type:** Flowchart  
**Source:** [techstack.md › B. QR scanning at the gate](../../../techstack.md#b-qr-scanning-at-the-gate)  
**Files:** [SVG](03-gate-qr-scanning-flow.svg) · [PNG](03-gate-qr-scanning-flow.png) · [Mermaid source](03-gate-qr-scanning-flow.mmd)

```mermaid
flowchart TD
    A[Guard signs in] --> B[Validate assigned event and gate]
    B --> C[Scan QR code]
    B --> C2[QR fails: enter 6-character code from the ticket]
    C --> D[Laravel validates ticket credential]
    C2 --> D
    D --> E{Ticket eligible?}
    E -- No --> F[Reject with reason]
    E -- Yes --> G[Atomically redeem ticket]
    G --> H{Redemption committed?}
    H -- Yes --> I[Admit attendee]
    H -- Already redeemed --> J[Reject duplicate scan]
    H -- Conflict --> K[Retry or report unavailable]
```

This is a copy of the Mermaid block in [techstack.md](../../../techstack.md#b-qr-scanning-at-the-gate). When it changes, update the source document, this file and `03-gate-qr-scanning-flow.mmd` together, then re-render ([how](../../README.md#regenerating)).
