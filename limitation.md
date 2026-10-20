# Business Rules and Limitations

**Multi-Organizer Event Ticketing Platform · Version 1.10 · 20 October 2026**

**Status:** Draft for stakeholder confirmation

This document sets out how the platform will behave: the business rules it follows, the limitations of the first release, and the decisions still needed from you. Please confirm each section, or tell us what should change, before development starts. Changing a rule after the build has started takes longer and costs more.

## How to review

1. **Read the business rules (Part A).** Each rule has a number, such as **5.3**. If a rule is wrong or missing something, reply with its number and the change you want.
2. **Review the limitations (Part B).** Mark each one as accepted, or tell us why it isn't acceptable.
3. **Confirm the decisions (Part C).** Most were settled on 15 October 2026; confirm each one, or answer the few still open.
4. **Sign off (Part D)** once everything is agreed.

---

## Part A — Business rules

### 1. Organizers and roles

- **1.1** Many organizers share one platform. Each organizer sees only their own events, sales, attendees, staff and reports, never another organizer's.
- **1.2** The platform has five roles:

  | Role                | What they do                                                                                   |
  | ------------------- | ---------------------------------------------------------------------------------------------- |
  | **Platform admin**  | Full rights and privileges across the entire platform: approves/suspends organizers, oversees events, settings, reports and overrides, and deletes a person's data on request |
  | **Organizer staff** | Run their organizer account (profile, team members and guard accounts) and create events. They manage an event's prices, offerings, ticket waves, delegations, guards and reports only for the events they created or that a platform admin gave them access to |
  | **Event owner**     | Assigned to specific events with access restricted to only viewing, filtering, and exporting guest and attendee data |
  | **Guard**           | Scans tickets at the gate, only for the events and gates assigned to them                      |
  | **Buyer**           | Buys tickets as a guest and receives them by email; no account needed                          |

- **1.3** Every organizer must always have at least one active organizer staff member.
- **1.3a** A staff member automatically manages the events they create. Only a platform admin can give a staff member access to another event, or take it away. A platform admin can also create an event for an organizer and choose which of its staff manage it. For their organizer's other events, staff see a read-only summary (details and ticket setup) without sales, orders, guest lists or reports, and can't change anything. A published event always keeps at least one manager: its only manager can't be removed until a platform admin has given someone else access.
- **1.3b** Only platform admins see the list of everyone on the platform. Staff see their own organizer's staff and guard accounts, and the people assigned to the events they manage. Event owners see only the names and email addresses of the staff who manage their events.
- **1.4** Event owners have read-only access to attendee data for their assigned events, with privileges to view, filter, and export to CSV, but cannot alter event configurations, tickets, pricing, guards, or financials.
- **1.5** Organizers can't sign up on the website. A platform admin creates each organizer and invites its first staff member by email. That person then invites the rest of their team and their event owners, and creates guard accounts.
- **1.6** Platform admins are invited by email by an existing platform admin, and accept by signing in with a Google account that uses the invited address. Any admin can remove another admin's rights, but the last admin can't be removed. The very first admin is set up once, with a setup command run by the technical team. No one can make themselves an admin.
- **1.7** Platform admins have full operational authority across the platform.
- **1.8** Suspending an organizer stops new sales for their events and blocks their staff, event owners and guards from signing in. Their published events stay visible but can't be bought.
- **1.9** Before an organizer's first paid event can go on sale, a platform admin checks their business or ID details and enters the account they'll be paid into.
- **1.10** Only platform admins enter, change or see an organizer's payout account. Organizers send their bank details to a platform admin, and see only whether a verified payout account is on file. Until a platform admin has entered it, an event with paid tickets stays as a draft with the organizer: it can be prepared, but not published for buyers. Free events can still be published. Every change is recorded.

### 2. Signing in

