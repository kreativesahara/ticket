# Business Rules and Limitations

**Multi-Organizer Event Ticketing Platform · Version 1.0 · 13 October 2026**

**Status:** Draft for stakeholder confirmation

This document sets out how the platform will behave: the business rules it follows, the limitations of the first release, and the decisions still needed from you. Please confirm each section, or tell us what should change, before development starts. Changing a rule after the build has started takes longer and costs more.

## How to review

1. **Read the business rules (Part A).** Each rule has a number, such as **5.3**. If a rule is wrong or missing something, reply with its number and the change you want.
2. **Review the limitations (Part B).** Mark each one as accepted, or tell us why it isn't acceptable.
3. **Answer the open decisions (Part C).** Each has our recommendation; agree with it or choose another option.
4. **Sign off (Part D)** once everything is agreed.

---

## Part A — Business rules

### 1. Organizers and roles

- **1.1** Many organizers share one platform. Each organizer sees only their own events, sales, attendees, staff and reports, never another organizer's.
- **1.2** The platform has five roles:

  | Role                | What they do                                                                                   |
  | ------------------- | ---------------------------------------------------------------------------------------------- |
  | **Platform admin**  | Runs the platform: approves and suspends organizers, oversees all events, sees platform-wide reports |
  | **Organizer owner** | Full control of their organizer account: events, prices, team members, guards and reports      |
  | **Organizer staff** | Only the tasks an owner allows them, such as managing events or viewing reports                |
  | **Guard**           | Scans tickets at the gate, only for the events and gates assigned to them                      |
  | **Buyer**           | Buys tickets as a guest and receives them by email; no account needed                          |

- **1.3** Organizer staff can never manage team members, change roles or edit the organizer's profile, and can't give permissions to anyone else.
- **1.4** Every organizer must always have at least one owner.
- **1.5** New organizers sign in, apply for an organizer account, and wait for a platform admin to approve them before they can create events.
- **1.6** Platform admins are appointed by an existing platform admin. No one can make themselves an admin.
- **1.7** When a platform admin looks at an organizer's data, the access is recorded.
- **1.8** Suspending an organizer stops new sales for their events and blocks their team from signing in. Their published events stay visible but can't be bought.

### 2. Signing in

- **2.1** Platform admins, organizer owners and organizer staff sign in with their **Google account**. They have no separate password for the platform.
- **2.2** Only Google accounts with a verified email address are accepted.
- **2.3** Staff are invited by email and must accept by signing in with a Google account that uses that same email address. Invitations expire and can be used only once.
- **2.4** A suspended user can't sign in, even with a valid Google account.
- **2.5** Buyers never sign in. They buy as guests and reach their tickets through an emailed link.

**Guards** can sign in in either of two ways:

- **2.6** With Google, after accepting an email invitation, the same way as staff.
- **2.7** With an **email and password account** created for them by an organizer owner, an authorized staff member or a platform admin.

Rules for guard password accounts:

- **2.8** Every guard gets their own account. One account is never shared between guards, because every ticket check is recorded against the guard who did it.
- **2.9** The platform creates a temporary password and shows it only once. The organizer passes it to the guard in person or by direct message, never in a group chat.
- **2.10** The guard must choose their own password the first time they sign in, so only they know it.
- **2.11** A guard password account can only ever be used for guard duties. It can never become an organizer, staff member or admin.
- **2.12** A guard password account belongs to the organizer who created it. No other organizer can use that guard.
- **2.13** Each password works for **one event only**. The guard is assigned to one event at a time.
- **2.14** The password is **deleted as soon as the event closes**, and the guard is signed out immediately. An event closes when its last gate closes, or when the organizer cancels or archives it, whichever happens first.
- **2.15** The guard's name stays on record after the password is deleted, so past ticket checks still show who did them. For the next event, the organizer issues a new password.
- **2.16** If a guard forgets their password, the organizer issues a new one. There is no "forgot password" email.
- **2.17** Repeated wrong passwords temporarily lock the account.
- **2.18** Guards are signed out automatically after at most 12 hours, and when their event closes.

### 3. Events

- **3.1** An event goes through these stages: **draft** → **published** → **closed** or **cancelled** → **archived**. A published event closes automatically when its last gate closes. Only published events can be seen and bought by the public. A draft that was never published can be deleted; an event that was ever published is never deleted, only archived, so its sales and entry records are kept.
- **3.2** Each event has: name, description, images, venue, date and time (with its timezone), ticket sales start and end, gate opening and closing times, total capacity, the attendee details required, ticket options, optional seating, gates and guards, and a cancellation and refund policy.
- **3.3** Three sets of times are separate and independent:
  - **Ticket sales:** when buying starts and stops.
  - **Gates:** when entry starts and stops.
  - **Event:** when the event itself starts and ends.

  Gates can open before the event starts and stay open after it begins.
