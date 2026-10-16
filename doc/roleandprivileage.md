# Roles, Privileges & Authorization Overview

**Multi-Organizer Event Ticketing Platform · Version 1.4 · 16 October 2026**  
**References:** [limitation.md](../limitation.md) (§1, §2, §11), [v1.md](../v1.md) (§FR-01, §FR-02, §FR-04, §FR-13, §FR-14, §FR-15, §3, §4.1), [techstack.md](../techstack.md) (§3), [implementation-plan.md](../implementation-plan.md) (Phase 1, Phase 2, §11 #29–#30). If this document disagrees with v1.md, v1.md wins.

---

## 1. Core Operating Principles

The ticketing platform operates as a shared system supporting multiple event organizers simultaneously while ensuring total privacy and data separation. The access and security framework is built around five core operational principles:

1. **Platform Administration & Oversight:** System Administrators hold overall operational authority, managing event organizer accounts, supervising platform activity, resolving cross-organizer issues, executing refunds, and reviewing overall business reporting.
2. **Organizer Self-Management:** Organizer Teams independently manage their own organization profile, create and manage events, set pricing and ticket inventories, create gate control accounts, and view financial settlement statements.
3. **Restricted Event Host Access:** External event owners or promoters are granted access strictly to monitor guest activity for their assigned events. They can view, filter, and export attendee lists, but cannot modify event details, ticket prices, or financial settings.
4. **Tenant Privacy & Data Isolation:** Strict boundaries ensure that no event organizer or team member can view, access, or modify another organizer's events, sales figures, guest lists, or financial payouts.
5. **Fail-Safe Security Default:** Access rights are continuously verified on every system action based on the logged-in user’s current role and verified organization permissions. Any unauthorized request is automatically blocked by default.

---

## 2. Summary of System Roles

```mermaid
graph TD
    classDef admin fill:#f9f,stroke:#333,stroke-width:2px;
    classDef staff fill:#bbf,stroke:#333,stroke-width:2px;
    classDef eventOwner fill:#dfd,stroke:#333,stroke-width:2px;
    classDef guard fill:#ffd,stroke:#333,stroke-width:2px;
    classDef guest fill:#eee,stroke:#333,stroke-width:2px;

    PA[Platform Admin]:::admin
    OS[Organizer Staff]:::staff
    EO[Event Owner]:::eventOwner
    GD[Guard]:::guard
    BY[Buyer / Attendee]:::guest

    PA -->|Full System Authority| OS
    PA -->|Platform Oversight| EO
    OS -->|Assigns Guest Access| EO
    OS -->|Creates & Assigns| GD
    BY -->|Purchases & Redeems| GD
```

### 2.1 Platform Admin
* **Description:** Executive platform managers who oversee all platform operations, organizer onboarding, compliance, and overall business performance.
* **Scope:** Full, platform-wide administrative oversight.
* **Log In Method:** Google Sign-In with a Google-verified email address, after accepting a platform admin invitation sent to that address.
* **Key Capabilities:**
  * Approve, verify, or suspend event organizer accounts.
  * View platform-wide sales summary reports, financial transactions, and system activity logs.
  * Authorize ticket cancellations, event cancellations, and customer refunds.
  * Record manual financial settlements and payout records for event organizers.
  * Invite new Platform Admins by email, and remove another admin's rights (never the last admin's). Any admin can do both, and both are audited.
  * The very first admin is created once, on a fresh installation, with a setup command (`php artisan platform:bootstrap-admin {email}`) that creates that admin's invitation and refuses to run once any admin exists.
* **Restrictions:** Admins do not scan tickets at event gates during operational entry (gate checking is handled strictly by Guard accounts).

