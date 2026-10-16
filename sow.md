# STATEMENT OF WORK (SOW)
## Event Ticketing, Management and Access Control Platform

**Document Version:** 1.9  
**Date:** 16 October 2026  
**Project Name:** Multi-Organizer Event Ticketing Platform  
**Client:** [Client / Organization Name]  
**Service Provider:** [Developer / Software Development Company]  
**Project Start Date:** [To Be Agreed]  
**Target Completion Date:** [To Be Agreed]  
**Document Status:** Draft for Review and Approval

---

## 1. PROJECT OVERVIEW

### 1.1 Background

The Client intends to develop a centralized digital event ticketing platform that enables multiple event organizers to create, manage, promote and sell tickets for their events through a shared system.

The platform will provide a public-facing event discovery and ticket purchasing interface, an organizer dashboard for event and sales management, a platform administration interface, and a dedicated gate management interface for authorized security personnel.

The system will integrate M-Pesa and card payments, generate unique QR-coded digital tickets, facilitate secure ticket retrieval through email, and validate tickets at event entrances to prevent duplicate or unauthorized entry.

The platform will be developed with a modular architecture that supports future expansion while maintaining security, data integrity, usability and operational reliability.

### 1.2 Project Purpose

The purpose of this engagement is to design, develop, test and deploy a functional event ticketing and access control platform that digitizes the event lifecycle, from event creation and ticket sales to payment confirmation, ticket issuance and entry validation.

### 1.3 Project Objectives

The project aims to:

1. Provide a centralized marketplace for events managed by multiple organizers.
2. Enable organizers to create events and configure ticket types, categories, prices, capacity, sales periods and ticket waves.
3. Facilitate secure ticket purchases through M-Pesa and card payments.
4. Automate ticket generation, email delivery and ticket retrieval.
5. Implement QR-code scanning and single-use ticket redemption at event gates.
6. Provide role-based dashboards for platform administrators, event organizers and guards.
7. Support promotional campaigns, discount codes, group tickets and optional reserved seating.
8. Provide event sales, payment and attendance reports.
9. Establish a maintainable technical foundation for future platform expansion.

---

## 2. SCOPE OF WORK

The Service Provider shall undertake the design, development, testing and deployment of the software components described below.

### 2.1 Module One: Authentication and User Management

The system shall provide secure authentication and role-based access control for platform users.

Functional requirements include:

- Sign-in with Google accounts (Google OAuth) for platform administrators, organizer staff and event owners.
- Guard sign-in only with an individual email and password account, created with the guard's own email address by organizer staff or a platform administrator. The guard shall set their own password at first sign-in, and passwords shall be stored only as secure hashes. Each guard password shall be valid only for the event it was issued for, and shall be revoked and deleted as soon as that event closes (when entry ends, or when it is cancelled or archived). Only guard accounts shall scan tickets.
- User profile management.
- Account activation and suspension.
- Organizer account applications, approval and management, including verification of business or ID details and the payout account before an organizer's first paid event. Only platform administrators enter, change or view an organizer's payout account; organizer staff see only whether a verified one is on file. Until a platform administrator has entered it, the organizer's events with paid tickets stay in draft and can't be published.
- Organizer membership through the organizer staff role. Staff manage the organizer account and create events. A staff member manages an event, with its sales, orders, guest data and reports, only with access to that event: automatically for the events they create, and otherwise only when a platform administrator grants it. For their organizer's other events, staff see a read-only summary.
- Member visibility: only platform administrators see the list of all users; staff see their own organizer's staff; event owners see the staff who manage their assigned events.
- Event owners assigned to specific events, with access limited to viewing, filtering and exporting that event's guest data.
- Staff and event owner invitations by email, accepted by signing in with a Google account that uses the invited email address, and guard password accounts created directly by organizer staff.
- Platform administrator invitations by email, sent by an existing platform administrator and accepted the same way. Any platform administrator can remove another's rights, but never the last administrator's. The very first administrator is set up once with a setup command that stops working as soon as an administrator exists.
- Event-specific and gate-specific guard assignments, and event-specific event owner assignments.
- Enforcement of access restrictions through the backend API.

The platform shall support the following primary roles:

- Platform Administrator (full rights across the platform)
- Organizer Staff (their organizer account, and the events they manage)
- Event Owner (view, filter and export guest data for assigned events only)
- Guard / Gate Officer
- Attendee / Ticket Purchaser

