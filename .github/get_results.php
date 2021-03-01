<?php

$branch = $argv[1] ?? null;
if (null === $branch) {
    return;
}

$results = [];
$files = getResultFiles($branch);
foreach ($files as $file) {
    $results[] = getTestResultFromFile($file);
}
$globalResults = getGlobalResults($results, $branch);

$data = [
    'stats' => [
        'start' => $globalResults['date_start']->format('Y-m-d H:i:s'),
        'end' => $globalResults['date_end']->format('Y-m-d H:i:s'),
        'duration' => $globalResults['duration'],
        'skipped' => 0,
        'pending' => 0,
        'passes' => $globalResults['passes'],
        'failures' => $globalResults['failures'],
        'suites' => 1,
        'tests' => count($results),
    ],
    'suites' => [
        'uuid' => uniqid(),
        'title' => $globalResults['title'],
        'file' => '',
        'duration' => $globalResults['duration'],
        'hasSkipped' => false,
        'hasPending' => false,
        'hasPasses' => $globalResults['passes'] > 0,
        'hasFailures' => $globalResults['failures'] > 0,
        'totalSkipped' => 0,
        'totalPending' => 0,
        'totalPasses' => $globalResults['passes'],
        'totalFailures' => $globalResults['failures'],
        'hasSuites' => true,
        'hasTests' => false,
        'tests' => [],
        'suites' => [[
            'uuid' => uniqid(),
            'title' => $globalResults['title'],
            'file' => '',
            'duration' => $globalResults['duration'],
            'hasSkipped' => false,
            'hasPending' => false,
            'hasPasses' => $globalResults['passes'] > 0,
            'hasFailures' => $globalResults['failures'] > 0,
            'totalSkipped' => 0,
            'totalPending' => 0,
            'totalPasses' => $globalResults['passes'],
            'totalFailures' => $globalResults['failures'],
            'hasSuites' => false,
            'hasTests' => true,
            'suites' => [],
            'tests' => $results,
        ]],
    ],
];

$filename = 'autoupgrade_' . date('Y-m-d') . '-' . $branch . '.json';
file_put_contents($filename, json_encode($data));

function getResultFiles(string $branch): array
{
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('./artifacts'));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isDir() || strpos($file->getPathname(), $branch . '.txt') === false) {
            continue;
        }
        $files[] = $file->getPathname();
    }

    return $files;
}

function getTestResultFromFile(string $file): array
{
    $data = explode('|', trim(file_get_contents($file)));
    $dateStart = getDateTimeFromString($data[3]);
    $dateEnd = getDateTimeFromString($data[4]);
    $duration = ($dateEnd->getTimestamp() - $dateStart->getTimestamp()) * 1000;
    $state = $data[5] === 'success' ? 'passed' : 'failed';
    $error = null;
    if ($state !== 'passed') {
        $error = [
            'message' => sprintf(
                '%s/%s/actions/runs/%s',
                getenv('GITHUB_SERVER_URL'),
                getenv('GITHUB_REPOSITORY'),
                getenv('GITHUB_RUN_ID')
            )
        ];
    }

    return [
        'uuid' => uniqid(),
        'title' => 'Upgrade from ' . $data[0] . ' to ' . $data[1],
        'context' => '{"value": "Upgrade from ' . $data[0] . ' to ' . $data[1] . '"}',
        'skipped' => [],
        'pending' => [],
        'duration' => $duration,
        'state' => $state,
        'err' => $error,
        'date_start' => $dateStart,
        'date_end' => $dateEnd,
        'branch' => $data[1]
    ];
}

function getDateTimeFromString(string $datetime): DateTime
{
    return DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $datetime, new DateTimeZone('UTC'));
}

function getGlobalResults(array $results, string $branch): array
{
    $globalResults = [
        'title' => 'Upgrade to branch ' . $branch,
        'date_start' => null,
        'date_end' => null,
        'duration' => 0,
        'passes' => 0,
        'failures' => 0,
    ];

    foreach ($results as $result) {
        if (null === $globalResults['date_start'] || $result['date_start'] < $globalResults['date_start']) {
            $globalResults['date_start'] = $result['date_start'];
        }
        if (null === $globalResults['date_end'] || $result['date_end'] > $globalResults['date_end']) {
            $globalResults['date_end'] = $result['date_end'];
        }
        $globalResults['passes'] += $result['state'] === 'passed' ? 1 : 0;
        $globalResults['failures'] += $result['state'] !== 'passed' ? 1 : 0;
    }

    $globalResults['duration'] = ($globalResults['date_end']->getTimestamp() - $globalResults['date_start']->getTimestamp()) * 1000;

    return $globalResults;
}