### 2.2 Organizer Staff
* **Description:** Operational managers for an event management company or organizer entity.
* **Scope:** Restricted strictly to their own organization's profile, events, sales data, staff accounts, and financial summaries.
* **Log In Method:** Google Sign-In.
* **Key Capabilities:**
  * Manage organization contact profile, public branding, and bank payout details.
  * Create, update, publish, or cancel events, ticket categories, and pricing schedules.
  * Issue complimentary, VIP, sponsor, or promotional tickets.
  * Invite team members, assign Event Owners, and manage entry Guard credentials.
  * View organizational sales reports, attendance figures, and financial payout reports.
* **Restrictions:** Cannot initiate customer refunds (handled by Platform Admins) and cannot scan tickets directly using standard staff logins (must use an assigned guard account to work a gate).

### 2.3 Event Owner
* **Description:** External event hosts, client representatives, or guest artists who require real-time visibility into attendance for specific assigned events.
* **Scope:** Strictly limited to guest data for their explicitly assigned event(s).
* **Log In Method:** Google Sign-In (via email invitation from Organizer Staff or Platform Admin).
* **Key Capabilities:**
  * **View Guest Information:** See each ticket holder's name, ticket category, delegation, check-in status and entry time for assigned events.
  * **Filter Guest Data:** Search by name, and filter by ticket category, delegation, purchase date or check-in status.
  * **Export Guest Data:** Download guest rosters as spreadsheet (CSV) files.
* **Restrictions:** Read-only guest access. Cannot edit event settings, ticket quantities, pricing, guard assignments, or view revenue figures and bank details.

### 2.4 Guard
* **Description:** On-site venue personnel working entry turnstiles, gates, or doors to scan tickets or handle entry checks.
* **Scope:** Restricted solely to scanning functions for assigned gates during an active event.
* **Log In Method:** Guard authentication is an isolated, temporary, single-event credential system: an email and password account created by Organizer Staff (or a Platform Admin), signed in through Laravel Sanctum SPA cookie sessions, never Google OAuth.
* **Key Capabilities:**
  * Scan ticket QR codes at designated gates.
  * Manually enter short verification codes for entry fallback.
  * View real-time gate entry counts for active shifts.
* **Restrictions:** No access to administrative tools, sales figures, financial data, or guest lists. A scan result shows only what the guard needs to check entry: the attendee's name (where collected), ticket category, seat or delegation, and the last 4 digits of their phone or ID number when the event collects one. Guard credentials work for one event only and automatically deactivate (password hash wiped and sessions terminated) when the event concludes.

### 2.5 Buyer / Attendee
* **Description:** Customers purchasing ticket offerings or claiming event passes online.
* **Scope:** Limited exclusively to their own ticket purchases and order receipts.
* **Log In Method:** Guest checkout (no account password creation needed).
* **Key Capabilities & Access:** Access passes via secure digital ticket links (`/t/{access_token}`) delivered to their email, plus a checkout token during checkout. Ticket holders can view their pass, present entry codes, or transfer eligible passes to friends prior to event start (unless the event collects national ID numbers).

---

## 3. Key Operations & Permissions Matrix

### Access Level Definitions:
* **Full:** Complete management authority within scope.
* **Scoped:** Permitted for specific assigned events or gates.
* **Limited:** Restricted strictly to viewing or exporting guest data.
* **No:** Access not allowed (`403 Forbidden`).