- **2.1** Platform admins, organizer staff and event owners sign in with their **Google account**. They have no separate password for the platform.
- **2.2** Only Google accounts with a verified email address are accepted.
- **2.3** Staff, event owners and platform admins are invited by email and accept by opening their invitation link while signed in with a Google account that uses that same email address. Signing in on its own doesn't accept an invitation. Invitations expire after 7 days and can be used only once; if a link is lost, whoever invited the person can send a new one.
- **2.4** A suspended user can't sign in, even with a valid Google account.
- **2.4a** Anyone else who signs in with Google without an invitation or a role is turned away, and no account is made for them. Someone whose roles have all ended is turned away too, until they're invited again.
- **2.5** Buyers never sign in. They buy as guests and reach their tickets through an emailed link.

**Guards** sign in differently:

- **2.6** Guards sign in only with an **email and password account** created for them, using their own email address, by an organizer staff member or a platform admin. Guards never use Google.
- **2.7** Only guard accounts can check tickets. A staff member who also works a gate uses a separate guard account, even with the same email address.

Rules for guard password accounts:

- **2.8** Every guard gets their own account. One account is never shared between guards, because every ticket check is recorded against the guard who did it.
- **2.9** The platform creates a temporary password and shows it only once. The organizer passes it to the guard in person or by direct message, never in a group chat.
- **2.10** The guard must choose their own password the first time they sign in, so only they know it.
- **2.11** A guard password account can only ever be used for guard duties. It can never become an organizer staff member, event owner or admin.
- **2.12** A guard password account belongs to the organizer who created it. No other organizer can use that guard.
- **2.13** Each password works for **one event only**. The guard is assigned to one event at a time.
- **2.14** The password is **deleted as soon as the event closes**, and the guard is signed out immediately. An event closes when its last gate closes (or at its end time, if it has no gates), or when the organizer cancels or archives it, whichever happens first.
- **2.15** The guard's name stays on record after the password is deleted, so past ticket checks still show who did them. For the next event, the organizer issues a new password.
- **2.16** If a guard forgets their password, the organizer issues a new one. There is no "forgot password" email.
- **2.17** Repeated wrong passwords temporarily lock the account.
- **2.18** Guards are signed out automatically after at most 12 hours, and when their event closes.

### 3. Events

- **3.1** An event goes through these stages: **draft** → **published** → **closed** or **cancelled** → **archived**. A published event closes automatically when its last gate closes, or at its end time if it has no gates. Only published events can be seen and bought by the public. A draft that was never published can be deleted; an event that was ever published is never deleted, only archived, so its sales and entry records are kept.
- **3.2** Each event has: name, description, images, an event category from the platform's list, venue, date and time (with its timezone), ticket sales start and end, gate opening and closing times, total capacity, the attendee details required, ticket options, optional ticket waves for each category (rule 4.12), its own link and QR code (rule 5.12), optional seating, gates and guards, the identity number buyers must give (rule 5.7), whether checkout needs a CAPTCHA, and a cancellation and refund policy.
- **3.2a** Organizers upload event images as JPEG, PNG or WebP photos of up to 10 MB. The platform shrinks each image to at most 1,920 pixels on its longest side and under 500 KB, and removes hidden details such as where the photo was taken. Each visitor gets a copy sized for their screen, so pages load quickly on phones. A very detailed photo may lose a little sharpness. Other formats, such as SVG or GIF, aren't accepted.
- **3.3** Three sets of times are separate and independent:
  - **Ticket sales:** when buying starts and stops.
  - **Gates:** when entry starts and stops.
  - **Event:** when the event itself starts and ends.

  Gates can open before the event starts and stay open after it begins.
- **3.4** An event can be published only when it has at least one ticket option on sale, sensible times, and enough capacity for all its allocations. If any of its tickets is paid, the organizer must also be verified and have its payout account entered by a platform admin (1.9, 1.10).
- **3.5** Every change to a published event is recorded. Changes that would affect tickets already sold need explicit confirmation or are blocked: for example, reducing capacity below the number sold, changing the price of a ticket that has sales, or moving the event's dates.
- **3.6** When an event is **cancelled**, every ticket is cancelled, every buyer is emailed, and a platform admin refunds each order.
- **3.7** When an event is **postponed** to new dates, tickets stay valid. Staff confirm the change and choose the last day buyers can ask for a refund, following the event's refund policy. Every buyer is emailed a link to ask for a refund, which works until that day.

