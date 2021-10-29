<?php

$matrix = [];

$nightlyEndpoint = "https://api-nightly.prestashop.com/reports";

$reports = json_decode(file_get_contents($nightlyEndpoint), true);
$currentDate = "";
$zipFiles = [];
foreach ($reports as $report) {
    $date = strtotime($report['date']);
    if ("" === $currentDate) {
        $currentDate = $date;
    }

    if (null === $report['download'] || in_array($report['download'], $zipFiles)) {
        continue;
    }

    if ($date === $currentDate) {
        $zipFiles[] = $report['download'];
        $matrix[] = [
            "channel" => "archive",
            "branch" => $report['version'],
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
