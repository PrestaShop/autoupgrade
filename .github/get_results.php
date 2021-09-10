<?php

$psBranch = $argv[1] ?? null;
if (null === $psBranch) {
    return;
}

$results = [];
$files = getResultFiles($psBranch);
foreach ($files as $file) {
    $results[] = getTestResultFromFile($file);
}
$globalResults = getGlobalResults($results, $psBranch);
$totalDuration = getTotalDuration($globalResults);

$data = [
    'stats' => [
        'start' => getDateStart($globalResults)->format('Y-m-d H:i:s'),
        'end' => getDateEnd($globalResults)->format('Y-m-d H:i:s'),
        'duration' => $totalDuration,
        'skipped' => 0,
        'pending' => 0,
        'passes' => getPasses($globalResults),
        'failures' => getFailures($globalResults),
        'suites' => 1,
        'tests' => count($results),
    ],
    'suites' => [
        'uuid' => uniqid(),
        'title' => 'Upgrade to branch ' . $psBranch,
        'file' => '',
        'duration' => $totalDuration,
        'hasSkipped' => false,
        'hasPending' => false,
        'hasPasses' => getPasses($globalResults) > 0,
        'hasFailures' => getFailures($globalResults) > 0,
        'totalSkipped' => 0,
        'totalPending' => 0,
        'totalPasses' => getPasses($globalResults),
        'totalFailures' => getFailures($globalResults),
        'hasSuites' => true,
        'hasTests' => false,
        'tests' => [],
        'suites' => [],
    ],
];

foreach ($globalResults as $globalResult) {
    $data['suites']['suites'][] = [
        'uuid' => uniqid(),
        'title' => $globalResult['title'],
        'file' => '',
        'duration' => $globalResult['duration'],
        'hasSkipped' => false,
        'hasPending' => false,
        'hasPasses' => $globalResult['passes'] > 0,
        'hasFailures' => $globalResult['failures'] > 0,
        'totalSkipped' => 0,
        'totalPending' => 0,
        'totalPasses' => $globalResult['passes'],
        'totalFailures' => $globalResult['failures'],
        'hasSuites' => false,
        'hasTests' => true,
        'suites' => [],
        'tests' => $globalResult['tests'],
    ];
}

$filename = 'autoupgrade_' . date('Y-m-d') . '-' . $psBranch . '.json';
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
    $dateStart = getDateTimeFromString($data[4]);
    $dateEnd = getDateTimeFromString($data[5]);
    $duration = ($dateEnd->getTimestamp() - $dateStart->getTimestamp()) * 1000;
    $state = $data[6] === 'success' ? 'passed' : 'failed';
    $branch = $data[0];
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
        'title' => '[' . $branch . '] Upgrade from ' . $data[1] . ' to ' . $data[2],
        'context' => '{"value": "[' . $branch . '] Upgrade from ' . $data[1] . ' to ' . $data[2] . '"}',
        'skipped' => [],
        'pending' => [],
        'duration' => $duration,
        'state' => $state,
        'err' => $error,
        'date_start' => $dateStart,
        'date_end' => $dateEnd,
        'branch' => $branch
    ];
}

function getDateTimeFromString(string $datetime): DateTime
{
    return DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $datetime, new DateTimeZone('UTC'));
}

function getGlobalResults(array $results, string $psBranch): array
{
    $globalResults = [];

    foreach ($results as $result) {
        if (!isset($globalResults[$result['branch']])) {
            $globalResults[$result['branch']] = [
                'title' => '[' . $result['branch'] . '] Upgrade to branch ' . $psBranch,
                'date_start' => null,
                'date_end' => null,
                'duration' => 0,
                'passes' => 0,
                'failures' => 0,
            ];
        }
        if (null === $globalResults[$result['branch']]['date_start'] || $result['date_start'] < $globalResults[$result['branch']]['date_start']) {
            $globalResults[$result['branch']]['date_start'] = $result['date_start'];
        }
        if (null === $globalResults[$result['branch']]['date_end'] || $result['date_end'] > $globalResults[$result['branch']]['date_end']) {
            $globalResults[$result['branch']]['date_end'] = $result['date_end'];
        }
        $globalResults[$result['branch']]['passes'] += $result['state'] === 'passed' ? 1 : 0;
        $globalResults[$result['branch']]['failures'] += $result['state'] !== 'passed' ? 1 : 0;
        $globalResults[$result['branch']]['tests'][] = $result;
    }

    foreach ($globalResults as &$globalResult) {
        $globalResult['duration'] = ($globalResult['date_end']->getTimestamp() - $globalResult['date_start']->getTimestamp()) * 1000;
    }

    return $globalResults;
}

function getDateStart(array $globalResults): DateTime {
    $dateStart = null;

    foreach ($globalResults as $globalResult) {
        if (null === $dateStart || $globalResult['date_start'] < $dateStart) {
            $dateStart = $globalResult['date_start'];
        }
    }

    return $dateStart;
}

function getDateEnd(array $globalResults): DateTime {
    $dateEnd = null;

    foreach ($globalResults as $globalResult) {
        if (null === $dateEnd || $globalResult['date_end'] < $dateEnd) {
            $dateEnd = $globalResult['date_end'];
        }
    }

    return $dateEnd;
}

function getPasses(array $globalResults): int {
    $passes = 0;

    foreach ($globalResults as $globalResult) {
        $passes += $globalResult['passes'];
    }

    return $passes;
}

function getFailures(array $globalResults): int {
    $failures = 0;

    foreach ($globalResults as $globalResult) {
        $failures += $globalResult['failures'];
    }

    return $failures;
}

function getTotalDuration(array $results): int {
    $dateStart = null;
    $dateEnd = null;

    foreach ($results as $result) {
        if (null === $dateStart || $result['date_start'] < $dateStart) {
            $dateStart = $result['date_start'];
        }
        if (null === $dateEnd || $result['date_end'] > $dateEnd) {
            $dateEnd = $result['date_end'];
        }
    }

    return ($dateEnd->getTimestamp() - $dateStart->getTimestamp()) * 1000;
}
