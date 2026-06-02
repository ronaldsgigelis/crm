<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';

function dashboard_scalar(string $sql): string
{
  $value = db()->query($sql)->fetchColumn();
  return $value === false || $value === null ? '0' : (string)$value;
}

function dashboard_summary(): array
{
  return [
    'clients_count' => (int)dashboard_scalar("SELECT COUNT(*) FROM clients"),
    'jobs_count' => (int)dashboard_scalar("SELECT COUNT(*) FROM jobs"),
    'jobs_without_invoice' => (int)dashboard_scalar("
      SELECT COUNT(*)
      FROM jobs j
      LEFT JOIN invoices i ON i.job_id = j.id
      WHERE i.id IS NULL
    "),
    'unpaid_invoices' => (int)dashboard_scalar("
      SELECT COUNT(*)
      FROM invoices
      WHERE payment_status = 'unpaid' AND due_date >= CURDATE()
    "),
    'overdue_invoices' => (int)dashboard_scalar("
      SELECT COUNT(*)
      FROM invoices
      WHERE payment_status = 'unpaid' AND due_date < CURDATE()
    "),
    'issued_this_month' => (float)dashboard_scalar("
      SELECT COALESCE(SUM(total), 0)
      FROM invoices
      WHERE invoice_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        AND invoice_date < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
    "),
    'paid_this_month' => (float)dashboard_scalar("
      SELECT COALESCE(SUM(total), 0)
      FROM invoices
      WHERE payment_status = 'paid'
        AND paid_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        AND paid_at < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
    "),
  ];
}

function dashboard_today_jobs(): array
{
  return db()->query("
    SELECT j.id, j.title, j.status, j.scheduled_at, c.name AS client_name
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    WHERE DATE(j.scheduled_at) = CURDATE()
    ORDER BY j.scheduled_at ASC, j.id ASC
  ")->fetchAll();
}

function dashboard_upcoming_jobs(): array
{
  return db()->query("
    SELECT j.id, j.title, j.status, j.scheduled_at, c.name AS client_name
    FROM jobs j
    JOIN clients c ON c.id = j.client_id
    WHERE j.scheduled_at >= NOW()
    ORDER BY j.scheduled_at ASC, j.id ASC
    LIMIT 5
  ")->fetchAll();
}

function dashboard_recent_invoices(): array
{
  return db()->query("
    SELECT id, invoice_number, payer_name, total, currency, payment_status, paid_at, due_date
    FROM invoices
    ORDER BY invoice_date DESC, id DESC
    LIMIT 5
  ")->fetchAll();
}

function dashboard_job_status_label(string $status): string
{
  return match ($status) {
    'new' => 'Jauns',
    'in_progress' => 'Procesā',
    'done' => 'Pabeigts',
    'canceled' => 'Atcelts',
    default => $status,
  };
}

function dashboard_invoice_payment_status_label(array $invoice): string
{
  if (($invoice['payment_status'] ?? 'unpaid') === 'paid') {
    return 'Apmaksāts' . (!empty($invoice['paid_at']) ? ', ' . $invoice['paid_at'] : '');
  }

  if (!empty($invoice['due_date']) && $invoice['due_date'] < date('Y-m-d')) {
    return 'Kavēts';
  }

  return 'Nav apmaksāts';
}

function dashboard_index(): void
{
  render('dashboard.php', [
    'summary' => dashboard_summary(),
    'todayJobs' => dashboard_today_jobs(),
    'upcomingJobs' => dashboard_upcoming_jobs(),
    'recentInvoices' => dashboard_recent_invoices(),
  ], 'CRM');
}