### 4. Tickets, prices and capacity

- **4.1** A **ticket type** describes how a ticket is sold: Early Bird, Normal, At the Gate, Group or Promoted. A **ticket category** describes the level of access: VVIP, VIP or Media. Organizers can add their own types and categories.
- **4.2** What a buyer actually purchases is a combination of type and category, for example "Early Bird VIP". Each combination has its own price, number available, sales period and minimum and maximum per order. Only combinations the organizer turns on can be bought.
- **4.3** Early Bird tickets stop selling when their sales period ends or when they sell out, whichever comes first.
- **4.4** Group tickets are bought in a set number at a time (for example 4, 8 or 12 for a group of 4), at the ticket's normal price per ticket: a group of 4 at KES 1,000 costs KES 4,000. The organizer can give group tickets a discount, as a percentage or a fixed amount off each ticket: KES 200 off makes a group of 4 cost 4 × 800 = KES 3,200. Every ticket in a group has the same whole-shilling price, and each has its own QR code.
- **4.5** At the Gate tickets come from the same capacity as advance tickets. Buyers buy them on their own phones through the public site, by scanning the event's QR code poster at the gate (rule 5.12). There are no cash sales.
- **4.6** Media tickets can be set to need the organizer's approval: the buyer pays first, and the tickets are issued only when the organizer approves them. A rejected request is refunded by a platform admin. Organizers can also issue Media tickets manually.
- **4.7** Promoted tickets need clear rules about who can buy them and at what price.
- **4.8** **Delegations** are allocations set aside for sponsors, departments or invited groups. For each delegation, staff set aside a number of tickets in each category it may claim, with their price (often free). These tickets are never sold to the public, don't follow the ticket waves, and count towards the event's total capacity like any other ticket. Members claim them through a private link or access code. Staff can return unclaimed tickets to public sale (rule 4.14).
- **4.9** Prices are in **whole Kenyan shillings** (no cents) and are stored exactly. A percentage discount is rounded to the nearest shilling.
- **4.10** The price a buyer pays is fixed when they order. If the organizer changes prices later, existing orders don't change.
- **4.11** The platform never sells more tickets than are available, even when many people try to buy the last tickets at the same moment.
- **4.12** **Ticket waves (optional).** When setting up an event, organizer staff choose for each ticket category whether it sells in **waves**, such as Wave 1, Wave 2 and Wave 3. Each wave has its own dates, and in each wave staff choose the ticket type, the number of tickets and the price. Categories that don't use waves sell as in rule 4.2.
- **4.13** A category's waves follow one another and never overlap. Each wave goes on sale on its start date and stops at its end date. If a wave sells out early, that category has nothing on sale until the next wave starts.
- **4.14** When a wave ends, its unsold tickets stay unsold unless staff move them. Organizer staff can move unsold tickets from one ticket option to another in the same category: from an ended wave to a later wave, or from a delegation back to public sale. Every move is recorded.

### 5. Buying tickets

