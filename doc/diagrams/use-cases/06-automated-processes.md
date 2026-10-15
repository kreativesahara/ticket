# Use cases: automated processes

**Type:** Use case diagram (Mermaid flowchart)  
**Based on:** [doc/state-machines.md](../../state-machines.md), [v1.md §6](../../../v1.md#6-payment-to-ticket-issuance-workflow), [techstack.md](../../../techstack.md)  
**Files:** [SVG](06-automated-processes.svg) · [PNG](06-automated-processes.png) · [Mermaid source](06-automated-processes.mmd)

What the platform does without a person: payment confirmation, hold expiry, event closing and email delivery.

```mermaid
flowchart LR
    classDef actor fill:#ffffff,stroke:#1f2937,stroke-width:2px,color:#111827
    classDef external fill:#f3f4f6,stroke:#6b7280,stroke-width:1px,stroke-dasharray:4 3,color:#374151
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1px,color:#1e1b4b

    Scheduler["«actor»<br/><b>Scheduler</b><br/>cron: scheduler and queue"]:::actor
    PesapalIPN["«system»<br/><b>Pesapal</b><br/>sends IPN"]:::external

    subgraph SYS["Ticketing platform: background processing"]
        Y1(["Receive a payment notification"]):::usecase
        Y2(["Reconcile pending payments"]):::usecase
        Y3(["Confirm with the transaction status endpoint"]):::usecase
        Y4(["Accept payment and issue tickets"]):::usecase
        Y5(["Refund a payment automatically"]):::usecase
        Y6(["Expire inventory holds"]):::usecase
        Y7(["Release seats and promo use"]):::usecase
        Y8(["Close events when entry ends"]):::usecase
        Y9(["Revoke guard passwords and sessions"]):::usecase
        Y10(["Expire unused tickets"]):::usecase
        Y11(["Deliver queued emails"]):::usecase
    end

    Pesapal["«system»<br/><b>Pesapal</b><br/>status and refunds API"]:::external
    Email["«system»<br/><b>Email service</b>"]:::external

    PesapalIPN --- Y1
    Scheduler ---|"every few minutes"| Y2
    Scheduler ---|"every minute"| Y6
    Scheduler --- Y8
    Scheduler --- Y11

    Y1 -.->|"«include»"| Y3
    Y2 -.->|"«include»"| Y3
    Y4 -.->|"«extend» completed, amount matches, order pending"| Y3
    Y5 -.->|"«extend» late, mismatched or duplicate payment"| Y3
    Y6 -.->|"«include»"| Y7
    Y8 -.->|"«include»"| Y9
    Y8 -.->|"«include»"| Y10

    %% layout only: keep actors in the leftmost column
    Scheduler ~~~ Y4
    Scheduler ~~~ Y5
    PesapalIPN ~~~ Y4

    Y3 --- Pesapal
    Y5 --- Pesapal
    Y11 --- Email
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

- The scheduler and queue run from cron on cPanel.
- Pesapal's transaction status endpoint is the only source of a payment's result. An IPN only triggers the check.
- Late, mismatched and duplicate payments are refunded automatically, with no approval step ([state-machines.md §3](../../state-machines.md#3-payment)).
