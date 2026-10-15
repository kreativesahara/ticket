# Role relationships

**Type:** Flowchart  
**Source:** [doc/roleandprivileage.md › 2. The Five System Roles](../../../doc/roleandprivileage.md#2-the-five-system-roles)  
**Files:** [SVG](01-role-relationships.svg) · [PNG](01-role-relationships.png) · [Mermaid source](01-role-relationships.mmd)

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

This is a copy of the Mermaid block in [doc/roleandprivileage.md](../../../doc/roleandprivileage.md#2-the-five-system-roles). When it changes, update the source document, this file and `01-role-relationships.mmd` together, then re-render ([how](../../README.md#regenerating)).
