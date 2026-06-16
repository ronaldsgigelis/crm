# CRM sistēmas prototips / CRM System Prototype

## Apraksts / Description

Latvian:
Šis ir PHP MVC CRM sistēmas prototips kursa darbam un lokālai demonstrācijai. Projekts nodrošina klientu, darbu/pasūtījumu un meistaru pārvaldību, rēķinu pārvaldību, apmaksas statusu uzskaiti, informācijas paneli ar statistiku, manuālu rēķinu izveidi un PDF rēķinu eksportu.

English:
This is a PHP MVC CRM system prototype for coursework and local demonstration. The project provides client, job/order and master management, invoice management, payment status tracking, a dashboard with statistics, manual invoice creation and PDF invoice export.

## Galvenās iespējas / Main Features

Latvian:
- Informācijas panelis ar kopsavilkumiem un statistiku.
- Klientu izveide, apskate, rediģēšana un dzēšana.
- Darbu/pasūtījumu izveide, apskate, rediģēšana, statusu maiņa un filtrēšana.
- Meistaru pārvaldība un publiska meistara darbu lapa ar token saiti.
- Rēķinu iestatījumi un pārdevēja profili.
- Rēķinu ģenerēšana no CRM darba.
- Manuāla rēķinu izveide un rēķinu sagataves.
- PDF rēķinu eksports ar Dompdf.
- Apmaksātu, neapmaksātu un kavētu rēķinu statusu uzskaite.
- Rēķinu filtri pēc tipa un apmaksas statusa.
- Gaišais/tumšais UI režīms.

English:
- Dashboard with summaries and statistics.
- Client creation, viewing, editing and deletion.
- Job/order creation, viewing, editing, status changes and filtering.
- Master management and a public master job page using a token link.
- Invoice settings and seller profiles.
- Invoice generation from a CRM job.
- Manual invoice creation and invoice templates.
- PDF invoice export with Dompdf.
- Paid, unpaid and overdue invoice status tracking.
- Invoice filters by type and payment status.
- Light/dark UI mode.

## Tehnoloģijas / Technologies

Latvian:
- PHP
- MySQL/MariaDB
- PDO
- HTML
- CSS
- JavaScript
- Composer
- Dompdf
- Lokāls Apache/XAMPP vai PHP iebūvētais serveris

English:
- PHP
- MySQL/MariaDB
- PDO
- HTML
- CSS
- JavaScript
- Composer
- Dompdf
- Local Apache/XAMPP or the PHP built-in server

## Projekta struktūra / Project Structure

Latvian:
- `public/` - publiskā tīmekļa sakne.
- `public/index.php` - galvenais front controller un maršrutu izpilde.
- `public/assets/` - projekta CSS un JavaScript faili.
- `app/` - konfigurācija, datubāzes savienojums, palīgfunkcijas un maršrutu funkcijas.
- `app/controllers/` - MVC kontrolieri klientiem, darbiem, meistariem, rēķiniem un panelim.
- `views/` - HTML/PHP skatu faili.
- `sql/` - datubāzes struktūras, demo dati un importēšanas dokumentācija.
- `vendor/` - Composer instalētās bibliotēkas.
- `composer.json` - PHP atkarību konfigurācija.

English:
- `public/` - public web root.
- `public/index.php` - main front controller and route execution.
- `public/assets/` - project CSS and JavaScript files.
- `app/` - configuration, database connection, helpers and route functions.
- `app/controllers/` - MVC controllers for clients, jobs, masters, invoices and dashboard.
- `views/` - HTML/PHP view files.
- `sql/` - database structure, demo data and import documentation.
- `vendor/` - Composer-installed libraries.
- `composer.json` - PHP dependency configuration.

## Uzstādīšana lokāli / Local Installation

Latvian:
1. Iekopējiet projektu XAMPP `htdocs` mapē vai citā lokālā PHP vidē.
2. Izveidojiet tukšu MySQL/MariaDB datubāzi.
3. Detalizētām datubāzes importēšanas instrukcijām izmantojiet `sql/README_IMPORT.md`.
4. Pārbaudiet datubāzes pieslēguma iestatījumus failā `app/config.php`.
5. Palaidiet Apache/MySQL vai izmantojiet PHP iebūvēto serveri.
6. Atveriet projektu pārlūkprogrammā.

English:
1. Copy the project into XAMPP `htdocs` or another local PHP environment.
2. Create an empty MySQL/MariaDB database.
3. Use `sql/README_IMPORT.md` for detailed database setup/import instructions.
4. Check the database connection settings in `app/config.php`.
5. Start Apache/MySQL or use the PHP built-in server.
6. Open the project in a browser.

```bash
php -S localhost:8081 -t public
```

## Datubāze / Database

Latvian:
Detalizētas datubāzes izveides un importēšanas instrukcijas ir pieejamas failā `sql/README_IMPORT.md`.

English:
Detailed database setup and import instructions are available in `sql/README_IMPORT.md`.

## Palaišana / Running the Project

Latvian:
Projektu ieteicams servēt no `public/` mapes. XAMPP vidē to var atvērt caur lokālo Apache adresi, vai arī projekta saknē palaist:

English:
The project should be served from the `public/` directory. In XAMPP it can be opened through the local Apache address, or from the project root run:

```bash
php -S localhost:8081 -t public
```

## Testēšanas saraksts / Testing Checklist

Latvian:
- Atvērt informācijas paneli.
- Izveidot un apskatīt klientu.
- Izveidot un apskatīt darbu/pasūtījumu.
- Izveidot un apskatīt meistaru.
- Ģenerēt un apskatīt rēķinu.
- Izveidot manuālu rēķinu.
- Eksportēt rēķinu PDF formātā.
- Mainīt apmaksāts/neapmaksāts statusu.
- Pārbaudīt filtrus.
- Pārbaudīt tumšo režīmu.

English:
- Open the dashboard.
- Create and view a client.
- Create and view a job/order.
- Create and view a master.
- Generate and view an invoice.
- Create a manual invoice.
- Export an invoice as PDF.
- Change paid/unpaid status.
- Test filters.
- Test dark mode.

## Svarīgas piezīmes / Important Notes

Latvian:
- Šis ir kursa darba/prototipa projekts lokālai un demonstrācijas lietošanai.
- Datubāzes piekļuves dati failā `app/config.php` jāpielāgo lokālajai videi.
- Projektu ieteicams servēt no `public/` mapes.
- Detalizētas datubāzes instrukcijas ir failā `sql/README_IMPORT.md`.
- Nepublicējiet reālus privātos piekļuves datus, klientu datus vai uzņēmuma informāciju.
- Publiskas token saites nedrīkst kopīgot publiski bez piekļuves kontroles izvērtēšanas.

English:
- This is a coursework/prototype project for local and demo use.
- Database credentials in `app/config.php` should be adjusted for the local environment.
- The project should be served from the `public/` directory.
- Detailed database instructions are in `sql/README_IMPORT.md`.
- Do not publish real private credentials, real client data or private company information.
- Public token links should not be shared publicly without an access-control review.

## Autors / Author

Author: Ronalds Gigelis  
Project type: coursework / CRM prototype
