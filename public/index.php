<?php

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/routes.php';
require_once __DIR__ . '/../app/controllers/ClientsController.php';
require_once __DIR__ . '/../app/controllers/JobsController.php';

if (is_route('GET', '/')) {
  $title = 'CRM';
  $content = '<p class="muted"> CRM sākums</p>';
  require __DIR__ . '/../views/layout.php';
  exit;
}

if (is_route('GET', '/clients')) { clients_index(); exit; }
if (is_route('POST', '/clients')) { clients_store(); exit; }

if (is_route('GET', '/jobs')) { jobs_index(); exit; }
if (is_route('POST', '/jobs')) { jobs_store(); exit; }

if (is_route('GET', '/job')) { job_show(); exit; }
if (is_route('POST', '/job')) { job_update_status(); exit; }

http_response_code(404);
echo "404 Not Found";
