# Hurdles: Gaps, Risks and Limitations

**Version 1.1 · 13 October 2026**

**Based on:** [v1.md](v1.md) (version 1.3), [sow.md](sow.md) (version 1.3), [techstack.md](techstack.md) and [implementation-plan.md](implementation-plan.md) (version 1.3).

This document lists what the current specification doesn't yet cover, and the limitations that come with decisions already made. The specification handles the core flow well: publishing events, buying and paying, issuing tickets and scanning them at the gate. The gaps are mostly in what happens to the money after a sale, unusual situations at the gate, and messages to buyers.

Most hurdles need a decision, not extra code. Once a hurdle is decided, record the decision in the decision log (implementation-plan.md §11) and update v1.md, which is the source of truth.

---

## Summary

**Impact:** **High** = could block launch, lose money or break a legal obligation. **Medium** = causes problems at events or in operations. **Low** = a consistency fix or a scope question.

| #   | Hurdle                                          | Area              | Impact | Decide by | Owner    | Status  |
| --- | ----------------------------------------------- | ----------------- | ------ | --------- | -------- | ------- |
| H1  | No record of what each organizer is owed        | Money             | High   | Phase 0   | Client   | Open    |
| H2  | Collecting for other businesses; unverified organizers | Money      | High   | Phase 0   | Client   | Open    |
| H3  | No path for free tickets or delegation claims   | Money             | High   | Phase 1   | Both     | Open    |
| H4  | "At the gate" tickets have no way to be sold    | Money             | High   | Phase 1   | Client   | Open    |
| H5  | No process for cancelled or postponed events    | Money             | High   | Phase 1   | Client   | Open    |
| H6  | No fallback when a QR code can't be scanned     | Gate              | Medium | Phase 5   | Provider | Decided |
| H7  | Re-entry rule not stated                        | Gate              | Medium | Phase 1   | Client   | Open    |
| H8  | Google-invited guards keep their assignment after the event | Gate  | Low    | Phase 2   | Provider | Open    |
| H9  | No buyer messages beyond the ticket email; SMS undecided | Buyers   | Medium | Phase 1   | Client   | Open    |
| H10 | No receipts; tax position unconfirmed           | Compliance        | High   | Phase 0   | Client   | Open    |
| H11 | Guest buyers have no defined way to view their order | Buyers       | Medium | Phase 1   | Provider | Open    |
| H12 | No rules for keeping or deleting personal data  | Compliance        | Medium | Phase 1   | Both     | Open    |
| H13 | Sale-day rush and ticket bots                   | Operations        | Medium | Phase 0   | Both     | Open    |

---

## 1. Money and payments

### H1. No record of what each organizer is owed

**Gap:** Every buyer payment lands in the platform's Pesapal merchant account, so the platform holds money that belongs to organizers. Nothing in the specification tracks what each organizer is owed (ticket sales, minus refunds, Pesapal's fees and any platform fee) or records the payouts made to them. The plan only says to pay organizers manually from the reconciliation reports.