- **5.1** Buyers check out as guests with their name and email, plus any other details the organizer asks for.
- **5.2** For each event, the organizer chooses whether to collect details only from the **person buying**, or from **each ticket holder**.
- **5.3** The steps are: choose tickets → choose seats (seated events only) → enter details → apply a promo code (optional) → review the order → pay. The tickets are held as soon as the details are entered (rule 5.4), so a promo code applies to the held order (rule 6.4).
- **5.4** Tickets are held for the buyer for **15 minutes** while they pay. If time runs out, the tickets go back on sale and the order expires.
- **5.5** One order can contain many tickets. Each ticket gets its own QR code and short code, and can be used by a different person.
- **5.6** The platform calculates every price and total itself. It never relies on figures sent from the buyer's browser.
- **5.7** When creating an event, the organizer chooses whether buyers must give a **phone number**, an **ID number**, or neither. Buyers can't choose: checkout asks for exactly what the event set, and never for both. With a phone or ID number, every ticket holder gives their name and that number; only the last 4 digits of an ID number are kept, and the guard checks the last 4 digits at the gate. The choice can be changed only while the event is still a draft.
- **5.8** Any extra questions an organizer adds are pick-lists, checkboxes or dates. There are no free-text questions.
- **5.9** Orders that cost nothing (free tickets, free sponsor tickets or a 100% discount) skip payment, and the tickets are issued straight away.
- **5.10** For high-demand events, the organizer can require a CAPTCHA at checkout to stop bots.
- **5.11** The platform's **landing page** lists every ticket option on sale across all published events: the event, date and venue, ticket type, category, wave and price, each with a Buy button. Buyers can search by event name or venue, and filter by event category and date. Sold-out options stay listed, marked "Sold out", and upcoming waves are shown greyed out with their start date and price. Sponsor (delegation) tickets are never listed.
- **5.12** Every event has its own **link** and **QR code**, which open its event page. Organizer staff can copy the link, and download the QR code as an image or as a printable poster to put up at the gate. The link can be changed while the event is a draft, but not once it's published, so printed posters and shared links keep working.

### 6. Promotions and discount codes

- **6.1** A discount can be a percentage or a fixed amount.
- **6.2** Each code can be limited to particular tickets, a date range, a total number of uses, a number of uses per buyer (by email address), and a minimum number of tickets or order value. It applies to one event.
- **6.3** Promo codes and coupon codes work the same way.
- **6.4** A code's use is counted as soon as it's applied to a held order. If the hold expires, the use is released.
- **6.5** A promo code can be used on group tickets. It applies after the group discount, to the discounted total.

### 7. Payments

- **7.1** All payments go through **Pesapal**. Buyers pay by M-Pesa or card on Pesapal's secure payment page. Card details never reach the platform, and the platform doesn't keep the M-Pesa number or card used to pay.
- **7.2** An order counts as paid only when the platform has confirmed the payment directly with Pesapal. A "payment successful" screen alone is never enough.
- **7.3** If Pesapal reports the same payment more than once, it's processed only once and tickets are issued only once.
- **7.4** If no confirmation arrives, the platform checks with Pesapal automatically every few minutes.
- **7.5** If a payment fails, the buyer can try again while their tickets are still held.
- **7.6** If the amount paid doesn't match the order, the order isn't marked as paid and the money is refunded automatically.
- **7.7** If a payment arrives after the hold has expired, it's refunded automatically. The buyer can order again.
- **7.8** If Pesapal later reverses a completed payment, the order is refunded and its unused tickets are cancelled.
- **7.9** Only platform admins make refunds, ticket by ticket, through Pesapal where it supports them. A refunded ticket is cancelled, and its place goes back on sale if the event is still on.
- **7.10** The platform is the seller of every ticket: all ticket money is paid into the platform's Pesapal account. Organizers are paid manually, outside the platform, from a settlement report per organizer and event (sales, refunds, fees and amount owed). Admins record each payout.
- **7.11** The platform's fee and Pesapal's fees are deducted from the organizer's settlement; buyers pay only the ticket price. How the platform fee is calculated is still to be decided (C2).
- **7.12** Platform admins see every payment as it happens, in a live table showing each payment's status and the status of its order and tickets.

### 8. Tickets and delivery

- **8.1** Tickets are issued only after payment is confirmed: one ticket for each place bought.
- **8.2** Each ticket has a unique ticket number, a QR code and a **6-character short code**.
- **8.3** The buyer receives an order confirmation email with a secure link to their tickets, which they can view and download, and a receipt for every paid order. Receipts show the event name, the organizer's name and the event's "Presented by" name if it has one.
- **8.4** A buyer who loses the link can ask for a new one by entering their email address. The platform doesn't reveal whether an email address has an order.
- **8.5** Each ticket shows the event details, the organizer's name and the event's "Presented by" name if it has one (for example a sponsor), ticket category, attendee name (where collected), ticket number, short code, and seat or delegation where relevant.
- **8.6** The QR code contains no personal information.
- **8.7** Organizers and admins can cancel (revoke) a ticket, giving a reason. Cancelled tickets are refused at the gate, and every cancellation is recorded.
- **8.8** A ticket holder can pass a ticket to someone else from their ticket link until the event starts, unless the event asks for ID numbers. The new holder gets a new QR code and short code, and the old ones stop working.
- **8.9** Buyers get emails only: order confirmation, tickets, receipts, event changes and cancellations. There are no SMS messages.