- **3.4** An event can be published only when it has at least one ticket option on sale, sensible times, and enough capacity for all its allocations.
- **3.5** Every change to a published event is recorded. Changes that would affect tickets already sold need explicit confirmation or are blocked: for example, reducing capacity below the number sold, changing the price of a ticket that has sales, or moving the event's dates.

### 4. Tickets, prices and capacity

- **4.1** A **ticket type** describes how a ticket is sold: Early Bird, Normal, At the Gate, Group or Promoted. A **ticket category** describes the level of access: VVIP, VIP or Media. Organizers can add their own types and categories.
- **4.2** What a buyer actually purchases is a combination of type and category, for example "Early Bird VIP". Each combination has its own price, number available, sales period and minimum and maximum per order. Only combinations the organizer turns on can be bought.
- **4.3** Early Bird tickets stop selling when their sales period ends or when they sell out, whichever comes first.
- **4.4** Group tickets require a set number of tickets and use the group price.
- **4.5** At the Gate tickets come from the same capacity as advance tickets.
- **4.6** Media tickets can be set to need the organizer's approval, or can be issued manually by the organizer.
- **4.7** Promoted tickets need clear rules about who can buy them and at what price.
- **4.8** **Delegations** are allocations set aside for sponsors, departments or invited groups. Each has its own number of tickets, allowed categories and prices.
- **4.9** Prices are in **Kenyan shillings** and are stored exactly, with no rounding errors.
- **4.10** The price a buyer pays is fixed when they order. If the organizer changes prices later, existing orders don't change.
- **4.11** The platform never sells more tickets than are available, even when many people try to buy the last tickets at the same moment.

### 5. Buying tickets

- **5.1** Buyers check out as guests with their name and email, plus any other details the organizer asks for.
- **5.2** For each event, the organizer chooses whether to collect details only from the **person buying**, or from **each ticket holder**.
- **5.3** The steps are: choose tickets → choose seats (seated events only) → enter details → apply a promo code (optional) → review the order → pay.
- **5.4** Tickets are held for the buyer for a limited time while they pay. If time runs out, the tickets go back on sale and the order expires. (Hold length: see decision C11.)
- **5.5** One order can contain many tickets. Each ticket gets its own QR code and short code, and can be used by a different person.
- **5.6** The platform calculates every price and total itself. It never relies on figures sent from the buyer's browser.

### 6. Promotions and discount codes

- **6.1** A discount can be a percentage or a fixed amount.
- **6.2** Each code can be limited to particular tickets, a date range, a total number of uses, a number of uses per buyer (by email address), and a minimum number of tickets or order value. It applies to one event.
- **6.3** Promo codes and coupon codes work the same way.
- **6.4** A code's use is counted as soon as it's applied to a held order. If the hold expires, the use is released.

### 7. Payments

- **7.1** All payments go through **Pesapal**. Buyers pay by M-Pesa or card on Pesapal's secure payment page. Card details never reach the platform.
- **7.2** An order counts as paid only when the platform has confirmed the payment directly with Pesapal. A "payment successful" screen alone is never enough.
- **7.3** If Pesapal reports the same payment more than once, it's processed only once and tickets are issued only once.
- **7.4** If no confirmation arrives, the platform checks with Pesapal automatically every few minutes.
- **7.5** If a payment fails, the buyer can try again while their tickets are still held.
- **7.6** If the amount paid doesn't match the order, the order isn't marked as paid and is flagged for review.
- **7.7** If a payment arrives after the hold has expired, the late-payment policy applies (see decision C12).
- **7.8** If Pesapal later reverses a completed payment, the order is flagged and its tickets are handled under the refund policy.
- **7.9** Refunds are made through Pesapal where it supports them. A full refund cancels the order's tickets. (Who approves refunds: see decision C13.)
- **7.10** All ticket money is paid into the platform's Pesapal account. Organizers are paid manually, outside the platform. (How payouts are tracked: see decision C1.)

### 8. Tickets and delivery

- **8.1** Tickets are issued only after payment is confirmed: one ticket for each place bought.
- **8.2** Each ticket has a unique ticket number, a QR code and a **6-character short code**.
- **8.3** The buyer receives an email with a secure link to their tickets, which they can view and download.
- **8.4** A buyer who loses the link can ask for a new one by entering their email address. The platform doesn't reveal whether an email address has an order.
- **8.5** Each ticket shows the event details, ticket category, attendee name (where collected), ticket number, short code, and seat or delegation where relevant.
- **8.6** The QR code contains no personal information.
- **8.7** Organizers and admins can cancel (revoke) a ticket, giving a reason. Cancelled tickets are refused at the gate, and every cancellation is recorded.