Attendees may purchase tickets as guests, without signing in. Mandatory attendee accounts are not required for the initial release unless subsequently agreed.

### 2.2 Module Two: Event Management

Authorized organizers shall be able to create and manage multiple events through a dedicated dashboard.

Each event shall support:

- Event name, description, images, venue information and an event category from a list maintained by platform administrators.
- Event location and date/time configuration.
- Explicit event timezone.
- Ticket sales start and end dates.
- Gate opening and closing times.
- Overall event capacity.
- Ticket inventory and allocation management.
- Ticket types and categories.
- Optional ticket waves for each ticket category.
- Delegation management.
- A public link and QR code for each event, downloadable as an image or as a printable PDF poster, for sharing and for selling at the gate.
- Optional seating configuration.
- Guard and gate assignments.
- Event publication, cancellation and archiving.
- Event-specific attendee information requirements, including an identity field (none, phone number or ID number) chosen by organizer staff, and extra questions limited to pick-lists, checkboxes and dates.
- An optional CAPTCHA at checkout for high-demand events.
- Handling of cancelled events (ticket revocation, buyer emails and administrator refunds) and postponed events (buyer refund requests).

The system shall distinguish between ticket sales deadlines, event start/end times and gate operating times.

### 2.3 Module Three: Ticket Types, Categories and Delegations

The platform shall support configurable ticket types, including:

- Early Bird
- Normal
- At the Gate
- Group Tickets
- Promoted Tickets

It shall also support organizer-defined ticket categories, initially including:

- VVIP
- VIP
- Media

Organizers shall be able to configure additional ticket types and categories according to their event requirements.

Ticket offerings shall support configurable prices, availability periods, allocations, quantity limits and eligibility rules.

Organizers shall be able to sell any ticket category in waves. Each wave shall have its own start and end dates, and for each wave the organizer shall choose the ticket type, the number of tickets and the price. A category's waves shall not overlap. Each wave shall open on its start date only, and unsold tickets shall move to a later wave only when organizer staff move them.

Delegations shall enable organizers to set aside tickets for groups, sponsors, departments, invited guests or other designated groups, as the delegation's own allocation, which is not sold to the public, does not follow the ticket waves, and is claimed through a private link or access code. Organizer staff shall be able to return unclaimed tickets to public sale. Orders that total zero shall skip payment and issue tickets directly.

The system shall enforce ticket inventory limits and prevent overselling through transaction-safe inventory management.

### 2.4 Module Four: Ticket Purchasing and Order Management

The public website shall allow attendees to browse available events and purchase tickets. Its landing page shall list every public ticket option across published events, with search by event name or venue and filters by event category and date. Sold-out options shall stay listed and be marked as sold out, and upcoming waves shall be shown with their start dates and prices. Delegation tickets shall not be listed.

The purchasing process shall include:

1. Event or ticket option selection, from the landing page or the event page.
2. Ticket type and category selection.
3. Ticket quantity selection.
4. Seat selection where applicable.
5. Collection of purchaser and attendee details.
6. Application of eligible promo or coupon codes.
7. Order summary and pricing calculation.
8. Payment method selection.
9. Payment initiation and confirmation.
10. Ticket issuance following verified successful payment.

Orders shall support multiple tickets, with each issued ticket having an independent ticket identifier and QR credential. Ticket holders shall be able to transfer a ticket to another person until the event starts, except for events that collect ID numbers.

The system shall maintain order records, pricing snapshots, payment references, order status and associated ticket records.

### 2.5 Module Five: Payment Gateway Integration

The platform shall integrate Pesapal (API 3.0) for M-Pesa and card payments through Pesapal's hosted checkout.

The implementation shall include payment initiation, gateway response handling, server-side payment verification, webhook processing, transaction references and payment status tracking.

The system shall support appropriate handling of pending, successful, failed, cancelled, expired and refunded transactions.

Additional requirements include:

- Duplicate webhook protection.
- Idempotent payment processing.
- Payment reconciliation records.
- Secure handling of payment credentials.
- Refund initiation by platform administrators, and automatic refunds of payments that can't be accepted, with status tracking where supported by Pesapal.
- An emailed receipt for every paid order.
- A settlement report per organizer and event, and a log of manual payouts recorded by platform administrators.
- No storage of the paying account (M-Pesa number or card number) that Pesapal reports.

