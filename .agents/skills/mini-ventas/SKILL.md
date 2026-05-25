---
name: mini-ventas
description: Reusable skill for building and managing the sales (ventas) subsystem in the Laravel Minimarket project.
---

# Skill: mini-ventas

This skill equips the agent with specific domain knowledge for developing the sales (ventas) subsystem within the Minimarket application.

## Core Capabilities

1. **Cashier Management**: Handling cashier sessions (`SessioneCaja`), checkout operations, and cash register flows.
2. **Sales Transactions**: Developing Eloquent models and migrations for sales (`Venta`), sales items/details, and stock deductions.
3. **Loyalty Program**: Updating customer loyalty points (`ClientePunto`) upon transaction completion.
4. **Filament Integration**: Crafting user interfaces within the `Ventas` cluster (`app/Filament/Clusters/Ventas`) using Filament resources, relation managers, and custom actions.

## Guidelines

- Always check if a cashier session is open before recording sales.
- Ensure product stock (`ProductoSucursal` or `Lote`) is checked and decremented correctly.
- Add test coverage for transaction rollback cases in `tests/`.