### 9. Gate entry

- **9.1** Guards see only the events and gates their organizer assigned to them, and can check only tickets for those events.
- **9.2** Ticket checking needs an internet connection. If the scanner can't reach the platform, it shows **"Unable to verify — do not admit"**. It never shows a ticket as valid when it can't check.
- **9.3** Entry is allowed only while the gate is open (or, for an event without gates, between its start and end times), based on the platform's clock rather than the guard's phone.
- **9.4** The scanner shows one of these results:
  - **Valid:** admit.
  - **Already used:** shows when and at which gate.
  - **Invalid:** with the reason, such as cancelled, unpaid, wrong event, or gate not open.
  - **Unable to verify:** do not admit.
- **9.5** Each ticket admits **one person, once**. If two guards check the same ticket at the same moment, only one succeeds.
- **9.6** If a QR code won't scan, the attendee shows their downloaded or printed ticket and the guard **types its 6-character short code**. The code goes through the same checks and admits once. The result shows the attendee's name (where collected) so the guard can compare it with the ticket, and, when the event asks for a phone or ID number, its last 4 digits.
- **9.7** Code typing is limited per guard. After **5 wrong codes in a row**, that guard's code entry is locked until the organizer unlocks it; QR scanning keeps working.
- **9.8** Every check is recorded, whether it succeeded or not: the guard, the gate, the time, and whether the QR code was scanned or the code typed.
- **9.9** Guards see only the details needed to check entry, not buyers' full information.
- **9.10** Re-entry is not supported: once a ticket has been used, it can't be used again.
- **9.11** An event can have several guards. Every check records the guard who made it.

### 10. Reserved seating (optional)

- **10.1** Reserved seating can be switched on for an event. Events without it sell general admission tickets and need no seat setup.
- **10.2** Organizers set up sections, rows and seat numbers using simple forms, including adding many seats at once.
- **10.3** A seat chosen during checkout is held for the buyer and released if the hold expires.
- **10.4** A seat can never be sold twice.
- **10.5** The seat number appears on the ticket and on the guard's screen.
- **10.6** If the schedule comes under pressure, reserved seating is the first feature to move to a later release, by agreement through change control.

### 11. Reports and records

- **11.1** Organizer staff see, only for the events they manage: ticket sales, payment status, tickets remaining, promotion performance, attendance, gate activity and the activity log. They can download guest and sales lists as spreadsheets (CSV).
- **11.2** Platform admins see platform-wide summaries, all transactions, the activity log, and each organizer's settlement and payouts.
- **11.3** Report figures always match the underlying orders and payments.
- **11.4** Sensitive actions are recorded with who did them and when: role changes, event cancellations, ticket cancellations, refunds, payment adjustments, and guard accounts and passwords, plus every sign-in, sign-out, failed sign-in and lockout. Nobody can change or delete these records; they are removed only when their retention period ends (12.4).
- **11.5** Event owners see only the guest list of the events they are assigned to: each ticket holder's name, ticket category, delegation, check-in status and entry time (when their ticket was scanned in). They can filter it and download it as a spreadsheet (CSV), but see no contact details, ID numbers, sales, payment or settlement figures.

### 12. Personal data

- **12.1** The platform collects only the attendee details each event needs.
- **12.2** Organizer staff see attendee details only for the events they manage, and event owners only for the events they are assigned to. Guards see only what they need to check entry.
- **12.3** Personal data is kept until someone asks for it to be deleted. Then their name, email, phone number and ID digits are removed, but the financial and entry records needed by law are kept without them. A platform admin carries out each request. The platform's logs never hold a buyer's or attendee's name, email, phone number or ID digits, so they need no cleaning.
- **12.4** The platform's logs are kept for set periods, then deleted automatically: the activity log (including sign-ins) and payment records of Pesapal's notifications for 5 years, the gate scan history for 12 months after each event, and technical logs for 90 days. Orders, payments, refunds, tickets and payouts are kept, not deleted.
- **12.5** Guards can see the tickets they have scanned at their current event (time, result and ticket category), but not the attendees' names.

