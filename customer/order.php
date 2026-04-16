<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect to new booking flow
$ticket_id = $_GET['ticket_id'] ?? null;
if ($ticket_id) {
    redirect('../flight_detail.php?id=' . $ticket_id);
} else {
    redirect('../index.php');
}