| Operational Area | Capability | Platform Admin | Organizer Staff | Event Owner | Guard | Buyer / Guest |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| **Platform Management** | View platform audit logs | Full | No | No | No | No |
| | View event audit records | Full | Limited (Own events, read-only) | No | No | No |
| | Verify organizers & categories | Full | No | No | No | No |
| | Invite or remove Platform Admins | Full | No | No | No | No |
| | Approve or suspend organizers | Full | No | No | No | No |
| | View platform financial totals | Full | No | No | No | No |
| **Account & Staffing** | Edit profile & payout bank details | Full | Full (Own org) | No | No | No |
| | Invite & manage Organizer Staff | Full | Full (Own org) | No | No | No |
| | Assign Event Owners to events | Full | Full (Own org) | No | No | No |
| **Event Management** | Create, edit, and publish events | Full | Full (Own org) | No | No | No |
| | Cancel or archive events | Full | Full (Own org) | No | No | No |
| | Configure ticket types & pricing | Full | Full (Own org) | No | No | No |
| | Configure reserved seating layouts | Full | Full (Own org) | No | No | No |
| | Manage partner & sponsor allocations | Full | Full (Own org) | No | No | No |
| | Issue complimentary / VIP tickets | Full | Full (Own org) | No | No | No |
| **Guest Data** | View attendee rosters | Full | Full (Own events) | Limited (Names, status & entry time, assigned event) | No | Own Order Only |
| | Filter attendee lists | Full | Full (Own events) | Limited (Names, status & entry time, assigned event) | No | No |
| | Export guest lists (CSV) | Full | Full (Own events) | Limited (Names, status & entry time, assigned event) | No | No |
| **Customer Orders** | Revoke or cancel tickets | Full | Full (Own events) | No | No | No |
| | Authorize and execute refunds | Full | No | No | No | Request after a postponement |
| | Transfer ticket to another person | No | No | No | No | Own tickets, before the event starts |
| **Scanning & Entry** | Create guard entry credentials | Full | Full (Own org) | No | No | No |
| | Assign guards to specific gates | Full | Full (Own events) | No | No | No |
| | Scan tickets & check in guests | No | No | No | Scoped (Assigned gates) | No |
| | Manual entry code verification | No | No | No | Scoped (Assigned gates) | No |
| | View real-time gate entry counts | Full | Full (Own events) | No | Scoped (Assigned gate) | No |
| **Financials** | View settlement statements | Full | Full (Own organizer) | No | No | View own receipt |
| | Record manual payout details | Full | No | No | No | No |

---

## 4. Operational Workflows & How Access Works

### 4.1 Step-by-Step Access Verification Flow

1. **User Request:** A staff member, event owner, or guard performs an action in the system (e.g., viewing an attendee roster or clicking to export guest data).
2. **Identity Verification:** The system confirms the user is logged in with valid active credentials (a Sanctum SPA cookie session, for Google users and guard password accounts alike).
3. **Role & Ownership Check:** The system checks the user's assigned role and verifies whether they belong to the organization hosting that specific event or are explicitly assigned to it.
4. **Permission Decision:**
   * **Platform Admin:** Request approved automatically across all operational resources.
   * **Organizer Staff:** Request approved if the event belongs to their active organizer organization.
   * **Event Owner:** Request approved only if the user is assigned directly to that specific event and the request is read-only guest management.
   * **Guard:** Request approved only for scanning and gate check functions at their assigned gate.
5. **Execution or Block:** Authorized requests display the relevant data or complete the task. Unauthorized requests are immediately blocked with an access denied message (`401 Unauthorized` or `403 Forbidden`).

### 4.2 Enforcement Sequence Diagram

```mermaid
sequenceDiagram
    autonumber
    actor Client as User (Staff / Event Owner / Admin)
    participant HTTP as Routing & Middleware Layer
    participant Sanctum as Auth Middleware (Sanctum)
    participant Policy as Laravel Policy Layer
    participant DB as Supabase PostgreSQL

    Client->>HTTP: Request (e.g. GET /api/v1/organizer/events/123/attendees/export)
    HTTP->>Sanctum: Authenticate session cookie
    alt Unauthenticated
        Sanctum-->>Client: 401 Unauthorized
    end

    Sanctum->>HTTP: User identity verified (User model)
    HTTP->>Policy: Authorize action (AttendeePolicy::export)

    critical Role & Scope Validation
        alt Platform Admin
            Policy-->>HTTP: Approved (Full Rights)
        else Organizer Staff
            Policy->>DB: Check user is active staff for event organizer
            alt Is Active Staff
                Policy-->>HTTP: Approved (Full Tenant Scope)
            else Not Staff
                Policy-->>Client: 403 Forbidden
            end
        else Event Owner
            Policy->>DB: Check event_staff for event_id + role == 'event_owner'
            alt Is Assigned Event Owner
                Policy-->>HTTP: Approved (Scoped to Assigned Event only)
            else Not Assigned to Event
                Policy-->>Client: 403 Forbidden
            end
        else Any Other Role
            Policy-->>Client: 403 Forbidden
        end
    end

    HTTP->>DB: Execute query filtered by event_id
    HTTP-->>Client: 200 OK (Stream CSV or Return JSON)
```