---

## Part B — Limitations of the first release

Please confirm that each of these is acceptable for launch.

| #     | Limitation                                       | What it means in practice                                                                                   | Accepted? |
| ----- | ------------------------------------------------ | ----------------------------------------------------------------------------------------------------------- | --------- |
| B1    | Internet needed at the gate                      | Without a connection, guards can't check tickets. There is no offline mode.                                 | ☐         |
| B2    | Google accounts for admins, staff and event owners | Anyone without a Google account must create one (free, and it can use an existing email address).           | ☐         |
| B3    | Guard passwords last one event                   | A password guard works one event at a time, gets a new password for each event, and sets their own password at each first sign-in. | ☐ |
| B4    | Short code needs the ticket                      | If an attendee's phone is dead and they have no printout, the guard can't find their ticket.                | ☐         |
| B5    | Pesapal is the only payment option               | If Pesapal is down, no one can buy tickets until it's back.                                                 | ☐         |
| B6    | Organizers are paid manually                     | Payouts to organizers happen outside the platform; automated payouts aren't included.                       | ☐         |
| B7    | No buyer accounts                                | Buyers can't log in to see past orders; they use the links in their emails.                                 | ☐         |
| B8    | English and Kenyan shillings only                | No other languages or currencies.                                                                           | ☐         |
| B9    | No mobile apps                                   | Everything, including the guard scanner, works in a phone or computer web browser.                         | ☐         |
| B10   | Simple seat setup                                | Seats are entered in forms; there's no visual drag-and-drop seating plan.                                   | ☐         |
| B11   | Seating may come later                           | If the schedule slips, reserved seating moves to a release after launch.                                    | ☐         |
| B12   | No re-entry                                      | Attendees who leave can't come back in on the same ticket.                                                  | ☐         |
| B13   | Limited ticket transfers                         | Tickets can be passed on only until the event starts, and never for events that ask for ID numbers.        | ☐         |
| B14   | No cash sales                                    | All sales are paid through Pesapal, including at the gate.                                                  | ☐         |
| B15   | Capacity on big sale days                        | The first hosting setup may slow down under a very large rush of buyers; it will be load-tested before launch. | ☐       |
| B16   | 12-week delivery                                 | Assumes a team of three to four developers and timely decisions; public holidays add one to two weeks.      | ☐         |
| B17   | No SMS                                           | Buyers get emails only.                                                                                     | ☐         |
| B18   | No free-text questions                           | Extra questions are pick-lists, checkboxes or dates.                                                        | ☐         |
| B19   | Guards need an email address                     | Every guard account uses the guard's own email address, and guards can't sign in with Google.              | ☐         |
| B20   | Waves open on their dates                        | If a wave sells out early, its category has nothing on sale until the next wave's start date; the next wave doesn't open early. | ☐ |
| B21   | Unsold wave tickets move by hand                 | Tickets a wave doesn't sell stay unsold until organizer staff move them to a later wave.                   | ☐         |
| B22   | Emails through the hosting's mail server         | On a very busy sale day, ticket emails may be delayed by the hosting's hourly sending limit, and there is no record of whether each email was delivered. | ☐ |
| B23   | Payout account changes rely on admins            | Organizers send bank details to a platform admin outside the platform. How the admin checks a request is genuine is left to them, so a convincing fake request could redirect a payout. | ☐ |
| B24   | Suspension signs people out everywhere           | Suspending an organizer signs out its staff even if they also work for another organizer; they sign back in to continue there. | ☐ |
| B25   | Admins are needed to go on sale                  | Paid events stay in draft until a platform admin enters the payout account, and staff wait for an admin to give them access to events they didn't create. | ☐ |
| B26   | Organizers are added by admins                   | Organizers can't sign up on the website: a platform admin creates each one and invites its first staff member. | ☐ |

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

