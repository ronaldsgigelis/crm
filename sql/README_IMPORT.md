# Database Import

For a quick qualification demonstration, import `sql/demo_setup.sql` into a local MySQL/MariaDB server to install the structure with prepared sample data. For an empty installation without demo records, import `sql/full_setup.sql` instead. Both standalone files create/use the default project database name, `crm`.

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

* phpMyAdmin: open the local MySQL server, select or create the `crm` database, then import `sql/full_setup.sql` or `sql/demo_setup.sql`.
* MySQL command line: run `mysql -u root < sql/full_setup.sql` from the project root, or use `mysql -u root < sql/demo_setup.sql` for the prepared demo database.

## Demo database

For presentation, an optional `sql/demo_setup.sql` file can be used.

* `sql/full_setup.sql` - clean empty database structure.
* `sql/demo_setup.sql` - prepared demo database with sample clients, masters, jobs, invoice profiles, invoices, and templates.

If `demo_setup.sql` is available, import it instead of `full_setup.sql` for the coursework presentation.

Note: demo data may reference uploaded files, for example invoice logos. When moving the project to another computer, copy the full project folder, including `public/uploads/` if it exists.

If importing into an existing database, make a backup first.

No application code needs to be changed after importing, except normal local database configuration if your MySQL username, password, host, or database name differs from the project defaults.
