---
name: mini-ux
description: Reusable skill for designing, styling, and updating views to be modern, responsive, and highly attractive in the Laravel Minimarket project.
---

# Skill: mini-ux

This skill equips the agent with domain-specific knowledge and guidelines for crafting modern, high-fidelity user experiences and styles within the Minimarket application.

## Core Capabilities

1. **Tailwind CSS & Styling**: Utilizing Tailwind CSS (v4) features, custom CSS variables, and utility classes to build polished, responsive layouts.
2. **Filament UX Customization**: Overriding and styling Filament resources, forms, tables, and dashboards to create a premium admin interface.
3. **Public Landing & Catalog**: Improving the public landing page (`resources/views/public/inicio.blade.php`) and the Livewire catalog (`livewire:publico.catalogo-productos`) with micro-animations, glassmorphism, and intuitive layout flows.
4. **Ventas/POS Subsystem UI**: Enhancing checkout dashboards, receipt/ticket layouts, cashier session screens, and key checkout modules to be clean, readable, and lightning-fast to navigate.

## Guidelines

- **Harmonious Color Palette**: Avoid default or primary-only colors. Use sophisticated color sets (like Emerald, Amber, and Slate/Zinc for dark modes/neutral text).
- **Responsive-First Grid & Flexbox**: Always test views on narrow screens (mobile) as cashiers or customers might view them on tablets or mobile devices.
- **Vite & Tailwind Builds**: Run compilation or checking tools when styling changes are introduced to verify that compilation succeeds and classes are generated correctly.
