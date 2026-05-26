---
name: mini-factu
description: Reusable skill for building, debugging, and managing the electronic invoicing (SUNAT Perú) subsystem in the Laravel Minimarket project.
---

# Skill: mini-factu

This skill equips the agent with domain-specific knowledge and best practices for developing the electronic invoicing (facturación electrónica) subsystem for SUNAT Perú.

## Core Capabilities

1. **UBL 2.1 XML Documents**: Implementing factories and helpers (e.g., `DocumentoGreenterFactory`) to generate XML structures for:
   - **Boletas de Venta** (Sales receipts)
   - **Facturas** (Invoices)
   - **Notas de Crédito** (Credit Notes)
   - **Notas de Débito** (Debit Notes)
   - **Resúmenes Diarios** (Daily summaries for Boletas)
   - **Comunicaciones de Baja** (Cancellations)
2. **Digital Certificate & Signature Management**: Accessing and loading PEM/PFX digital certificates, configuring SSL/TLS, and signing XML documents using the Greenter SDK (`GreenterSeeFactory`).
3. **SOAP Client & Endpoints**: Sending signed XML files to SUNAT API endpoints (Beta/Homologación vs. Producción) and PSE/OSE integrations.
4. **CDR Receiving & Response Parsing**: Extracting and handling CDR (Constancia de Recepción) zip files, parsing XML responses, logging status/response codes, and parsing notes or warnings.
5. **Filament Integration**: Customizing forms, tables, action buttons, and logs under the `Sunat` cluster (`app/Filament/Clusters/Sunat`) to allow manual re-sending of documents, downloading XML/CDR, and troubleshooting validation errors.

## Guidelines

- **Transactions and State**: Updates to `Sunat` status logs must be done atomically. If document sending fails, log the error code returned by SUNAT (e.g., 2000+ codes) or exceptions safely.
- **Environment Separation**: Always verify the Tributary Environment (`entorno` from `Empresa`) before sending documents. Beta/Homologación endpoints must be used for testing, and Producción endpoints only for live operations.
- **Greenter Model mapping**: Map attributes (RUC, serie, numero, cliente, items, IGV, exonerado, inafecto, gravado) correctly between Laravel `Documento`/`DetalleDocumento` models and Greenter models (`Invoice`, `SaleDetail`, `Client`, `Company`, etc.).
- **Response Handling Code Map**:
  - Code `0`: Approved.
  - Codes `100 - 1999`: Approved but has warnings (ensure notes are logged).
  - Codes `>= 2000`: Rejected (rechazado) or invalid (require correction and re-sending or replacement).
