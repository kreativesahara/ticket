# Authorization pipeline

**Type:** Sequence diagram  
**Source:** [doc/roleandprivileage.md › 4.2 Enforcement Sequence Diagram](../../../doc/roleandprivileage.md#42-enforcement-sequence-diagram)  
**Files:** [SVG](02-authorization-pipeline.svg) · [PNG](02-authorization-pipeline.png) · [Mermaid source](02-authorization-pipeline.mmd)

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
            Policy->>DB: Check event_staff for event_id + role == 'manager' (and active membership)
            alt Manages This Event
                Policy-->>HTTP: Approved (Managed Event)
            else Staff Without Event Access
                Policy-->>Client: 403 Forbidden (read-only summary only)
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

This is a copy of the Mermaid block in [doc/roleandprivileage.md](../../../doc/roleandprivileage.md#42-enforcement-sequence-diagram). When it changes, update the source document, this file and `02-authorization-pipeline.mmd` together, then re-render ([how](../../README.md#regenerating)).