### 4.3 Laravel Policy Implementation Patterns

#### Event Management Policy (`EventPolicy.php`)
```php
namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Platform Admin has full rights over all resources.
     */
    public function before(User $user, $ability): ?bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Only Organizer Staff can update event configuration.
     * Event Owners CANNOT update events.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->organizerMemberships()
            ->where('organizer_id', $event->organizer_id)
            ->where('role', 'staff')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Delete/archive events - Organizer Staff only.
     */
    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
```

#### Attendee & Guest Data Policy (`AttendeePolicy.php`)
```php
namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendeePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability): ?bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view, filter, or export guest data.
     * Allowed for:
     * 1. Organizer Staff belonging to the event's organizer.
     * 2. Event Owners explicitly assigned to this specific event.
     */
    public function viewGuestData(User $user, Event $event): bool
    {
        // 1. Check if user is Organizer Staff for this event
        $isStaff = $user->organizerMemberships()
            ->where('organizer_id', $event->organizer_id)
            ->where('role', 'staff')
            ->where('status', 'active')
            ->exists();

        if ($isStaff) {
            return true;
        }

        // 2. Check if user is an assigned Event Owner for this specific event
        return $user->staffAssignments()
            ->where('event_id', $event->id)
            ->where('role', 'event_owner')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Export guest data as CSV.
     */
    public function exportGuestData(User $user, Event $event): bool
    {
        return $this->viewGuestData($user, $event);
    }
}
```

---

## 5. Key Operational Safeguards & Security Rules

### 5.1 Strict Boundaries for Event Owners

#### 5.1.1 Event-Specific Access Authorization
* **Explicit Assignment Required:** Event Owners gain access to event data strictly through explicit assignment by authorized Organizer Staff or Platform Admins.
* **Strict Event Isolation:** Access is confined solely to explicitly assigned events. Event Owners cannot view or discover other events hosted by the same organizer.

#### 5.1.2 Permitted Guest Information
* **Visible Fields:** Event Owners can view each ticket holder's full name, ticket category, delegation, check-in status (checked in or not yet) and entry time (when the ticket was scanned in). Nothing else: no ticket type, contact details, ID numbers, prices or order amounts.
* **Roster Operations:** Permitted actions are limited strictly to searching, filtering, and exporting attendee rosters as CSV files.

#### 5.1.3 Prohibited Operational and Administrative Access
* **Financial Data Shielding:** Revenue figures, payout summaries, ticket prices, and settlement details are completely hidden from Event Owners.
* **Operational Restrictions:** Event Owners cannot modify ticket capacities, adjust pricing, create discount codes, manage guard credentials, or trigger ticket cancellations and refunds.

#### 5.1.4 Read-Only Enforcement
* **UI and API Blocking:** Administrative options are hidden in the interface, and write API attempts are rejected with explicit permission errors (`403 Forbidden`).
* **Scanning Restrictions:** Event Owner credentials cannot be used to perform ticket entry checks or operate gate turnstiles.

#### 5.1.5 Assignment Management and Revocation
* **Dynamic Revocation:** Access permissions terminate immediately when an Event Owner is removed from an event or when the assigned event is archived.
* **Organizer Scope Control:** Disabling or suspending an organizer account automatically revokes access for all linked Event Owners.

