# Task: POS and WhatsApp Integration Fixes

## 1. POS Fixes (Subdirectory Compatibility)
- [ ] Implement `fixUrl` helper in `POS/index.blade.php`.
- [ ] Wrap all `fetch`, `$.get`, `$.post`, and `Select2` URLs with `fixUrl`.
- [ ] Fix `searchReturnInvoices` in `PosController` to support searching by Invoice Number (`INV-xxxx`).

## 2. WhatsApp Fixes
- [ ] Update `WhatsAppService.php` to handle potential 404s and try more intelligent fallback logic.
- [ ] Replace hardcoded WhatsApp URLs in `PosController.php` with service calls.
- [ ] Fix `logout` functionality (if possible) or improve error reporting.

## 3. General Polish
- [ ] Address deprecation warnings in Laravel helpers.
- [ ] Verify image serving endpoints for subdirectory installs.