## Part C — Decisions

These were settled on 15, 16 and 20 October 2026. Please confirm each one, or answer it where it's still open.

| #   | Decision                                               | Decision recorded                                                                                           | Confirmed? |
| --- | ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------- | ---------- |
| C1  | How do we track what each organizer is owed?           | A settlement report per organizer and event (sales, refunds, fees, amount owed), plus a log of each manual payout | |
| C2  | Does the platform charge a fee, and who pays it?       | Deducted from the organizer, and Pesapal's fees too. **Still open:** how the fee is calculated (a percentage, a fixed amount per ticket, or both) | |
| C3  | How do we check organizers before they sell?           | A platform admin verifies business or ID details and enters the payout account before the organizer's first paid event. Pesapal declined to collect money on behalf of other businesses, so the platform sells the tickets itself (C35) | |
| C4  | How are "At the Gate" tickets sold?                    | Buyers pay on their own phones through a QR code poster at the gate that links to the event page; the platform gives each event its link, QR code and a printable poster; no cash | |
| C5  | What happens when an event is cancelled or postponed?  | **Cancelled:** all tickets cancelled, every buyer emailed, an admin refunds each order. **Postponed:** tickets stay valid, buyers emailed a refund-request link valid for a set period | |
| C6  | Is re-entry allowed?                                   | No, for the first release                                                                                   | |
| C7  | How do free and sponsor tickets work?                  | Orders that total zero skip payment and issue tickets directly; each delegation gets a private link or code to claim its tickets, which are set aside from public sale | |
| C8  | Which messages do buyers get, and is SMS included?     | Emails for order confirmation, ticket delivery, receipts, event changes and cancellations. No SMS            | |
| C9  | Receipts and tax                                       | Email a receipt for every paid order. Your accountant confirmed that VAT and KRA eTIMS invoicing don't apply. **Please confirm** that this still holds now that the platform is the seller of every ticket (C35) | |
| C10 | How long do we keep attendee data?                     | Until someone asks for it to be deleted; a platform admin carries out each request                         | |
| C11 | How long are tickets held while a buyer pays?          | 15 minutes                                                                                                  | |
| C12 | What if a payment arrives after the hold expired?      | Refund it automatically; the buyer orders again                                                             | |
| C13 | Who approves refunds?                                  | Only platform admins make refunds                                                                           | |
| C14 | Must guards change their temporary password?           | Yes, at first sign-in for each event, so only the guard knows it                                            | |
| C15 | Should ticket prices be whole shillings?               | Yes: whole shillings only, with no cents; percentage discounts round to the nearest shilling | |
| C16 | How do we protect popular sales from bots?             | The organizer can require a CAPTCHA at checkout for each event. Your largest expected event is 1,000 to 5,000 tickets, and we load-test for 5,000. **Still open:** how many buyers arrive in the first minutes of a big sale | |
| C17 | Ticket transfers or name changes                       | Allowed until the event starts, except for events that ask for ID numbers                                  | |
| C18 | Can organizers download attendee and sales lists?      | Yes, as spreadsheet (CSV) files; event owners can download the guest list of their assigned events          | |
| C19 | Can buyers browse events by type (music, sport and so on)? | Yes, from a category list the platform admins manage                                                    | |
| C20 | Is a Swahili version of the public site needed?        | Not for the first release                                                                                   | |
| C21 | Which email provider sends the tickets?                | Your hosting's own (cPanel) mail server. Before launch we need its hourly sending limit, and the SPF, DKIM and DMARC records set up for your domain (B22) | |
| C22 | Can organizers sell tickets in waves?                  | Yes, as an option for each ticket category: each wave has its own dates, ticket type, number of tickets and price; waves open on their start dates only; staff move unsold tickets by hand | |
| C23 | What does the landing page show?                       | Every public ticket option across all events, with search by event name or venue, event category and date filters, sold-out options marked and upcoming waves shown | |
| C24 | Are sponsor tickets set aside?                         | Yes: each delegation's tickets are its own allocation, never sold to the public and outside the waves       | |
| C25 | When is a promo code applied?                          | On the order review, after the tickets are held                                                             | |
| C26 | What is the platform's public name?                    | **Still open:** used on the landing page, in emails and on the QR code posters                              | |
| C27 | What do event owners see in the guest list?            | Each ticket holder's name, ticket category, delegation, check-in status and entry time; no contact details, ID numbers or money figures | |
| C28 | Who can make someone a platform admin?                 | Only an existing platform admin, by email invitation; the last admin can't be removed; the very first admin is set up once with a setup command | |
| C29 | Can every staff member manage every event of their organizer? | No. Staff manage the events they create; a platform admin grants access to any other event. Other events show a read-only summary without sales or guest data | |
| C30 | Who can see the list of people on the platform?        | Only platform admins see everyone; staff see their own organizer's staff; event owners see the staff who manage their events | |
| C31 | Who can change an organizer's payout account?          | Only platform admins, who are also the only ones to see it; staff see only whether a verified payout account is on file. Until it's entered, events with paid tickets stay in draft | |
| C32 | Can a platform admin set up an event for an organizer? | Yes: the admin creates it as a draft and chooses which of the organizer's staff manage it; the admin doesn't become one of its managers | |
| C33 | How long are the platform's logs kept?                 | Activity log and payment notification records 5 years (the usual tax-record period; your accountant confirms), gate scans 12 months after the event, technical logs 90 days | |
| C34 | Is a history of sign-ins kept?                         | Yes: every sign-in, sign-out, failed attempt and lockout, visible to platform admins, and to an event's managers for its guards | |
| C35 | Who sells the tickets to buyers?                       | The platform sells every ticket and pays each organizer as its supplier, under a contract with each organizer that your lawyer prepares. **Still open:** Pesapal must confirm this arrangement in writing | |
| C36 | What do tickets and receipts show about who runs the event? | The event name, the organizer's name and an optional "Presented by" name for each event, such as a sponsor. No organizer agreement or terms-of-sale checkbox is built in | |
| C37 | Can an event lose its last manager?                    | No. While an event is on sale, its only manager can't be removed until a platform admin gives another staff member access | |
| C38 | Can admins watch payments as they happen?             | Yes: a live table of every payment, with its status and the status of its order and tickets, updating every few seconds | |
| C39 | How large can event images be?                         | Each image is shrunk to under 500 KB and at most 1,920 pixels on its longest side when it's uploaded, and each visitor gets a copy sized for their screen | |
| C40 | Can anyone sign up as an organizer?                    | No. A platform admin creates each organizer and invites its first staff member, who then invites the rest of the team. Anyone who signs in with Google without an invitation or a role is turned away | |
| C41 | Can an event's link change after it's published?      | No. Staff can change it while the event is a draft; once published it's fixed, so printed posters and shared links keep working | |
| C42 | Who decides how long buyers can ask for a refund after a postponement? | Staff, when they confirm the new dates, following the event's refund policy | |
| C43 | How does someone accept an invitation?                 | By opening the invitation link while signed in with the invited Google address; signing in alone isn't enough. A lost link can be replaced by whoever sent it | |
| C44 | How are group tickets priced?                          | At the normal price per ticket, bought in multiples of the group size: a group of 4 at KES 1,000 costs KES 4,000 | |
| C45 | What does a group discount's "fixed rate" mean?       | A fixed amount off each ticket: KES 200 off a group of 4 at KES 1,000 gives 4 × 800 = KES 3,200 | |
| C46 | Can a promo code be used on top of a group discount?  | Yes. It applies after the group discount, to the discounted total, under the promotion's own rules | |

---

## Part D — Sign-off

By signing, you confirm that the business rules in Part A are correct (with any changes noted), the limitations in Part B are accepted, and the decisions in Part C are confirmed as recorded above.

**Changes requested** (by rule or item number):

&nbsp;

&nbsp;

| Name | Role | Signature | Date |
| ---- | ---- | --------- | ---- |
|      |      |           |      |
|      |      |           |      |
|      |      |           |      |
