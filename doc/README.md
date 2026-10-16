# Diagrams

Every diagram has four files with the same name:

| File | Use |
|---|---|
| `.md` | The diagram as a Mermaid block, which GitHub renders, with its source and notes. Start here. |
| `.mmd` | The same Mermaid source, used to render the images. |
| `.svg` | Vector image. |
| `.png` | Raster image at 2× scale. |

There are two kinds of diagram:

- **Copies** of the Mermaid blocks in the specification documents (v1.md, techstack.md, implementation-plan.md, doc/state-machines.md, doc/roleandprivileage.md and doc/sponsored.md). The document is the source; the `.md` and `.mmd` files must match it.
- **Standalone diagrams** drawn from the specification: the complete database ERD, the use case diagrams and the activity diagrams. Their `.mmd` and `.md` files are the source.

Mermaid has no use case or activity diagram type, so those are drawn as flowcharts that follow UML conventions. Each one explains its notation.

## Database ERD

The complete logical schema from [v1.md §4](../v1.md#4-database-design): all 36 tables. [v1.md §4.5](../v1.md#45-core-relationships) links here; it replaced the short diagram that section used to show ([hurdles.md §6](../hurdles.md#6-document-upkeep)). P1.3 updates it to match the final migrations.

| # | Diagram | Based on | Files |
|---|---|---|---|
| 0 | [Overview: every table and foreign key](diagrams/erd/00-overview.md) | v1.md §4.1–§4.4 | [md](diagrams/erd/00-overview.md) · [mmd](diagrams/erd/00-overview.mmd) · [svg](diagrams/erd/00-overview.svg) · [png](diagrams/erd/00-overview.png) |
| 1 | [Identity and tenancy](diagrams/erd/01-identity-and-tenancy.md) | v1.md §4.1 | [md](diagrams/erd/01-identity-and-tenancy.md) · [mmd](diagrams/erd/01-identity-and-tenancy.mmd) · [svg](diagrams/erd/01-identity-and-tenancy.svg) · [png](diagrams/erd/01-identity-and-tenancy.png) |
| 2 | [Ticket configuration and inventory](diagrams/erd/02-ticket-configuration-and-inventory.md) | v1.md §4.2 | [md](diagrams/erd/02-ticket-configuration-and-inventory.md) · [mmd](diagrams/erd/02-ticket-configuration-and-inventory.mmd) · [svg](diagrams/erd/02-ticket-configuration-and-inventory.svg) · [png](diagrams/erd/02-ticket-configuration-and-inventory.png) |
| 3 | [Orders, attendees and tickets](diagrams/erd/03-orders-attendees-and-tickets.md) | v1.md §4.3 | [md](diagrams/erd/03-orders-attendees-and-tickets.md) · [mmd](diagrams/erd/03-orders-attendees-and-tickets.mmd) · [svg](diagrams/erd/03-orders-attendees-and-tickets.svg) · [png](diagrams/erd/03-orders-attendees-and-tickets.png) |
| 4 | [Payments, promotions and operations](diagrams/erd/04-payments-promotions-and-operations.md) | v1.md §4.4 | [md](diagrams/erd/04-payments-promotions-and-operations.md) · [mmd](diagrams/erd/04-payments-promotions-and-operations.mmd) · [svg](diagrams/erd/04-payments-promotions-and-operations.svg) · [png](diagrams/erd/04-payments-promotions-and-operations.png) |

## Use cases

What each role can do, from the permissions matrix in [v1.md §3](../v1.md#3-permissions-matrix).

| # | Diagram | Based on | Files |
|---|---|---|---|
| 0 | [Overview: every actor](diagrams/use-cases/00-overview.md) | v1.md §2, §3 | [md](diagrams/use-cases/00-overview.md) · [mmd](diagrams/use-cases/00-overview.mmd) · [svg](diagrams/use-cases/00-overview.svg) · [png](diagrams/use-cases/00-overview.png) |
| 1 | [Buyer and ticket holder](diagrams/use-cases/01-buyer-and-ticket-holder.md) | v1.md FR-06 to FR-09 | [md](diagrams/use-cases/01-buyer-and-ticket-holder.md) · [mmd](diagrams/use-cases/01-buyer-and-ticket-holder.mmd) · [svg](diagrams/use-cases/01-buyer-and-ticket-holder.svg) · [png](diagrams/use-cases/01-buyer-and-ticket-holder.png) |
| 2 | [Organizer staff](diagrams/use-cases/02-organizer-staff.md) | v1.md §3, FR-02, FR-03 | [md](diagrams/use-cases/02-organizer-staff.md) · [mmd](diagrams/use-cases/02-organizer-staff.mmd) · [svg](diagrams/use-cases/02-organizer-staff.svg) · [png](diagrams/use-cases/02-organizer-staff.png) |
| 3 | [Event owner](diagrams/use-cases/03-event-owner.md) | v1.md §3, FR-13 | [md](diagrams/use-cases/03-event-owner.md) · [mmd](diagrams/use-cases/03-event-owner.mmd) · [svg](diagrams/use-cases/03-event-owner.svg) · [png](diagrams/use-cases/03-event-owner.png) |
| 4 | [Guard](diagrams/use-cases/04-guard.md) | v1.md FR-01, FR-10 | [md](diagrams/use-cases/04-guard.md) · [mmd](diagrams/use-cases/04-guard.mmd) · [svg](diagrams/use-cases/04-guard.svg) · [png](diagrams/use-cases/04-guard.png) |
| 5 | [Platform admin](diagrams/use-cases/05-platform-admin.md) | v1.md §3, FR-08, FR-13 | [md](diagrams/use-cases/05-platform-admin.md) · [mmd](diagrams/use-cases/05-platform-admin.mmd) · [svg](diagrams/use-cases/05-platform-admin.svg) · [png](diagrams/use-cases/05-platform-admin.png) |
| 6 | [Automated processes](diagrams/use-cases/06-automated-processes.md) | doc/state-machines.md, v1.md §6 | [md](diagrams/use-cases/06-automated-processes.md) · [mmd](diagrams/use-cases/06-automated-processes.mmd) · [svg](diagrams/use-cases/06-automated-processes.svg) · [png](diagrams/use-cases/06-automated-processes.png) |

## Activity diagrams

How a buyer gets a ticket, from the landing page to the gate (1–7), and how organizer staff set up ticket waves (8).

| # | Diagram | Based on | Files |
|---|---|---|---|
| 1 | [Overview](diagrams/activity/01-buyer-journey-overview.md) | v1.md FR-06 to FR-09 | [md](diagrams/activity/01-buyer-journey-overview.md) · [mmd](diagrams/activity/01-buyer-journey-overview.mmd) · [svg](diagrams/activity/01-buyer-journey-overview.svg) · [png](diagrams/activity/01-buyer-journey-overview.png) |
| 2 | [Choose tickets and place a hold](diagrams/activity/02-choose-tickets-and-hold.md) | v1.md FR-06, FR-07 | [md](diagrams/activity/02-choose-tickets-and-hold.md) · [mmd](diagrams/activity/02-choose-tickets-and-hold.mmd) · [svg](diagrams/activity/02-choose-tickets-and-hold.svg) · [png](diagrams/activity/02-choose-tickets-and-hold.png) |
| 3 | [Promo code, review and payment](diagrams/activity/03-promo-review-and-payment.md) | v1.md FR-08, FR-11 | [md](diagrams/activity/03-promo-review-and-payment.md) · [mmd](diagrams/activity/03-promo-review-and-payment.mmd) · [svg](diagrams/activity/03-promo-review-and-payment.svg) · [png](diagrams/activity/03-promo-review-and-payment.png) |
| 4 | [Payment confirmation and ticket issuance](diagrams/activity/04-payment-confirmation-and-issuance.md) | v1.md §6, state machines §2–§4 | [md](diagrams/activity/04-payment-confirmation-and-issuance.md) · [mmd](diagrams/activity/04-payment-confirmation-and-issuance.mmd) · [svg](diagrams/activity/04-payment-confirmation-and-issuance.svg) · [png](diagrams/activity/04-payment-confirmation-and-issuance.png) |
| 5 | [Hold expiry and late payment](diagrams/activity/05-hold-expiry-and-late-payment.md) | state machines §2, §3, §6 | [md](diagrams/activity/05-hold-expiry-and-late-payment.md) · [mmd](diagrams/activity/05-hold-expiry-and-late-payment.mmd) · [svg](diagrams/activity/05-hold-expiry-and-late-payment.svg) · [png](diagrams/activity/05-hold-expiry-and-late-payment.png) |
| 6 | [Delegation claim](diagrams/activity/06-delegation-claim.md) | doc/sponsored.md | [md](diagrams/activity/06-delegation-claim.md) · [mmd](diagrams/activity/06-delegation-claim.mmd) · [svg](diagrams/activity/06-delegation-claim.svg) · [png](diagrams/activity/06-delegation-claim.png) |
| 7 | [Ticket retrieval and transfer](diagrams/activity/07-ticket-retrieval-and-transfer.md) | v1.md FR-09 | [md](diagrams/activity/07-ticket-retrieval-and-transfer.md) · [mmd](diagrams/activity/07-ticket-retrieval-and-transfer.mmd) · [svg](diagrams/activity/07-ticket-retrieval-and-transfer.svg) · [png](diagrams/activity/07-ticket-retrieval-and-transfer.png) |
| 8 | [Organizer: set up ticket waves](diagrams/activity/08-set-up-ticket-waves.md) | v1.md FR-04 | [md](diagrams/activity/08-set-up-ticket-waves.md) · [mmd](diagrams/activity/08-set-up-ticket-waves.mmd) · [svg](diagrams/activity/08-set-up-ticket-waves.svg) · [png](diagrams/activity/08-set-up-ticket-waves.png) |

## State machines ([doc/state-machines.md](state-machines.md))

The authoritative state machines. The state diagrams in v1.md §4.6 are short versions of these.

| # | Diagram | Source section | Files |
|---|---|---|---|
| 1 | [Event](diagrams/state-machines/01-event-states.md) | §1 Event | [md](diagrams/state-machines/01-event-states.md) · [mmd](diagrams/state-machines/01-event-states.mmd) · [svg](diagrams/state-machines/01-event-states.svg) · [png](diagrams/state-machines/01-event-states.png) |
| 2 | [Order](diagrams/state-machines/02-order-states.md) | §2 Order | [md](diagrams/state-machines/02-order-states.md) · [mmd](diagrams/state-machines/02-order-states.mmd) · [svg](diagrams/state-machines/02-order-states.svg) · [png](diagrams/state-machines/02-order-states.png) |
| 3 | [Payment](diagrams/state-machines/03-payment-states.md) | §3 Payment | [md](diagrams/state-machines/03-payment-states.md) · [mmd](diagrams/state-machines/03-payment-states.mmd) · [svg](diagrams/state-machines/03-payment-states.svg) · [png](diagrams/state-machines/03-payment-states.png) |
| 4 | [Refund](diagrams/state-machines/04-refund-states.md) | §3.1 Refund | [md](diagrams/state-machines/04-refund-states.md) · [mmd](diagrams/state-machines/04-refund-states.mmd) · [svg](diagrams/state-machines/04-refund-states.svg) · [png](diagrams/state-machines/04-refund-states.png) |
| 5 | [Ticket](diagrams/state-machines/05-ticket-states.md) | §4 Ticket | [md](diagrams/state-machines/05-ticket-states.md) · [mmd](diagrams/state-machines/05-ticket-states.mmd) · [svg](diagrams/state-machines/05-ticket-states.svg) · [png](diagrams/state-machines/05-ticket-states.png) |
| 6 | [Seat](diagrams/state-machines/06-seat-states.md) | §5 Seat | [md](diagrams/state-machines/06-seat-states.md) · [mmd](diagrams/state-machines/06-seat-states.mmd) · [svg](diagrams/state-machines/06-seat-states.svg) · [png](diagrams/state-machines/06-seat-states.png) |
| 7 | [Inventory reservation](diagrams/state-machines/07-inventory-reservation-states.md) | §6 Inventory reservation | [md](diagrams/state-machines/07-inventory-reservation-states.md) · [mmd](diagrams/state-machines/07-inventory-reservation-states.mmd) · [svg](diagrams/state-machines/07-inventory-reservation-states.svg) · [png](diagrams/state-machines/07-inventory-reservation-states.png) |

## v1: Refined Software Requirements Specification ([v1.md](../v1.md))

Diagram 2, the short ERD that v1.md §4.5 used to show, was removed in v1.md 1.8. §4.5 now links to the [complete ERD](diagrams/erd/00-overview.md).

| # | Diagram | Source section | Status | Files |
|---|---|---|---|---|
| 1 | [High-level architecture](diagrams/v1/01-high-level-architecture.md) | §1 Product scope and architecture | Current | [md](diagrams/v1/01-high-level-architecture.md) · [mmd](diagrams/v1/01-high-level-architecture.mmd) · [svg](diagrams/v1/01-high-level-architecture.svg) · [png](diagrams/v1/01-high-level-architecture.png) |
| 3 | [Payment states](diagrams/v1/03-payment-states.md) | §4.6 Important state models | Short version | [md](diagrams/v1/03-payment-states.md) · [mmd](diagrams/v1/03-payment-states.mmd) · [svg](diagrams/v1/03-payment-states.svg) · [png](diagrams/v1/03-payment-states.png) |
| 4 | [Order states](diagrams/v1/04-order-states.md) | §4.6 Important state models | Short version | [md](diagrams/v1/04-order-states.md) · [mmd](diagrams/v1/04-order-states.mmd) · [svg](diagrams/v1/04-order-states.svg) · [png](diagrams/v1/04-order-states.png) |
| 5 | [Ticket states](diagrams/v1/05-ticket-states.md) | §4.6 Important state models | Short version | [md](diagrams/v1/05-ticket-states.md) · [mmd](diagrams/v1/05-ticket-states.mmd) · [svg](diagrams/v1/05-ticket-states.svg) · [png](diagrams/v1/05-ticket-states.png) |
| 6 | [Payment-to-ticket issuance sequence](diagrams/v1/06-payment-to-ticket-sequence.md) | §6 Payment-to-ticket issuance workflow | Current | [md](diagrams/v1/06-payment-to-ticket-sequence.md) · [mmd](diagrams/v1/06-payment-to-ticket-sequence.mmd) · [svg](diagrams/v1/06-payment-to-ticket-sequence.svg) · [png](diagrams/v1/06-payment-to-ticket-sequence.png) |
| 7 | [Event states](diagrams/v1/07-event-states.md) | §4.6 Important state models | Short version | [md](diagrams/v1/07-event-states.md) · [mmd](diagrams/v1/07-event-states.mmd) · [svg](diagrams/v1/07-event-states.svg) · [png](diagrams/v1/07-event-states.png) |

## Tech stack: Architecture Blueprint ([techstack.md](../techstack.md))

| # | Diagram | Source section | Files |
|---|---|---|---|
| 1 | [System architecture](diagrams/techstack/01-system-architecture.md) | §2 System architecture | [md](diagrams/techstack/01-system-architecture.md) · [mmd](diagrams/techstack/01-system-architecture.mmd) · [svg](diagrams/techstack/01-system-architecture.svg) · [png](diagrams/techstack/01-system-architecture.png) |
| 2 | [Ticket purchase flow](diagrams/techstack/02-ticket-purchase-flow.md) | §5A Ticket purchase | [md](diagrams/techstack/02-ticket-purchase-flow.md) · [mmd](diagrams/techstack/02-ticket-purchase-flow.mmd) · [svg](diagrams/techstack/02-ticket-purchase-flow.svg) · [png](diagrams/techstack/02-ticket-purchase-flow.png) |
| 3 | [Gate QR scanning flow](diagrams/techstack/03-gate-qr-scanning-flow.md) | §5B QR scanning at the gate | [md](diagrams/techstack/03-gate-qr-scanning-flow.md) · [mmd](diagrams/techstack/03-gate-qr-scanning-flow.mmd) · [svg](diagrams/techstack/03-gate-qr-scanning-flow.svg) · [png](diagrams/techstack/03-gate-qr-scanning-flow.png) |
| 4 | [Google sign-in flow](diagrams/techstack/04-google-sign-in.md) | §3 Authentication and role design | [md](diagrams/techstack/04-google-sign-in.md) · [mmd](diagrams/techstack/04-google-sign-in.mmd) · [svg](diagrams/techstack/04-google-sign-in.svg) · [png](diagrams/techstack/04-google-sign-in.png) |

## Implementation Plan ([implementation-plan.md](../implementation-plan.md))

| # | Diagram | Source section | Files |
|---|---|---|---|
| 1 | [Delivery timeline (12 weeks)](diagrams/implementation-plan/01-delivery-timeline.md) | §1.3 Timeline | [md](diagrams/implementation-plan/01-delivery-timeline.md) · [mmd](diagrams/implementation-plan/01-delivery-timeline.mmd) · [svg](diagrams/implementation-plan/01-delivery-timeline.svg) · [png](diagrams/implementation-plan/01-delivery-timeline.png) |

## Roles and privileges ([doc/roleandprivileage.md](roleandprivileage.md))

| # | Diagram | Source section | Files |
|---|---|---|---|
| 1 | [Role relationships](diagrams/roleandprivileage/01-role-relationships.md) | §2 Summary of System Roles | [md](diagrams/roleandprivileage/01-role-relationships.md) · [mmd](diagrams/roleandprivileage/01-role-relationships.mmd) · [svg](diagrams/roleandprivileage/01-role-relationships.svg) · [png](diagrams/roleandprivileage/01-role-relationships.png) |
| 2 | [Authorization pipeline](diagrams/roleandprivileage/02-authorization-pipeline.md) | §4.2 Enforcement Sequence Diagram | [md](diagrams/roleandprivileage/02-authorization-pipeline.md) · [mmd](diagrams/roleandprivileage/02-authorization-pipeline.mmd) · [svg](diagrams/roleandprivileage/02-authorization-pipeline.svg) · [png](diagrams/roleandprivileage/02-authorization-pipeline.png) |

## Sponsored tickets ([doc/sponsored.md](sponsored.md))

| # | Diagram | Source section | Files |
|---|---|---|---|
| 1 | [Delegation claim pipeline](diagrams/sponsored/01-delegation-claim-pipeline.md) | §4 End-to-End Pipeline Architecture | [md](diagrams/sponsored/01-delegation-claim-pipeline.md) · [mmd](diagrams/sponsored/01-delegation-claim-pipeline.mmd) · [svg](diagrams/sponsored/01-delegation-claim-pipeline.svg) · [png](diagrams/sponsored/01-delegation-claim-pipeline.png) |

## SOW ([sow.md](../sow.md))

The Statement of Work contains no Mermaid diagrams.

## Regenerating

1. Change the diagram where its source is:
   - **Copies:** edit the Mermaid block in the specification document, then copy it into the `.mmd` file and into the Mermaid block of the `.md` file.
   - **Standalone diagrams** (`erd/`, `use-cases/`, `activity/`): edit the `.mmd` file, then copy it into the Mermaid block of the `.md` file.
2. Re-render with [mermaid-cli](https://github.com/mermaid-js/mermaid-cli), from the `doc` folder:

   ```sh
   npx -p @mermaid-js/mermaid-cli mmdc -i diagrams/v1/01-high-level-architecture.mmd -o diagrams/v1/01-high-level-architecture.svg -b white
   npx -p @mermaid-js/mermaid-cli mmdc -i diagrams/v1/01-high-level-architecture.mmd -o diagrams/v1/01-high-level-architecture.png -b white -s 2
   ```

   For wide diagrams (`erd/00-overview` and `use-cases/`), add `-w 1200` to the PNG command so the text stays legible.
3. Check that the `.md` file still renders. mermaid-cli renders every Mermaid block in a Markdown file and fails on a syntax error:

   ```sh
   npx -p @mermaid-js/mermaid-cli mmdc -i diagrams/erd/00-overview.md -o /tmp/check.md
   ```
