# **Business Rules and Limitations**

**Multi-Organizer Event Ticketing Platform · Version 1.0 · 13 October 2026**  
This document sets out how the platform will behave: the business rules it follows, the limitations of the first release, and the decisions we still need to make. Please confirm each section on what should change, before development starts. Changing a rule after the build has started takes longer and costs more.

# **Part A — Business rules**

## **1\. Organizers and roles**

*  Many organizers share one platform. Each organizer sees only their own events, sales, attendees, staff and reports, never another organizer's.  
* The platform has five roles:

| Role | What they do |
| :---- | :---- |
| **Platform admin** | Full rights and privileges across the entire platform: approves and suspends organizers, oversees all events, manages settings, reports, and overrides |
| **Organizer staff** | Full control of their organizer account: events, prices, ticket offerings, team members, delegations, guards and reports |
| **Event owner** | Assigned to specific events with access restricted to only viewing, filtering, and exporting guest and attendee data |
| **Guard** | Scans tickets at the gate, only for the events and gates assigned to them |
| **Buyer** | Buys tickets as a guest and receives them by email; no account needed |

* Every organizer must always have at least one active staff member.  
* Event owners have read-only access to attendee details for their assigned event(s) and can filter and export guest data to CSV, but cannot modify event configurations, tickets, pricing, guards, or financials.  
* New organizers sign in, apply for an organizer account, and wait for a platform admin to approve them before they can create events.  
* Platform admins are appointed by an existing platform admin or via system command. No one can make themselves an admin.  
* Platform admins have full operational authority across the platform.  
* Suspending an organizer stops new sales for their events and blocks their staff, event owners and guards from signing in. Their published events stay visible but can't be bought.

## **2\. Signing in**

* Platform admins, organizer staff and event owners sign in with their **Google account**. They have no separate password for the platform.  
* Only Google accounts with a verified email address are accepted.  
* Staff and event owners are invited by email and must accept by signing in with a Google account that uses that same email address. Invitations expire and can be used only once.  
* A suspended user can't sign in, even with a valid Google account.  
* Buyers never sign in. They buy as guests and reach their tickets through an emailed link.

**Guards** can sign in in either of two ways:

* With Google, after accepting an email invitation, the same way as staff.  
* With an email and password account created for them by an organizer staff member or a platform admin.

Rules for guard password accounts:

* Every guard gets their own account. One account is never shared between guards, because every ticket check is recorded against the guard who did it.  
* The platform creates a temporary password and shows it only once. The organizer passes it to the guard in person or by direct message, never in a group chat.  
* The guard must choose their own password the first time they sign in, so only they know it.  
* A guard password account can only ever be used for guard duties. It can never become an organizer staff member, event owner or admin.  
* A guard password account belongs to the organizer who created it. No other organizer can use that guard.  
* Each password works for **one event only**. The guard is assigned to one event at a time.  
* The password is **deleted as soon as the event closes**, and the guard is signed out immediately. An event closes when its last gate closes, or when the organizer cancels or archives it, whichever happens first.  
* The guard's name stays on record after the password is deleted, so past ticket checks still show who did them. For the next event, the organizer issues a new password.  
* If a guard forgets their password, the organizer issues a new one. There is no "forgot password" email.  
* Repeated wrong passwords temporarily lock the account.  
* Guards are signed out automatically after at most 12 hours, and when their event closes.

## **3\. Events**

* An event goes through these stages: **draft** → **published** → **cancelled** or **archived**. Only published events can be seen and bought by the public.  
* Each event has: name, description, images, venue, date and time (with its timezone), ticket sales start and end, gate opening and closing times, total capacity, the attendee details required, ticket options, optional seating, gates and guards, and a cancellation and refund policy.  
* Three sets of times are separate and independent:  
  * **Ticket sales:** when buying starts and stops.  
  * **Gates:** when entry starts and stops.  
  * **Event:** when the event itself starts and ends.  
    Gates can open before the event starts and stay open after it begins.  
* An event can be published only when it has at least one ticket option on sale, sensible times, and enough capacity for all its allocations.  
* Every change to a published event is recorded. Changes that would affect tickets already sold need explicit confirmation or are blocked: for example, reducing capacity below the number sold, changing the price of a ticket that has sales, or moving the event's dates.

## **4\. Tickets, prices and capacity**

* A **ticket type** describes how a ticket is sold: Early Bird, Normal, At the Gate, Group or Promoted. A **ticket category** describes the level of access: VVIP, VIP or Media. Organizers can add their own types and categories.  
* What a buyer actually purchases is a combination of type and category, for example "Early Bird VIP". Each combination has its own price, number available, sales period and minimum and maximum per order. Only combinations the organizer turns on can be bought.  
* Early Bird tickets stop selling when their sales period ends or when they sell out, whichever comes first.  
* Group tickets require a set number of tickets and use the group price.  
* At the Gate tickets come from the same capacity as advance tickets.  
* Media tickets can be set to need the organizer's approval, or can be issued manually by the organizer.  
* Promoted tickets need clear rules about who can buy them and at what price.  
* **Delegations** are allocations set aside for sponsors, departments or invited groups. Each has its own number of tickets, allowed categories and prices.  
* Prices are in **Kenyan shillings** and are stored exactly, with no rounding errors.  
* The price a buyer pays is fixed when they order. If the organizer changes prices later, existing orders don't change.  
* The platform never sells more tickets than are available, even when many people try to buy the last tickets at the same moment.

