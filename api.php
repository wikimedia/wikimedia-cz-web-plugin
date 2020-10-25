<?php

require_once 'autoload.php';

header('Content-Type: application/json');

switch ($_GET['action']) {
    case 'getMapData':
        $ical = $_GET['ical'];
        $calendar = new WmczCalendar( $ical );
        echo json_encode([
            'status' => 'ok',
            'ical' => $ical,
            'data' => [
                'points' => $calendar->getPlaces(),
            ]
        ]);
        break;
    
    default:
        echo json_encode([
            'status' => 'error',
            'errorcode' => 'invalid-action'
        ]);
        break;
}

?>