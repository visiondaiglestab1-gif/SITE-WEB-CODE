<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$events = $db->query("
    SELECT 
        e.id,
        e.title,
        e.description,
        e.event_date as start,
        e.event_time,
        e.location,
        COUNT(r.id) as registrations
    FROM events e
    LEFT JOIN event_registrations r ON e.id = r.event_id
    GROUP BY e.id
")->fetchAll(PDO::FETCH_ASSOC);

$formatted_events = [];
foreach($events as $event) {
    $formatted_events[] = [
        'id' => $event['id'],
        'title' => $event['title'],
        'start' => $event['start'] . 'T' . $event['event_time'],
        'description' => $event['description'],
        'extendedProps' => [
            'location' => $event['location'],
            'registrations' => $event['registrations']
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($formatted_events);
?>