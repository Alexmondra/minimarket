---
name: mini-compras
description: Reusable skill for building and managing the purchases (compras) subsystem in the Laravel Minimarket project.
---

# Skill: mini-compras

This skill equips the agent with specific domain knowledge for developing the purchases (compras) subsystem within the Minimarket application.

## Core Capabilities

1. **Supplier Management**: Handling supplier records (`Proveedor`), supplier contacts, and active status checks.
2. **Purchase Transactions**: Developing Eloquent models, migrations, and views for purchases (`Compra`) and purchase details (`DetalleCompra`).
3. **Lot & Expiration Tracking**: Creating and managing batches/lots (`Lote`, `LotePresentacion`) of products, containing cost price, stock, and expiration dates.
4. **Filament Integration**: Crafting user interfaces within the `Compras` cluster (`app/Filament/Clusters/Compras`) using Filament resources, relation managers, custom action buttons, and wizards.

## Guidelines

- Ensure database operations regarding purchase registration, lot allocation, and stock updates are wrapped in secure transactions (`DB::transaction`).
- Always check and validate the active sucursal context using `app(SucursalContext::class)` before registering any purchase.
- Update/write tests in `tests/` to verify purchase logic, stock intake, and transaction boundaries.
