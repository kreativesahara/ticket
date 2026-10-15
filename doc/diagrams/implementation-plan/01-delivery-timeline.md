# Delivery timeline (12 weeks)

**Type:** Gantt chart  
**Source:** [implementation-plan.md › 1.3 Timeline](../../../implementation-plan.md#13-timeline)  
**Files:** [SVG](01-delivery-timeline.svg) · [PNG](01-delivery-timeline.png) · [Mermaid source](01-delivery-timeline.mmd)

```mermaid
gantt
    %% Placeholder dates: 2024-01-01 is the start of project week 1. Axis labels show project weeks.
    title Indicative 12-week delivery plan
    dateFormat YYYY-MM-DD
    axisFormat Wk %W
    tickInterval 1week
    weekday monday

    section Phases
    0 Discovery and specification       :p0, 2024-01-01, 7d
    1 Design and foundation             :p1, 2024-01-08, 14d
    2 Events and ticket configuration   :p2, 2024-01-22, 14d
    3 Checkout and inventory            :p3, 2024-01-29, 14d
    4 Payments                          :p4, 2024-02-12, 14d
    5 Ticket issuance and delivery      :p5, 2024-02-19, 14d
    6 Gate access and scanning          :p6, 2024-02-26, 14d
    7 Seating, promotions and reporting :p7, 2024-03-04, 14d
    8 Testing, deployment and handover  :p8, 2024-03-11, 14d

    section Client dependencies
    Pesapal onboarding                  :crit, c1, 2024-01-01, 70d
    User acceptance testing             :c2, 2024-03-18, 5d

    section Milestones
    M1 Requirements approved            :milestone, m1, 2024-01-08, 0d
    M2 Events and checkout              :milestone, m2, 2024-02-12, 0d
    M3 Payments and ticketing           :milestone, m3, 2024-03-04, 0d
    M4 UAT, deployment and handover     :milestone, m4, 2024-03-25, 0d
```

This is a copy of the Mermaid block in [implementation-plan.md](../../../implementation-plan.md#13-timeline). When it changes, update the source document, this file and `01-delivery-timeline.mmd` together, then re-render ([how](../../README.md#regenerating)).
