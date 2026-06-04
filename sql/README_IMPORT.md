# Database Import

For a fresh coursework/demo install, create an empty local MySQL/MariaDB database and import `sql/full_setup.sql`. The file creates and uses the default project database name, `crm`.

Do not rely on `sql/schema.sql` alone for the final version. It is an early base schema from development and does not include all current invoice profile, numbering, payment status, and manual invoice template structure.

The other SQL files in this folder are kept for development history and incremental migration reference:

- `schema.sql` - early base schema / historical setup file.
- `invoices.sql` - invoice table setup from a development phase.
- `invoice_profiles.sql` - invoice seller profile table setup/migration.
- `invoice_profile_id.sql` - migration adding the invoice profile relation to invoices.
- `invoice_number_settings.sql` - migration adding invoice numbering settings.
- `invoice_payment_status.sql` - migration adding invoice payment status fields.
- `invoice_templates.sql` - manual invoice templates table setup.

Recommended import methods:

- phpMyAdmin: open the local MySQL server, select or create the `crm` database, then import `sql/full_setup.sql`.
- MySQL command line: run `mysql -u root < sql/full_setup.sql` from the project root, or adjust the `CREATE DATABASE` / `USE` lines in `full_setup.sql` if your local database name is different.

If importing into an existing database, make a backup first.

No application code needs to be changed after importing, except normal local database configuration if your MySQL username, password, host, or database name differs from the project defaults.