#### 5.1.6 Auditability and Data Protection
* **Audit Trail Logging:** All view, filter, and export requests initiated by Event Owners are logged with timestamps and user IDs for compliance monitoring.
* **Privacy Compliance:** Personal guest details are rendered strictly for necessary event coordination, upholding platform tenant isolation policies.

#### Expected Outcome
Event Owners gain necessary real-time attendee visibility to coordinate event hospitality while maintaining comprehensive isolation from financial, administrative, and cross-event data.

### 5.2 Multi-Tenant Isolation & Guard Safeguards

1. **Guard Password Account Containment:**
   * Password accounts belong strictly to the creating organizer (`managed_by_organizer_id`) and work for **one event only**.
   * A guard account can never be assigned as Organizer Staff, Event Owner, or Platform Admin.
   * Temporary passwords are shown once upon creation and must be changed on first login before scanning endpoints unlock.
   * **Automatic Lifecycle Wipe:** When the event closes, is cancelled, or is archived, the guard password hash is wiped, active sessions are invalidated, and the guard is signed out immediately.
   * Lockout limits: 5 consecutive failed passwords lock the account; 5 invalid short codes lock code-entry fallback.
2. **Instant Session Revocation & Suspension Cascading:**
   * Suspending an organizer account instantly terminates sessions for all linked Organizer Staff and assigned Event Owners/Guards.
   * Suspending a user immediately deletes all matching rows in the `sessions` table.
3. **Fail-Closed Default & No Role Escalation:**
   * The backend validates ownership and role assignments from the database on every authenticated request.
   * Event Owners have zero ability to invite users, change roles, or grant permissions.
   * Platform admin rights come only from accepting a platform admin invitation sent by an existing admin, or, for the very first admin, from the one-time setup command. The last admin can't be removed.

---

## 6. Database Schema Supporting RBAC

```sql
-- Users Table
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    sign_in_method VARCHAR(20) NOT NULL DEFAULT 'google', -- 'google', 'password'
    google_id VARCHAR(255) UNIQUE,
    managed_by_organizer_id UUID REFERENCES organizers(id) ON DELETE CASCADE,
    platform_role VARCHAR(20) DEFAULT NULL, -- 'admin' or NULL
    status VARCHAR(20) NOT NULL DEFAULT 'active', -- 'active', 'suspended'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    CONSTRAINT uq_user_email_method UNIQUE (email, sign_in_method)
);

-- Organizers (Tenants)
CREATE TABLE organizers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'active', 'suspended'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Organizer Memberships (Tenant Staff with full operational rights)
CREATE TABLE organizer_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organizer_id UUID NOT NULL REFERENCES organizers(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL DEFAULT 'staff', -- 'staff'
    status VARCHAR(20) NOT NULL DEFAULT 'active', -- 'active', 'suspended'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    CONSTRAINT uq_organizer_user UNIQUE (organizer_id, user_id)
);

-- Event Staff & Assignments (Guards and Event Owners)
CREATE TABLE event_staff (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    event_id UUID NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    gate_id UUID REFERENCES event_gates(id) ON DELETE SET NULL, -- Nullable for Event Owners
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL, -- 'guard', 'event_owner'
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    CONSTRAINT uq_event_user_role UNIQUE (event_id, user_id, role)
);

-- Guard Credentials (Single-Event Passwords)
CREATE TABLE guard_credentials (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_id UUID NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    password_hash VARCHAR(255) NOT NULL,
    password_change_required BOOLEAN NOT NULL DEFAULT true,
    issued_by UUID NOT NULL REFERENCES users(id),
    issued_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    revoked_at TIMESTAMP WITH TIME ZONE DEFAULT NULL
);

-- System Audit Logs
CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    actor_user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    organizer_id UUID REFERENCES organizers(id) ON DELETE SET NULL,
    event_id UUID REFERENCES events(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(100) NOT NULL,
    resource_id VARCHAR(255) NOT NULL,
    metadata JSONB DEFAULT '{}',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```