### 9. Gate entry

- **9.1** Guards see only the events and gates their organizer assigned to them, and can check only tickets for those events.
- **9.2** Ticket checking needs an internet connection. If the scanner can't reach the platform, it shows **"Unable to verify — do not admit"**. It never shows a ticket as valid when it can't check.
- **9.3** Entry is allowed only while the gate is open, based on the platform's clock rather than the guard's phone.
- **9.4** The scanner shows one of these results:
  - **Valid:** admit.
  - **Already used:** shows when and at which gate.
  - **Invalid:** with the reason, such as cancelled, unpaid, wrong event, or gate not open.
  - **Unable to verify:** do not admit.
- **9.5** Each ticket admits **one person, once**. If two guards check the same ticket at the same moment, only one succeeds.
- **9.6** If a QR code won't scan, the attendee shows their downloaded or printed ticket and the guard **types its 6-character short code**. The code goes through the same checks and admits once. The result shows the attendee's name (where collected) so the guard can compare it with the ticket.
- **9.7** Code typing is limited per guard. After **5 wrong codes in a row**, that guard's code entry is locked until the organizer unlocks it; QR scanning keeps working.
- **9.8** Every check is recorded, whether it succeeded or not: the guard, the gate, the time, and whether the QR code was scanned or the code typed.
- **9.9** Guards see only the details needed to check entry, not buyers' full information.
- **9.10** Re-entry is not supported: once a ticket has been used, it can't be used again (see decision C6).

### 10. Reserved seating (optional)

- **10.1** Reserved seating can be switched on for an event. Events without it sell general admission tickets and need no seat setup.
- **10.2** Organizers set up sections, rows and seat numbers using simple forms, including adding many seats at once.
- **10.3** A seat chosen during checkout is held for the buyer and released if the hold expires.
- **10.4** A seat can never be sold twice.
- **10.5** The seat number appears on the ticket and on the guard's screen.
- **10.6** If the schedule comes under pressure, reserved seating is the first feature to move to a later release, by agreement through change control.

### 11. Reports and records

- **11.1** Organizers see, for their own events only: ticket sales, payment status, tickets remaining, promotion performance, attendance and gate activity.
- **11.2** Platform admins see platform-wide summaries, all transactions and the activity log.
- **11.3** Report figures always match the underlying orders and payments.
- **11.4** Sensitive actions are recorded with who did them and when: role changes, event cancellations, ticket cancellations, refunds, payment adjustments, and guard accounts and passwords.

### 12. Personal data

- **12.1** The platform collects only the attendee details each event needs.
- **12.2** Organizers see attendee details only for their own events. Guards see only what they need to check entry.
- **12.3** When someone asks for their personal data to be deleted, their name, email and phone number are removed, but the financial records needed by law are kept without them. (Retention periods: see decision C10.)

---

## Part B — Limitations of the first release

Please confirm that each of these is acceptable for launch.

| #     | Limitation                                       | What it means in practice                                                                                   | Accepted? |
| ----- | ------------------------------------------------ | ----------------------------------------------------------------------------------------------------------- | --------- |
| B1    | Internet needed at the gate                      | Without a connection, guards can't check tickets. There is no offline mode.                                 | ☐         |
| B2    | Google accounts for admins, organizers and staff | Anyone without a Google account must create one (free, and it can use an existing email address).           | ☐         |
| B3    | Guard passwords last one event                   | A password guard works one event at a time, gets a new password for each event, and sets their own password at each first sign-in. | ☐ |
| B4    | Short code needs the ticket                      | If an attendee's phone is dead and they have no printout, the guard can't find their ticket.                | ☐         |
| B5    | Pesapal is the only payment option               | If Pesapal is down, no one can buy tickets until it's back.                                                 | ☐         |
| B6    | Organizers are paid manually                     | Payouts to organizers happen outside the platform; automated payouts aren't included.                       | ☐         |
| B7    | No buyer accounts                                | Buyers can't log in to see past orders; they use the links in their emails.                                 | ☐         |
| B8    | English and Kenyan shillings only                | No other languages or currencies.                                                                           | ☐         |
| B9    | No mobile apps                                   | Everything, including the guard scanner, works in a phone or computer web browser.                         | ☐         |
| B10   | Simple seat setup                                | Seats are entered in forms; there's no visual drag-and-drop seating plan.                                   | ☐         |
| B11   | Seating may come later                           | If the schedule slips, reserved seating moves to a release after launch.                                    | ☐         |
| B12   | No re-entry                                      | Attendees who leave can't come back in on the same ticket (unless decision C6 changes this).                | ☐         |
| B13   | No ticket transfers                              | Buyers can't pass a ticket to someone else or change the name on it (unless decision C17 changes this).     | ☐         |
| B14   | No cash sales                                    | All sales are paid through Pesapal, including at the gate (unless decision C4 changes this).                | ☐         |
| B15   | Capacity on big sale days                        | The first hosting setup may slow down under a very large rush of buyers; it will be load-tested before launch. | ☐       |
| B16   | 12-week delivery                                 | Assumes a team of three to four developers and timely decisions; public holidays add one to two weeks.      | ☐         |

