# Google sign-in flow

**Type:** Sequence diagram  
**Source:** [techstack.md › Google sign-in flow](../../../techstack.md#google-sign-in-flow)  
**Files:** [SVG](04-google-sign-in.svg) · [PNG](04-google-sign-in.png) · [Mermaid source](04-google-sign-in.mmd)

```mermaid
sequenceDiagram
    participant B as Browser (Next.js app)
    participant A as Laravel API
    participant G as Google

    B->>A: GET /auth/google/redirect
    A-->>B: Redirect to Google (client ID, scopes, state)
    B->>G: Sign in and approve
    G-->>B: Redirect to callback with code and state
    B->>A: GET /auth/google/callback
    A->>G: Exchange code for verified profile
    A->>A: Find or link user, check status, start session
    A-->>B: Session cookie, redirect to dashboard
    B->>A: GET /api/v1/auth/me (with cookie)
    A-->>B: User, roles and organizers
```

This is a copy of the Mermaid block in [techstack.md](../../../techstack.md#google-sign-in-flow). When it changes, update the source document, this file and `04-google-sign-in.mmd` together, then re-render ([how](../../README.md#regenerating)).