The Client shall provide or facilitate the required payment provider accounts, credentials, merchant registrations and approvals.

Payment processing shall remain subject to the capabilities, policies, availability and approval processes of the selected providers.

### 2.6 Module Six: Digital Ticket Generation and Retrieval

Following confirmed payment, the system shall generate digital tickets containing unique ticket references and QR codes.

The system shall provide:

- Unique ticket generation for each ticket purchased.
- Secure QR-code generation.
- Email delivery of order confirmations with ticket retrieval links, receipts, and notices of event changes and cancellations. SMS messaging is not included.
- A secure ticket retrieval page.
- Ticket display and download functionality.
- A 6-character short code on every ticket, unique within its event, for entry when the QR code can't be scanned.
- Display of event information, ticket category, ticket number, short code, attendee details where collected, and seat details where applicable.
- Ticket revocation controls for authorized users.
- Secure access-token management.

QR codes shall reference secure ticket identifiers and shall not contain any personal information.

Ticket retrieval links shall use secure tokens rather than predictable database identifiers.

### 2.7 Module Seven: Gate Management and QR Validation

The platform shall provide a dedicated interface for authorized guards.

Guard functionality shall include:

- Secure sign-in with a guard email and password account.
- Scanning limited to tickets for the events and gates the event organizer has assigned to the guard.
- Viewing assigned events and gates.
- QR-code scanning using supported mobile devices.
- Manual entry of the ticket's 6-character short code when a QR code can't be scanned, with the same validation and single-use redemption, rate-limited per guard.
- Online ticket validation through the backend.
- Verification of ticket status and event association.
- Confirmation of successful entry.
- Rejection of invalid, revoked, unpaid, expired or previously redeemed tickets.
- Display of authorized attendee details.
- Recording of scan attempts, timestamps, gate identifiers and guard identity.

Ticket redemption shall be processed atomically to prevent duplicate admission when multiple guards scan the same ticket concurrently.

**Initial release limitation:** Gate scanning shall require an active internet connection. Offline ticket validation is excluded from the initial scope.

### 2.8 Module Eight: Promo Codes and Coupons

The system shall support configurable promotional campaigns.

Features shall include:

- Percentage-based and fixed-value discounts.
- Promotion start and expiry dates.
- Eligible ticket types and categories.
- Usage limits.
- Per-buyer limits where applicable.
- Minimum purchase requirements.
- Event-specific restrictions.
- Redemption tracking and reporting.

Promo codes and coupons may use a shared discount engine, with distinct rules configured where required.

### 2.9 Module Nine: Optional Reserved Seating

For events requiring reserved seating, organizers shall be able to configure seating sections, rows and seat numbers.

The system shall support:

- Seat identification and availability.
- Seat selection during checkout.
- Temporary seat reservations.
- Expiry of abandoned seat reservations.
- Prevention of duplicate seat allocation.
- Association of purchased seats with individual tickets.
- Display of assigned seats on digital tickets.

Reserved seating shall be optional and shall not be required for general-admission events.

### 2.10 Module Ten: Dashboards and Reporting

**Platform Administrator Dashboard**

The administrator interface shall provide authorized platform-wide management capabilities, including:

- Organizer and user management, including inviting and removing platform administrators, creating events for an organizer, and granting organizer staff access to events.
- Event oversight.
- Account suspension and activation.
- Platform-level transaction oversight.
- Audit log access.
- Organizer verification, organizers' payout accounts and the event category list.
- Refunds, settlement reports and the manual payout log.
- Deletion of a person's personal data on request.
- Operational and summary reports.

**Organizer Dashboard**

Organizers shall be able to access the following for the events they manage, with a read-only summary of their organizer's other events:

- Event creation and management.
- Ticket pricing and inventory, including ticket waves and delegations' set-aside tickets.
- Each event's link, QR code and printable poster.
- Order and sales reports.
- Payment status and reconciliation information for their events.
- Promotion management.
- Guard and gate assignments.
- Event owner assignments.
- Guest lists with filters and CSV export, which event owners can also access for their assigned events (limited to each ticket holder's name, ticket category, delegation, check-in status and entry time).
- Sales list CSV export and the audit records of the events they manage.
- Attendance reports.
- Ticket revocation controls.
- Event-specific operational summaries.

**Guard Interface**

The guard interface shall be limited to assigned events, gates, scanning operations and relevant entry activity.

**Attendee Interface**

Attendees shall be able to browse ticket options on the landing page and event pages, purchase tickets, retrieve digital tickets and access their own purchase information through the supported ticket retrieval mechanism.

---

## 3. TECHNICAL DELIVERABLES

The Service Provider shall deliver the following project artifacts and software components.

| Deliverable | Description |
|---|---|
| D1. Requirements specification | Approved functional requirements, assumptions and acceptance criteria |
| D2. UI/UX designs | Designs for public, organizer, administrator and guard interfaces |
| D3. Database design | Entity relationship diagram, database schema and migration scripts |
| D4. Backend application | API, authentication, authorization and business logic |
| D5. Public event website | Event discovery, checkout and ticket retrieval interfaces |
| D6. Administration dashboard | Platform administration and oversight tools |
| D7. Organizer dashboard | Event, ticket, inventory, staff and reporting tools |
| D8. Guard interface | Online QR scanning and ticket validation |
| D9. Payment integration | M-Pesa and card payment integration with test evidence |
| D10. Ticketing engine | Ticket generation, QR codes, secure retrieval and delivery |
| D11. Testing documentation | Test cases, results and defect resolution records |
| D12. Deployment | Deployment to the agreed production environment |
| D13. Documentation | Setup instructions, API documentation and operational guidance |
| D14. Handover | Source code, configuration guidance and agreed access credentials |

D5 to D8 are delivered as areas of one web application. The specific frameworks, hosting services, email provider, database technology and payment gateway providers shall be documented in the approved technical design.

---

## 4. PROJECT METHODOLOGY AND IMPLEMENTATION PHASES

The project shall be delivered incrementally, with reviews and approvals at agreed milestones.

| Phase | Activities | Weeks | Expected deliverable |
|---|---|---|---|
| 1. Discovery and specification | Confirm requirements, business rules and architecture | 1 | Approved SOW and requirements |
| 2. Design and foundation | UI/UX, database design, authentication and tenant isolation | 2–3 | Approved designs and working foundation |
| 3. Event management | Events, ticket offerings, pricing and inventory | 4–5 | Functional event management |
| 4. Checkout and payments | Orders, discounts, M-Pesa and card integrations | 5–8 | Functional payment workflow |
| 5. Ticket issuance | QR generation, retrieval and email delivery | 8–9 | Functional digital ticketing |
| 6. Gate access | Guard assignments, scanning and redemption | 9–10 | Functional access control |
| 7. Seating, promotions and reporting | Optional reserved seating, remaining promotion rules, organizer and administrator reports | 10–11 | Functional seating, promotions and reporting |
| 8. Testing and deployment | Integration testing, security checks, deployment and handover | 11–12 | Production-ready agreed scope |

Phases overlap by about a week because design, backend and frontend work run in parallel. The project implementation plan sets out the detailed steps and exit criteria for each phase.

### 4.1 Indicative Timeline

The estimated delivery window is **12 weeks** from project start, subject to final scope confirmation, timely Client feedback, payment provider onboarding and third-party integration dependencies. A shorter schedule is not realistic for the full scope in Section 2, which includes two payment methods, four user interfaces (public, organizer, administrator and guard), optional reserved seating, promotions and reporting.

If the schedule comes under pressure, the parties may agree through change control (Section 12) to move Module Nine (optional reserved seating) to a release after launch. The core purchase-to-entry workflow does not depend on it. The seating acceptance criterion in Section 5.2 would move with the module.

The features added on 15 October 2026 (ticket waves, the landing page's ticket listing with search and filters, event links with QR codes and printable posters, set-aside delegation tickets, and data deletion by platform administrators) add work to phases 3, 4 and 7. Their effect on the 12-week estimate shall be confirmed when the schedule is baselined.

The schedule shall be baselined after requirements approval, taking account of public holidays within the delivery window. Any delays arising from external approvals, late delivery of required assets or significant scope changes may affect the target completion date.

### 4.2 Milestone Approval

Each milestone shall be submitted to the Client for review. The Client shall provide consolidated feedback or approval within five business days, unless another period is agreed in writing.

Defects against approved requirements shall be distinguished from requests for new functionality. New functionality shall follow the change-control process in this SOW.

---

## 5. ACCEPTANCE CRITERIA

The project shall be considered functionally complete when the following criteria have been demonstrated in the agreed test environment.

### 5.1 Identity and Access

- Authorized users can sign in, with Google or (guards only) with a guard password account, and access their assigned interfaces.
- Guard password accounts can reach only the guard interface, for their assigned event and gates, and their passwords stop working as soon as that event closes.
- Organizers cannot access another organizer's private resources.
- Guards can access only assigned events and permitted gates.
- Event owners can view, filter and export the guest list only for their assigned events, and every other organizer action rejects them.
- Only an existing platform administrator can make someone an administrator, by invitation, and the last administrator can't be removed.
- Organizer staff can change an event, or see its sales, orders and guest data, only for the events they created or that a platform administrator gave them access to; other events of their organizer are read-only summaries.
- Only platform administrators can see the list of all users. Staff see only their own organizer's staff, and event owners only the staff who manage their assigned events.
- Only platform administrators can enter, change or view an organizer's payout account.
- Unauthorized actions are rejected by the backend.

### 5.2 Event and Ticket Management

- Organizers can create and publish events. Events with paid tickets can't be published until a platform administrator has entered the organizer's payout account.
- Ticket types, categories, prices, deadlines and allocations are configurable.
- Ticket waves open and close on their dates, and a category's waves never overlap.
- Delegation tickets can't be bought through public checkout.
- Each event's link and QR code open its event page.
- Inventory constraints are enforced.
- Optional seating works without allowing duplicate seat assignments.

### 5.3 Checkout and Payments

- The landing page lists every public ticket option, with working search and filters.
- Buyers can complete the purchase workflow.
- M-Pesa and card payment integrations pass agreed test scenarios.
- Tickets are not issued solely because of a client-side payment success message.
- Duplicate callbacks do not create duplicate ticket issuance.
- Failed or expired transactions are handled according to the approved business rules, and payments that can't be accepted are refunded automatically.

### 5.4 Digital Tickets

- Confirmed purchases generate the expected number of tickets.
- Each ticket has a unique QR credential.
- The retrieval link provides authorized access to the relevant tickets.
- Revoked or invalid retrieval tokens are rejected.
- A transferred ticket's previous QR code and short code are rejected at the gate.

### 5.5 Gate Access

- Valid tickets can be scanned and redeemed online.
- Already-redeemed tickets are rejected.
- When a QR code can't be scanned, entering the ticket's short code admits the attendee once, and invalid codes are rejected.
- Invalid tickets and tickets belonging to other events are rejected.
- Concurrent scans do not produce multiple successful redemptions.
- Scan records include the required event, gate, guard and timestamp information.

### 5.6 Reporting and Handover

- Organizer staff can view reports limited to the events they manage.
- Administrative actions are auditable.
- A platform administrator can delete a person's personal data on request, keeping the financial and entry records.
- Agreed documentation and source code are delivered.
- No unresolved critical or high-severity defects attributable to the agreed requirements remain at final acceptance, subject to the agreed defect classification and resolution procedure.

The Client shall participate in user acceptance testing (UAT) and provide written acceptance or a documented list of defects against the agreed criteria.

---

## 6. RESPONSIBILITIES OF THE SERVICE PROVIDER

The Service Provider shall:

1. Develop the software components defined in the approved scope.
2. Follow agreed coding, security and documentation standards.
3. Implement the agreed database and API architecture.
4. Integrate the selected payment and email providers, subject to access and provider capabilities.
5. Conduct development and integration testing.
6. Resolve defects attributable to the delivered implementation during the agreed warranty period.
7. Deploy the system to the agreed environment.
8. Provide the documentation and handover items specified in this SOW.
9. Communicate progress, risks, blockers and material deviations from the approved schedule.
10. Protect Client data and credentials accessed during development.

---

## 7. RESPONSIBILITIES OF THE CLIENT

The Client shall:

1. Provide complete business requirements and timely decisions.
2. Nominate an authorized representative for approvals and project coordination.
3. Supply branding assets, event examples, content and other required materials.
4. Arrange access to required hosting, domain, email, payment provider and Google Cloud accounts (Google Cloud is used for Google sign-in).
5. Complete payment provider onboarding, merchant verification and any required approvals.
6. Provide test accounts and participate in integration testing.
7. Review designs, milestone deliverables and UAT submissions within agreed timelines.
8. Provide feedback in a consolidated form.
9. Confirm pricing policies, refund rules, ticket allocation rules and other business decisions.
10. Ensure that event and attendee data supplied to the platform is lawfully obtained and used.

Delays in these responsibilities may affect project milestones and delivery dates.

---

## 8. ASSUMPTIONS AND DEPENDENCIES

This SOW is based on the following assumptions:

- The platform will support multiple organizers on a shared application.
- The backend will be the authoritative source for access control, ticket inventory, payment confirmation and ticket redemption.
- M-Pesa and card payments will be integrated through Pesapal, which provides suitable APIs and a supported test environment.
- Email delivery will use an agreed transactional email provider.
- Gate validation will require internet connectivity in the initial release.
- Reserved seating will be an optional event configuration.
- Attendee data requirements will be configurable per event.
- Platform administrators, organizer staff and event owners have, or can create, a Google account to sign in. Guards use guard password accounts with their own email address.
- The Client will supply or approve the required branding, content and policies.
- Hosting, domains, third-party service subscriptions and transaction fees will be identified separately in the commercial agreement.
- The initial release will be developed in one agreed language and currency configuration unless additional requirements are approved.
- The technical implementation will use the approved architecture and technology stack documented during discovery.

Material changes to these assumptions may require a revised estimate or change request.

---

## 9. OUT OF SCOPE

Unless expressly included in a signed change request, the following are excluded from this engagement:

1. Native Android and iOS applications.
2. Offline QR validation and cross-device offline synchronization.
3. Hardware procurement, installation or integration beyond agreed browser-compatible scanning devices.
4. Advanced venue mapping and complex interactive seating-plan editors.
5. Automated fraud detection using machine learning or artificial intelligence.
6. External accounting, ERP, CRM or marketing automation integrations.
7. Loyalty programs, memberships and recurring subscriptions.
8. Multi-currency settlement or international tax automation.
9. Automated organizer payouts or split settlements unless specifically agreed.
10. Third-party payment gateway fees, transaction charges, hosting charges and other recurring operating costs.
11. Legal, tax or regulatory advisory services.
12. Content creation, event promotion, advertising and ongoing operational staffing.
13. SMS messaging.
14. Features not explicitly identified in the approved scope.

The parties may add any excluded capability through the formal change-control process.

---

## 10. SECURITY, PRIVACY AND DATA MANAGEMENT

The platform shall implement reasonable technical and organizational safeguards appropriate to its functionality.

These shall include:

- HTTPS for production communications.
- Google OAuth sign-in with protected session cookies. Passwords exist only for guard password accounts, stored as secure hashes, with rate-limited sign-in.
- Server-side authorization and tenant isolation.
- Input validation and protection against common web application vulnerabilities.
- Secure storage of integration credentials.
- Payment webhook authentication and verification.
- Rate limiting and abuse prevention on sensitive endpoints.
- Secure QR credentials and ticket retrieval tokens.
- Audit logs for sensitive operations.
- Database backups and an agreed recovery procedure.
- Data minimization and appropriate access restrictions for attendee information, with personal data deleted on request by a platform administrator.

The Client shall approve the applicable privacy notices, retention policies, refund policies and other operational rules.

The parties shall agree on their respective data protection responsibilities, including controller and processor roles where applicable, and ensure that applicable Kenyan data protection requirements are addressed before production launch.

---

## 11. PROJECT FEES AND PAYMENT TERMS

The total project fee, payment milestones and applicable taxes shall be agreed separately and incorporated into the signed commercial schedule.

**Total development fee:** KES [Amount to be agreed]

**Proposed payment structure:**

| Milestone | Payment |
|---|---:|
| Project commencement and requirements approval | 30% |
| Completion of core event management and checkout | 30% |
| Completion of payment integration and digital ticketing | 25% |
| UAT approval, deployment and handover | 15% |
| **Total** | **100%** |

The percentages above are a proposed commercial arrangement and are subject to mutual agreement.

Third-party subscriptions, payment processing fees, hosting, domains, email services, SMS services and other operating costs shall be itemized separately and allocated to the responsible party in the commercial agreement.

Invoices shall be payable within the period agreed by the parties. Work may be paused following written notice where a material payment obligation remains overdue, subject to the final agreement.

---

## 12. CHANGE CONTROL

Any material change to the approved scope, functionality, integrations, deliverables, timeline or technical requirements shall be documented in a change request.

Each change request shall identify:

- Description and business justification.
- Impact on deliverables and acceptance criteria.
- Additional development effort and cost.
- Impact on the project schedule.
- Any additional third-party dependencies or operating costs.

The Service Provider shall assess the request and provide an estimate. Work on the requested change shall commence only after written approval by both parties.

Clarifications and corrections required to meet the existing approved requirements shall not automatically be treated as additional scope.

---

## 13. DEFECT MANAGEMENT AND WARRANTY

The Service Provider shall provide a proposed **30-day warranty period** commencing on final acceptance or production handover, as specified in the final agreement.

During this period, the Service Provider shall correct reproducible defects that cause the delivered software to fail to meet the approved requirements, without additional development fees.

The warranty shall not automatically cover:

- New functionality or changes in business requirements.
- Problems caused by unauthorized modifications.
- Third-party service outages or provider policy changes.
- Failures caused by infrastructure or configurations outside the Service Provider's control.
- Misuse of the platform or unsupported integrations.

Third-party outages shall be handled through reasonable diagnosis and support within the agreed responsibilities. Any ongoing maintenance, hosting management, support response times or service-level agreements shall be documented separately.

---

## 14. INTELLECTUAL PROPERTY AND CONFIDENTIALITY

The parties shall agree in writing on intellectual property ownership before development begins.

Unless otherwise agreed:

- The Client shall retain ownership of its branding, supplied content and pre-existing intellectual property.
- Each party shall retain ownership of its pre-existing tools, libraries, frameworks and proprietary materials.
- Ownership and transfer of custom project source code shall be governed by the final agreement and any applicable payment conditions.
- Open-source software shall remain subject to its respective licenses.
- Third-party service accounts, keys and credentials shall be managed according to the agreed ownership and handover arrangements.
- Both parties shall protect confidential business information, credentials, personal data and non-public technical information obtained through the engagement.

The final agreement shall define source code transfer, reuse rights, licensing, confidentiality duration and any restrictions on the use of project materials.

---

## 15. DEPLOYMENT AND HANDOVER

The Service Provider shall deploy the agreed release to the approved hosting environment, subject to the Client providing necessary access and third-party approvals.

The handover shall include:

- Application source code as agreed.
- Database schema and migration scripts.
- Environment configuration documentation.
- API documentation.
- Deployment and setup instructions.
- Payment and email integration configuration guidance.
- Administrator and organizer operating guidance.
- Test results and known non-critical issues.
- Agreed account access and credential transfer arrangements.

Production credentials shall be transferred securely. The parties shall confirm who is responsible for ongoing hosting, backups, security patches, monitoring, incident response and maintenance.

---

## 16. TERMINATION AND SUSPENSION

Either party may terminate the engagement in accordance with the final signed agreement.

The agreement should define:

- Notice periods and termination grounds.
- Procedures for remedying material breaches.
- Payment for completed and accepted work.
- Treatment of work in progress and approved non-cancellable third-party costs.
- Handover of paid-for deliverables.
- Return, transfer or deletion of confidential information and personal data.
- Suspension procedures for material non-payment or other agreed grounds.

Neither party shall assume that termination automatically transfers ownership of unpaid deliverables; this shall be governed by the agreed commercial and intellectual property terms.

---

## 17. RISKS AND MITIGATION

| Risk | Mitigation |
|---|---|
| Payment provider onboarding delays | Begin provider selection and account setup early |
| Duplicate or delayed payment callbacks | Use idempotent processing and reconciliation |
| Overselling tickets | Implement transactional inventory reservations |
| Duplicate entry attempts | Enforce atomic server-side redemption |
| Cross-organizer data exposure | Enforce resource-level authorization and automated isolation tests |
| Email delivery failures | Use delivery monitoring, retries and secure ticket retrieval |
| Poor connectivity at gates | Define online-only operating procedures and a clear fail-closed scanner state |
| Late scope changes | Use documented change requests and milestone re-estimation |
| Schedule pressure on the 12-week window | Run design, backend and frontend in parallel; defer optional reserved seating through change control if needed |
| Unclear refund policies | Obtain written business rules before payment integration |
| Third-party service changes | Document dependencies and establish an incident and maintenance process |

---

## 18. APPROVAL AND SIGN-OFF

By signing below, the parties confirm that they have reviewed this Statement of Work and agree to use it as the basis for the project, subject to any final commercial agreement and mutually approved amendments.



