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
$totalDuration = getTotalDuration($globalResults, $results[0]['branch']);

$data = [
    'stats' => [
        'suites' => 1,
        'tests' => count($results),
        'passes' => getPasses($globalResults),
        'pending' => 0,
        'failures' => getFailures($globalResults),
        'testsRegistered' => count($results),
        //passPercent
        //pendingPercent
        'other' => 0,
        'hasOther' => 0,
        'skipped' => 0,
        'hasSkipped' => 0,
        'start' => getDateStart($results)->format('Y-m-d\TH:i:s\Z'),
        'end' => getDateEnd($globalResults)->format('Y-m-d\TH:i:s\Z'),
        'duration' => $totalDuration,
    ],
    'results' => [],
];

foreach ($globalResults as $globalResult) {
    $suiteTestsPasses = $suiteTestsFailures = $suiteTestsPending = $suiteTestsSkipped = [];
    foreach($globalResult['tests'] as $test) {
        if ($test['pass']) {
            $suiteTestsPasses[] = $test['uuid'];
            continue;
        }
        if ($test['fail']) {
            $suiteTestsFailures[] = $test['uuid'];
            continue;
        }
        if ($test['pending']) {
            $suiteTestsPending[] = $test['uuid'];
            continue;
        }
    }

    $data['results'][] = [
        'uuid' => $globalResult['uuid'],
        'title' => 'Upgrade to branch ' . $psBranch,
        'fullFile' => '',
        'file' => '',
        'tests' => [],
        'suites' => [
            [
                'uuid' => $globalResult['uuid'],
                'title' => $globalResult['title'],
                'file' => '',
                'tests' => $globalResult['tests'],
                'suites' => [],
                'passes' => $suiteTestsPasses,
                'failures' => $suiteTestsFailures,
                'pending' => $suiteTestsPending,
                'skipped' => $suiteTestsSkipped,
                'duration' => $globalResult['duration'],
                'root' => false,
                'rootEmpty' => false,
            ],
        ],
        'passes' => [],
        'failures' => [],
        'pending' => [],
        'skipped' => [],
        'duration' => $data['stats']['duration'],
        'root' => true,
        'rootEmpty' => true,
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
        'title' => '[' . $branch . '] Upgrade from ' . $data[1] . ' to ' . $data[2],
        'duration' => $duration,
        'state' => $state,
        'pass' => $state === 'passed',
        'fail' => $state === 'failed',
        'pending' => false,
        'context' => '{"title": "testIdentifier","value": "[' . $branch . '] Upgrade from ' . $data[1] . ' to ' . $data[2] . '"}',
        'err' => $error,
        'uuid' => uniqid(),
        'skipped' => false,
        'branch' => $branch,
        'date_start' => $dateStart,
        'date_end' => $dateEnd,
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
        $resultBranch = $result['branch'];
        if (!isset($globalResults[$resultBranch])) {
            $globalResults[$resultBranch] = [
                'uuid' => uniqid(),
                'title' => '[' . $resultBranch . '] Upgrade to branch ' . $psBranch,
                'tests' => [],
                'date_start' => null,
                'date_end' => null,
                'passes' => 0,
                'failures' => 0,
            ];
        }
        if (null === $globalResults[$resultBranch]['date_start']
            || $result['date_start'] < $globalResults[$resultBranch]['date_start']) {
            $globalResults[$resultBranch]['date_start'] = $result['date_start'];
        }
        if (null === $globalResults[$resultBranch]['date_end']
            || $result['date_end'] > $globalResults[$resultBranch]['date_end']) {
            $globalResults[$resultBranch]['date_end'] = $result['date_end'];
        }
        $globalResults[$resultBranch]['passes'] += $result['state'] === 'passed' ? 1 : 0;
        $globalResults[$resultBranch]['failures'] += $result['state'] !== 'passed' ? 1 : 0;
        $globalResults[$resultBranch]['tests'][] = [
            'title' => $result['title'],
            'fullTitle' => $result['title'],
            'timedOut' => false,
            'duration' => $result['duration'],
            'state' => $result['state'],
            'pass' => $result['state'] === 'passed',
            'fail' => $result['state'] === 'failed',
            'pending' => $result['state'] === 'pending',
            'context' => $result['context'],
            'uuid' => $result['uuid'],
            'parentUUID' => $globalResults[$resultBranch]['uuid'],
            'isHook' => false,
            'skipped' => $result['state'] === 'skipped',
        ];
    }

    foreach ($globalResults as &$globalResult) {
        $globalResult['duration'] = ($globalResult['date_end']->getTimestamp() - $globalResult['date_start']->getTimestamp()) * 1000;
    }

    return $globalResults;
}

function getDateStart(array $results): DateTime {
    $dateStart = null;

    foreach ($results as $result) {
        if (null === $dateStart || $result['date_start'] < $dateStart) {
            $dateStart = $result['date_start'];
        }
    }

    return $dateStart;
}

function getDateEnd(array $results): DateTime {
    $dateEnd = null;

    foreach ($results as $result) {
        if (null === $dateEnd || $result['date_end'] < $dateEnd) {
            $dateEnd = $result['date_end'];
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

function getTotalDuration(array $results, string $branch): int {
    $duration = 0;

    foreach ($results[$branch]['tests'] as $result) {
        $duration += $result['duration'];
    }

    return $duration;
}