**Also undecided:**
- Whether the platform charges a fee, and whether the buyer or the organizer pays it (decision log #2).
- Who absorbs Pesapal's transaction fees.

**Why it matters:** Without a clear balance per organizer, payouts become a manual spreadsheet exercise, disputes are hard to settle, and the platform's books won't reconcile. The fee decision also changes how prices are stored on every order.

**Recommendation:** Add a per-organizer settlement report (sales, refunds, fees and amount owed per event) and a simple log for recording each manual payout. Automated payouts stay out of scope, as SOW §9 states.

**Decide by:** Phase 0 · **Owner:** Client

### H2. Collecting for other businesses, and unverified organizers

**Gap:** Two related issues:
- The platform collects money on behalf of many organizers through one Pesapal account. Pesapal hasn't yet confirmed that this is allowed under a standard merchant account; marketplaces sometimes need a specific arrangement.
- Anyone who signs in can apply to become an organizer, and a platform admin approves them. Nothing checks the organizer's identity, business registration or payout details before they start selling.

**Why it matters:** If Pesapal requires a different arrangement, discovering it late could delay launch. Without organizer verification, someone could publish a fake event, collect buyers' money and disappear, leaving the platform to handle refunds and complaints.

**Recommendation:** Raise the marketplace question with Pesapal during onboarding in Phase 0. Add a verification step before an organizer's first paid event can be published: business or ID details, and the account they'll be paid into.

**Decide by:** Phase 0 · **Owner:** Client

### H3. No path for free tickets or delegation claims

**Gap:**
- Complimentary tickets, delegation tickets priced at zero, and 100% discount codes all produce an order total of zero. Pesapal can't process a zero payment, and the specification has no way to issue tickets without one.
- Delegations (sponsor, department or guest-list allocations) have their own inventory and prices, but there's no defined way for members of a delegation to claim or buy their tickets, such as a private link or an access code.

**Why it matters:** Guest lists and sponsor allocations are common at events. Without these paths, organizers would have to work around the system, and those tickets wouldn't be tracked properly.

**Recommendation:** Orders with a zero total skip payment and issue tickets straight away, with the same checks and audit trail as paid orders. Give each delegation a private link or access code that unlocks its allocation.

**Decide by:** Phase 1 (database design) · **Owner:** Both

### H4. "At the gate" tickets have no way to be sold

**Gap:** "At the gate" is one of the default ticket types, but the specification has no channel for selling it. Guards are explicitly not allowed to issue tickets, and cash isn't supported.

**Why it matters:** Organizers will expect to sell tickets at the venue, and the ticket type suggests they can. Without a defined process, gate sales will either fail or happen outside the system, which breaks capacity limits and reporting.

**Recommendation:** The simplest option is for buyers to pay on their own phones through the public website, using a QR code poster at the gate that links to the event page. The alternative is a separate "box office" role with its own selling screen, which adds scope. Decide whether cash sales are supported at all.

**Decide by:** Phase 1 · **Owner:** Client

### H5. No process for cancelled or postponed events

**Gap:** Organizers can cancel an event, but the specification doesn't say what happens to tickets already sold: whether buyers are refunded automatically through Pesapal, how they're told, and whether tickets stay valid if an event is postponed to a new date.

**Why it matters:** A cancelled event with hundreds of paid tickets is exactly when buyers, organizers and the platform most need a clear, fast process. Handling it manually risks missed refunds and complaints.

**Recommendation:** Define both cases:
- **Cancellation:** all tickets are revoked, refunds are issued in bulk through Pesapal (following the refund policy), and every buyer is emailed.
- **Postponement:** tickets stay valid for the new date, buyers are emailed, and those who can't attend can request a refund within a set period.

**Decide by:** Phase 1 · **Owner:** Client

---

## 2. Gate operations

### H6. No fallback when a QR code can't be scanned — Decided

**Gap:** Guards could only admit someone by scanning their QR code.

**Why it matters:** Cracked screens, glare and badly printed tickets are common at the gate. Without a fallback, a guard must either turn away a paying attendee or wave them in without any record.

**Decision (13 October 2026):** Every ticket carries a 6-character short code, shown on the ticket page and on the downloaded ticket. When the QR code won't scan, the attendee shows their downloaded or printed ticket and the guard types the code.
- The code goes through the same checks and the same one-time check-in as a scan, and it is logged as a short-code entry.
- The result shows the attendee's name (where collected), so the guard can compare it with the ticket shown.
- Codes use characters that are hard to confuse and are unique within each event. They are stored as a keyed hash plus an encrypted copy, never in plain text.
- Six characters are easier to guess than a QR code, so code entry is accepted only from an assigned guard at an open gate, is rate-limited per guard, and locks after repeated invalid codes.

Recorded in v1.md (FR-09, FR-10 and §4.3), sow.md (Modules Six and Seven, §5.5) and decision #16 in the implementation plan.

**Remaining risk:** The fallback needs the attendee to show their ticket. If their phone is dead and they have no printout, the guard still can't find the ticket. See the known limitations below.

**Owner:** Provider

### H7. Re-entry rule not stated

**Gap:** Each ticket can be used for entry once. The specification doesn't say whether attendees who leave can come back in.

**Why it matters:** Organizers will ask, and festivals often allow re-entry. If re-entry is needed, the ticket states and scanning rules change.

**Recommendation:** State explicitly that re-entry is not supported in the first release. If it's needed later, add "exited" and "re-admitted" states as a change request.

**Decide by:** Phase 1 · **Owner:** Client

### H8. Google-invited guards keep their assignment after the event

**Gap:** When an event closes, guards using password accounts lose their password and assignment immediately. Guards who were invited and sign in with Google keep their assignment, even though they can no longer scan anything for that event.

**Why it matters:** It's a small inconsistency rather than a security hole, because scans after the gates close are rejected anyway. But stale assignments clutter the guard's screen and the organizer's records.

**Recommendation:** End every guard assignment when its event closes, whichever way the guard signs in.

**Decide by:** Phase 2 · **Owner:** Provider

---

## 3. Buyers and compliance

### H9. No buyer messages beyond the ticket email; SMS undecided

**Gap:** The only message to buyers is the email with their ticket link. There are no messages for order confirmation, failed payments, event changes (new time or venue) or cancellations. The SOW lists SMS as an operating cost, but no SMS feature is specified.

**Why it matters:** Buyers who aren't told about a venue change or cancellation turn up at the wrong place or chase refunds. In Kenya, many buyers respond to SMS faster than to email.

**Recommendation:** Add emails for order confirmation, event changes and cancellations. Decide whether SMS is in scope; if it is, choose an SMS provider early, because sender ID registration can take time.

**Decide by:** Phase 1 · **Owner:** Client

### H10. No receipts; tax position unconfirmed

**Gap:** The specification doesn't include a receipt or invoice for buyers, and it doesn't address tax: whether VAT applies to tickets or to platform fees, and whether KRA's eTIMS electronic invoicing applies.

**Why it matters:** Tax obligations are legal requirements, and they can change how order totals are calculated and what documents must be issued with each sale. Discovering this after launch could mean reissuing documents or changing prices.

**Recommendation:** Have the client's accountant confirm the VAT and eTIMS position in Phase 0. At minimum, email buyers a receipt for every paid order.

**Decide by:** Phase 0 · **Owner:** Client

### H11. Guest buyers have no defined way to view their order

**Gap:** The API lets buyers view an order (`GET /api/v1/orders/{id}`), but buyers check out as guests without an account, so it's unclear how they prove the order is theirs.

**Why it matters:** If this endpoint relied only on the order ID, anyone who guessed an ID could see another buyer's details.

**Recommendation:** Guests reach their order only through their secure retrieval link. The order endpoint is for organizers and admins with the right permissions.

**Decide by:** Phase 1 (API contract) · **Owner:** Provider

### H12. No rules for keeping or deleting personal data

**Gap:** The retention policy is left to the client, but the system design also needs concrete rules: how long attendee details are kept after an event, and how to delete a person's data when they ask, while keeping the financial and audit records the law requires.

**Why it matters:** Kenya's Data Protection Act gives people the right to have their data deleted. Financial records can't simply be deleted, so the system needs a way to anonymize a person without breaking orders, payments or scan records.

**Recommendation:** Design anonymization in Phase 1 (replace names, emails and phone numbers while keeping amounts and references). The client confirms the retention periods before launch.

**Decide by:** Phase 1 (design), Phase 8 (policy) · **Owner:** Both

### H13. Sale-day rush and ticket bots

**Gap:** No one has estimated the peak load for a popular on-sale, and there's no protection against bots buying tickets in bulk beyond per-order limits and rate limiting.

**Why it matters:** Shared cPanel hosting can slow down or fail under a sudden rush, which is exactly when sales matter most. Bots can buy up a popular event's tickets for resale.

**Recommendation:** Get the client's largest expected event and peak buyer numbers in Phase 0, and test against them in Phase 8. Decide whether to add a CAPTCHA at checkout, and plan the move to a VPS if the load test shows cPanel can't cope.

**Decide by:** Phase 0 (estimate), Phase 3 (bot protection) · **Owner:** Both

---

## 4. Scope to confirm

These aren't in the specification. Each should be confirmed as in or out of the first release, so no one assumes they're included.

| Item                          | Question                                                         | Suggested answer                               |
| ----------------------------- | ---------------------------------------------------------------- | ---------------------------------------------- |
| Ticket transfer               | Can a buyer pass a ticket to someone else, or change its name?   | Out for the first release                      |
| Attendee exports              | Can organizers download attendee and sales lists (CSV)?          | In: simple to build and often requested        |
| Event categories              | Can buyers browse events by type (music, sport and so on)?       | Decide; it was dropped when the documents were aligned |
| Swahili                       | Is a Swahili version of the public site needed?                  | Out; the SOW assumes one language              |

**Decide by:** Phase 1 · **Owner:** Client

---

## 5. Known limitations

These come from decisions already made. They're accepted for the first release, but everyone should know about them.

| Limitation                                   | What it means                                                                                   | When to revisit                                        |
| -------------------------------------------- | ----------------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Online-only scanning                         | Gates need an internet connection. If it drops, the scanner shows "Unable to verify — do not admit". | If venues have poor connectivity; offline scanning is out of scope (SOW §9) |
| Short-code fallback needs the ticket         | If a QR code won't scan, the attendee must show their downloaded or printed ticket so the guard can type its short code. With a dead phone and no printout, there's no way in. | If this happens often; a lookup by attendee name could be added |
| Google sign-in for admins, organizers and staff | Anyone without a Google account must create one (it's free, and can use an existing email address). | If organizers push back                                |
| Guard passwords last one event               | A password guard works one event at a time, gets a new password for each event, and sets their own password at each first sign-in. | If the extra step slows gate setup (decision log #15)  |
| One payment gateway                          | If Pesapal is down, no one can buy tickets.                                                     | If outages occur; another gateway can be added later   |
| Manual organizer payouts                     | Organizers are paid by hand, outside the platform.                                              | When payout volume grows (needs a change request)      |
| Shared cPanel hosting                        | Limits on background processing and capacity under heavy load.                                  | If the Phase 8 load test fails; move to a VPS          |
| Guest checkout only                          | Buyers have no account; they reach their tickets through emailed links.                         | If repeat buyers ask for accounts                      |
| One language and currency                    | English and Kenyan shillings only (SOW §8).                                                     | If organizers sell to international buyers             |
| No native mobile apps                        | The guard scanner and all dashboards run in the browser (SOW §9).                               | If browser scanning proves too slow at the gate        |
| Simple seat setup                            | Seats are set up through forms, with no visual seat-map editor (SOW §9).                        | If organizers run large seated venues                  |
| Reserved seating may be deferred             | If the schedule slips, seating moves to a release after launch (SOW §4.1).                      | At the Phase 7 checkpoint                              |
| 12-week timeline                             | Assumes three to four developers; holidays inside the window add one to two weeks.              | When the team and start date are confirmed in Phase 0  |

---

## 6. Document upkeep

These are corrections to the documents themselves, not decisions.

- **The database diagram in v1.md (§4.5) is out of date.** It doesn't show invitations, guard credentials, gates, delegations or inventory reservations. Phase 1 produces a new one from the final schema; until then, the tables in v1.md §4 are correct.
- **The event lifecycle needs a "closed" state.** Guard passwords are deleted when an event closes, so "closed" should appear in the state machine alongside draft, published, cancelled and archived.
- **v2.md is out of date.** It's the earlier requirements review and still uses old names. Mark it as replaced by v1.md.
- **The SOW still has commercial blanks:** the project fee, intellectual property ownership, and support and maintenance after the 30-day warranty.

---

## Next steps

1. Review this list in the Phase 0 decision workshop, alongside the decision log.
2. Settle H1–H5 first. They change the database and payment design, which Phase 1 locks in.
3. Record each decision in implementation-plan.md §11, and update v1.md to match.