## **5\. Buying tickets**

* Buyers check out as guests with their name and email, plus any other details the organizer asks for.  
* For each event, the organizer chooses whether to collect details only from the **person buying**, or from **each ticket holder**.  
* The steps are: choose tickets → choose seats (seated events only) → enter details → apply a promo code (optional) → review the order → pay.  
* Tickets are held for the buyer for a limited time while they pay. If time runs out, the tickets go back on sale and the order expires.   
* One order can contain many tickets. Each ticket gets its own QR code and short code, and can be used by a different person.  
* The platform calculates every price and total itself. It never relies on figures sent from the buyer's browser.

## **6\. Promotions and discount codes**

* A discount can be a percentage or a fixed amount.  
* Each code can be limited to particular tickets, a date range, a total number of uses, a number of uses per buyer (by email address), and a minimum number of tickets or order value. It applies to one event.  
* Promo codes and coupon codes work the same way.  
* A code's use is counted as soon as it's applied to a held order. If the hold expires, the use is released.

## **7\. Payments**

* All payments go through **Pesapal**. Buyers pay by M-Pesa or card on Pesapal's secure payment page. Card details never reach the platform.  
* An order counts as paid only when the platform has confirmed the payment directly with Pesapal. A "payment successful" screen alone is never enough.  
* If Pesapal reports the same payment more than once, it's processed only once and tickets are issued only once.  
* If no confirmation arrives, the platform checks with Pesapal automatically every few minutes.  
* If a payment fails, the buyer can try again while their tickets are still held.  
* If the amount paid doesn't match the order, the order isn't marked as paid and is flagged for review.  
* If a payment arrives after the hold has expired, the late-payment policy applies .  
* If Pesapal later reverses a completed payment, the order is flagged and its tickets are handled under the refund policy.  
* Refunds are made through Pesapal where it supports them. A full refund cancels the order's tickets.  
* All ticket money is paid into the platform's Pesapal account. Organizers are paid manually, outside the platform. 

## **8\. Tickets and delivery**

* Tickets are issued only after payment is confirmed: one ticket for each place bought.  
* Each ticket has a unique ticket number, a QR code and a **6-character short code**.  
* The buyer receives an email with a secure link to their tickets, which they can view and download.  
* A buyer who loses the link can ask for a new one by entering their email address. The platform doesn't reveal whether an email address has an order.  
* Each ticket shows the event details, ticket category, attendee name (where collected), ticket number, short code, and seat or delegation where relevant.  
* The QR code contains no personal information.  
* Organizers and admins can cancel (revoke) a ticket, giving a reason. Cancelled tickets are refused at the gate, and every cancellation is recorded.

## **9\. Gate entry**

* Guards see only the events and gates their organizer assigned to them, and can check only tickets for those events.  
* Ticket checking needs an internet connection. If the scanner can't reach the platform, it shows **"Unable to verify — do not admit"**. It never shows a ticket as valid when it can't check.  
* Entry is allowed only while the gate is open, based on the platform's clock rather than the guard's phone.  
* The scanner shows one of these results:  
  * **Valid:** admit.  
  * **Already used:** shows when and at which gate.  
  * **Invalid:** with the reason, such as cancelled, unpaid, wrong event, or gate not open.  
  * **Unable to verify:** do not admit.  
* Each ticket admits **one person, once**. If two guards check the same ticket at the same moment, only one succeeds.  
* If a QR code won't scan, the attendee shows their downloaded or printed ticket and the guard **types its 6-character short code**. The code goes through the same checks and admits once. The result shows the attendee's name (where collected) so the guard can compare it with the ticket.  
* Code typing is limited per guard. After **5 wrong codes in a row**, that guard's code entry is locked until the organizer unlocks it; QR scanning keeps working.  
* Every check is recorded, whether it succeeded or not: the guard, the gate, the time, and whether the QR code was scanned or the code typed.  
* Guards see only the details needed to check entry, not buyers' full information.  
* Re-entry is not supported: once a ticket has been used, it can't be used again.

## **10\. Reserved seating (optional)**

* Reserved seating can be switched on for an event. Events without it sell general admission tickets and need no seat setup.  
* Organizers set up sections, rows and seat numbers using simple forms, including adding many seats at once.  
* A seat chosen during checkout is held for the buyer and released if the hold expires.  
* A seat can never be sold twice.  
* The seat number appears on the ticket and on the guard's screen.  
* If the schedule comes under pressure, reserved seating is the first feature to move to a later release, by agreement through change control.

## **11\. Reports and records**

