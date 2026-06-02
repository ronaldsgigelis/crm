<?php

// Palīgfunkcijas, piemēram: render(), redirect(), h()
require_once __DIR__ . '/../app/helpers.php';

// Maršrutu funkcijas: path(), is_route(), url()
require_once __DIR__ . '/../app/routes.php';

// Kontrolieri
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ClientsController.php';
require_once __DIR__ . '/../app/controllers/JobsController.php';
require_once __DIR__ . '/../app/controllers/MastersController.php';
require_once __DIR__ . '/../app/controllers/InvoicesController.php';

// Sākumlapa
if (is_route('GET', '/')) {
  dashboard_index();
  exit;
}

// Klienti
if (is_route('GET', '/clients')) {
  clients_index();
  exit;
}
if (is_route('POST', '/clients')) {
  clients_store();
  exit;
}

// Viena klienta kartīte
if (is_route('GET', '/client')) {
  client_show();
  exit;
}

// Darbi
if (is_route('GET', '/jobs')) {
  jobs_index();
  exit;
}
if (is_route('POST', '/jobs')) {
  jobs_store();
  exit;
}

// Viena darba kartīte
if (is_route('GET', '/job')) {
  job_show();
  exit;
}

// Darba statusa maiņa
if (is_route('POST', '/job')) {
  job_update_status();
  exit;
}

// Pilna darba rediģēšana
if (is_route('POST', '/job-update')) {
  job_update();
  exit;
}

// Meistari
if (is_route('GET', '/masters')) {
  masters_index();
  exit;
}
if (is_route('POST', '/masters')) {
  masters_store();
  exit;
}

// Publiska meistara darbu lapa pēc token
if (is_route('GET', '/my-jobs')) {
  my_jobs();
  exit;
}

// Darba piešķiršana meistaram
if (is_route('POST', '/job-assign')) {
  job_assign_master();
  exit;
}

if (is_route('GET', '/invoice-settings')) {
  invoice_settings_index();
  exit;
}
if (is_route('POST', '/invoice-settings')) {
  invoice_settings_store();
  exit;
}
if (is_route('POST', '/invoice-settings/default')) {
  invoice_settings_default_store();
  exit;
}
if (is_route('GET', '/invoices')) {
  invoices_index();
  exit;
}
if (is_route('GET', '/manual-invoice')) {
  manual_invoice_form();
  exit;
}
if (is_route('POST', '/manual-invoice')) {
  manual_invoice_store();
  exit;
}
if (is_route('GET', '/invoice-create')) {
  invoice_create_form();
  exit;
}
if (is_route('POST', '/invoice-create')) {
  invoice_store();
  exit;
}
if (is_route('GET', '/invoice')) {
  invoice_show();
  exit;
}
if (is_route('POST', '/invoice-delete')) {
  invoice_delete();
  exit;
}
if (is_route('POST', '/invoice-payment-status')) {
  invoice_payment_status_update();
  exit;
}
if (is_route('GET', '/invoice-pdf')) {
  invoice_pdf();
  exit;
}

// Ja nekas neatbilst, rādam 404
http_response_code(404);
echo "404 Not Found";
