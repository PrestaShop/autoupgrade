<?php

$matrix = [];

$nightlyEnpoint = "https://api-nightly.prestashop.com/reports";

$reports = json_decode(file_get_contents($nightlyEnpoint), true);
$currentDate = "";
foreach ($reports as $report) {
    $date = strtotime($report['date']);
    if ("" === $currentDate) {
        $currentDate = $date;
    }
    if ($date === $currentDate) {
        $matrix[] = [
            "from" => "1.7.6.9",
            "channel" => "archive",
            "version" => getVersionFromFilename($report['download']),
            "file" => $report['download']
        ];
    }
}

function getVersionFromFilename($filename) {
    $matches = [];
    preg_match('/^.*prestashop_(.*)\.zip$/', $filename, $matches);

    return $matches[1];
}

echo json_encode($matrix);