* Organizers see, for their own events only: ticket sales, payment status, tickets remaining, promotion performance, attendance and gate activity.  
* Platform admins see platform-wide summaries, all transactions and the activity log.  
* Event owners see only the guest list of the events they are assigned to. They can filter it and download it as a spreadsheet (CSV), but see no sales, payment or settlement figures.  
* Report figures always match the underlying orders and payments.  
* Sensitive actions are recorded with who did them and when: role changes, event cancellations, ticket cancellations, refunds, payment adjustments, and guard accounts and passwords.

## **12\. Personal data**

* The platform collects only the attendee details each event needs.  
* Organizers see attendee details only for their own events, and event owners only for the events they are assigned to. Guards see only what they need to check entry.  
* When someone asks for their personal data to be deleted, their name, email and phone number are removed, but the financial records needed by law are kept without them. 

# **Part B — Limitations of the first release**

Please confirm that each of these is acceptable for launch.

| Limitation | What it means in practice | Accepted(yes/no) |
| :---- | :---- | :---- |
| Internet needed at the gate | Without a connection, guards can't check tickets. There is no offline mode. |  |
| Google accounts for admins, staff and event owners | Anyone without a Google account must create one (free, and it can use an existing email address). |  |
| Guard passwords last one event | A password guard works one event at a time, gets a new password for each event, and sets their own password at each first sign-in. |  |
| Short code needs the ticket | If an attendee's phone is dead and they have no printout, the guard can't find their ticket. |  |
| Pesapal is the only payment option | If Pesapal is down, no one can buy tickets until it's back. |  |
| Organizers are paid manually | Payouts to organizers happen outside the platform; automated payouts aren't included. |  |
| No buyer accounts | Buyers can't log in to see past orders; they use the links in their emails. |  |
| English and Kenyan shillings only | No other languages or currencies. |  |
| No mobile apps | Everything, including the guard scanner, works in a phone or computer web browser. |  |
| Simple seat setup | Seats are entered in forms; there's no visual drag-and-drop seating plan. |  |
| Seating may come later | If the schedule slips, reserved seating moves to a release after launch. |  |
| No re-entry | Attendees who leave can't come back in on the same ticket. |  |
| No ticket transfers | Buyers can't pass a ticket to someone else or change the name on it. | To be discussed |
| No cash sales | All sales are paid through Pesapal, including at the gate. |  |
| Capacity on big sale days | The first hosting setup may slow down under a very large rush of buyers; it should be load-tested before launch. |  |

**Not included in this project**. 

* Multiple currencies and international tax.  
* Automated organizer payouts.  
* Third-party fees (Pesapal, hosting, email and SMS).  
* Legal and tax advice.  
* Content creation and event promotion.

# **Part C — Decisions needed** 

Each decision has our recommendation. Please agree with it or choose another option.

| Decision | Recommended | Your decision |
| :---- | :---- | :---- |
| How do we track what each organizer is owed? | A settlement report per organizer and event (sales, refunds, fees, amount owed). No manual payment are accepted |  |
| Does the platform charge a fee, and who pays it? |  |  |
| How do we check organizers before they sell? | Verify business or ID details and payout account before an organizer's first paid event goes live. Also confirm with Pesapal that one account can collect for many organizers |  |
| How are "At the Gate" tickets sold? | Buyers pay on their own phones through a QR code poster at the gate that links to the event page; no cash |  |
| What happens when an event is cancelled or postponed? | **Cancelled:** all tickets cancelled, refunds in bulk, every buyer emailed. **Postponed:** tickets stay valid, buyers emailed and offered a refund for a set period |  |
| Is re-entry allowed? | No, for the first release |  |
| How do free and sponsor tickets work? | Orders that total zero skip payment and issue tickets directly; each delegation gets a private link or code to claim its tickets |  |
| Which messages do buyers get, and is SMS included? | Emails for order confirmation, ticket delivery, event changes and cancellations. SMS only if you want it, chosen early |  |
| Receipts and tax | Email a receipt for every paid order. Should be confirmed whether VAT and KRA eTIMS invoicing apply |  |
| How long do we keep attendee data? | Your policy; currently we will remove personal details at a set time after each event, while keeping financial records |  |
| How long are tickets held while a buyer pays? | 10–15 minutes |  |
| What if a payment arrives after the hold expired? | Honor it if tickets are still available; otherwise refund it |  |
| Who approves refunds? | The organizer requests, a platform admin approves, following each event's policy |  |
| Must guards change their temporary password? | Yes, at first sign-in for each event, so only the guard knows it |  |
| Should ticket prices be whole shillings? | Yes, to avoid rounding problems with M-Pesa (we'll confirm with Pesapal) |  |
| How do we protect popular sales from bots? | Add a CAPTCHA at checkout for high-demand events.  |  |
| Ticket transfers or name changes | Not in the first release |  |
| Can organizers download attendee and sales lists? | Yes, as spreadsheet (CSV) files |  |
| Can buyers browse events by type (music, sport and so on)? | Your choice; simple to add |  |
| Is a Swahili version of the public site needed? | Not for the first release |  |