**Not included in this project** (from the Statement of Work, §9). These can be added later through a change request:
- Native Android and iOS apps.
- Offline ticket checking.
- Scanner hardware.
- Visual venue mapping.
- Automated fraud detection.
- Links to accounting, ERP, CRM or marketing systems.
- Loyalty programs, memberships and subscriptions.
- Multiple currencies and international tax.
- Automated organizer payouts.
- Third-party fees (Pesapal, hosting, email and SMS).
- Legal and tax advice.
- Content creation and event promotion.

---

## Part C — Decisions needed from you

Each decision has our recommendation. Please agree with it or choose another option.

| #   | Decision                                               | Our recommendation                                                                                          | Your decision |
| --- | ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------- | ------------- |
| C1  | How do we track what each organizer is owed?           | A settlement report per organizer and event (sales, refunds, fees, amount owed), plus a log of each manual payout | |
| C2  | Does the platform charge a fee, and who pays it?       | Your business decision; tell us the amount and whether the buyer or organizer pays it. Also decide who absorbs Pesapal's fees | |
| C3  | How do we check organizers before they sell?           | Verify business or ID details and payout account before an organizer's first paid event goes live. Also confirm with Pesapal that one account can collect for many organizers | |
| C4  | How are "At the Gate" tickets sold?                    | Buyers pay on their own phones through a QR code poster at the gate that links to the event page; no cash   | |
| C5  | What happens when an event is cancelled or postponed?  | **Cancelled:** all tickets cancelled, refunds in bulk, every buyer emailed. **Postponed:** tickets stay valid, buyers emailed and offered a refund for a set period | |
| C6  | Is re-entry allowed?                                   | No, for the first release                                                                                   | |
| C7  | How do free and sponsor tickets work?                  | Orders that total zero skip payment and issue tickets directly; each delegation gets a private link or code to claim its tickets | |
| C8  | Which messages do buyers get, and is SMS included?     | Emails for order confirmation, ticket delivery, event changes and cancellations. SMS only if you want it, chosen early | |
| C9  | Receipts and tax                                       | Email a receipt for every paid order. Your accountant confirms whether VAT and KRA eTIMS invoicing apply    | |
| C10 | How long do we keep attendee data?                     | Your policy; we suggest removing personal details a set time after each event, while keeping financial records | |
| C11 | How long are tickets held while a buyer pays?          | 10–15 minutes                                                                                               | |
| C12 | What if a payment arrives after the hold expired?      | Honor it if tickets are still available; otherwise refund it                                                | |
| C13 | Who approves refunds?                                  | The organizer requests, a platform admin approves, following each event's policy                            | |
| C14 | Must guards change their temporary password?           | Yes, at first sign-in for each event, so only the guard knows it                                            | |
| C15 | Should ticket prices be whole shillings?               | Yes, to avoid rounding problems with M-Pesa (we'll confirm with Pesapal)                                    | |
| C16 | How do we protect popular sales from bots?             | Add a CAPTCHA at checkout for high-demand events; tell us your largest expected event so we can load-test   | |
| C17 | Ticket transfers or name changes                       | Not in the first release                                                                                    | |
| C18 | Can organizers download attendee and sales lists?      | Yes, as spreadsheet (CSV) files                                                                             | |
| C19 | Can buyers browse events by type (music, sport and so on)? | Your choice; simple to add                                                                              | |
| C20 | Is a Swahili version of the public site needed?        | Not for the first release                                                                                   | |

---

## Part D — Sign-off

By signing, you confirm that the business rules in Part A are correct (with any changes noted), the limitations in Part B are accepted, and the decisions in Part C are recorded as above.

**Changes requested** (by rule or item number):

&nbsp;

&nbsp;

| Name | Role | Signature | Date |
| ---- | ---- | --------- | ---- |
|      |      |           |      |
|      |      |           |      |
|      |      |           |      |
