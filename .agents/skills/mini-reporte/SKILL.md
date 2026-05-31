---
name: mini-reporte
description: Reusable skill for building, debugging, and managing the reporting and analytics subsystem in the Laravel Minimarket project.
---

# Skill: mini-reporte

This skill equips the agent with specific domain knowledge for developing and maintaining the reports and analytics subsystem within the Minimarket application.

## Core Capabilities

1. **Financial Reports**: Implementing profit (`Ganancias`) and loss (`Perdidas`) calculations and summaries, including cost of goods sold (COGS) and operational costs.
2. **Sales & Purchasing Analytics**: Aggregating sales metrics (`Ventas`) and purchase details (`Compras`) over customizable time periods.
3. **Inventory & Product Reports**: Developing product movement reports, slow-moving items, and stock value analytics.
4. **Filament & Livewire Integration**: Building and enhancing Filament report pages within the `Reportes` cluster (`app/Filament/Clusters/Reportes`), implementing custom charts (e.g. Filament Widgets or Chart.js), filters, and data export features.

## Guidelines

- Use optimized SQL/Eloquent queries with aggregations (e.g. `sum()`, `count()`) to compute reports quickly without loading large volumes of models into memory.
- Leverage eager loading (e.g. `with()`) to prevent N+1 query issues.
- Support robust filtering by date ranges, branches/sucursales, and product categories where applicable.
- Adhere to the existing naming patterns and UI/UX theme conventions in the Laravel Minimarket application.
