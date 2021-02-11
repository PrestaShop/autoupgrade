<?php

$result = getTestResultFromFile('result.txt');
$testPassed = $result['state'] === 'passed';
$branch = $result['branch'];

$data = [
    'stats' => [
        'start' => $result['date_start']->format('Y-m-d H:i:s'),
        'end' => $result['date_end']->format('Y-m-d H:i:s'),
        'duration' => $result['duration'],
        'skipped' => 0,
        'pending' => 0,
        'passes' => $testPassed ? 1 : 0,
        'failures' => $testPassed ? 0 : 1,
        'suites' => 1,
        'tests' => 1,
    ],
    'suites' => [
        'uuid' => uniqid(),
        'title' => $result['title'],
        'file' => '',
        'duration' => $result['duration'],
        'hasSkipped' => false,
        'hasPending' => false,
        'hasPasses' => $testPassed > 0,
        'hasFailures' => $testPassed === 0,
        'totalSkipped' => 0,
        'totalPending' => 0,
        'totalPasses' => $testPassed ? 1 : 0,
        'totalFailures' => $testPassed ? 0 : 1,
        'hasSuites' => true,
        'hasTests' => false,
        'tests' => [],
        'suites' => [[
            'uuid' => uniqid(),
            'title' => $result['title'],
            'file' => '',
            'duration' => $result['duration'],
            'hasSkipped' => false,
            'hasPending' => false,
            'hasPasses' => $testPassed > 0,
            'hasFailures' => $testPassed === 0,
            'totalSkipped' => 0,
            'totalPending' => 0,
            'totalPasses' => $testPassed ? 1 : 0,
            'totalFailures' => $testPassed ? 0 : 1,
            'hasSuites' => false,
            'hasTests' => true,
            'suites' => [],
            'tests' => [$result],
        ]],
    ],
];

$filename = 'autoupgrade_' . date('Y-m-d') . '-' . $branch . '.json';
file_put_contents($filename, json_encode($data));

function getTestResultFromFile($file)
{
    $data = explode('|', trim(file_get_contents($file)));
    $dateStart = getDateTimeFromString($data[2]);
    $dateEnd = getDateTimeFromString($data[3]);
    $duration = ($dateEnd->getTimestamp() - $dateStart->getTimestamp()) * 1000;
    $state = $data[4] === 'success' ? 'passed' : 'failed';
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
        'title' => 'Upgrade to ' . $data[1],
        'context' => '{"value": "Upgrade to ' . $data[1] . '"}',
        'skipped' => [],
        'pending' => [],
        'duration' => $duration,
        'state' => $state,
        'err' => $error,
        'date_start' => $dateStart,
        'date_end' => $dateEnd,
        'branch' => $data[0]
    ];
}

function getDateTimeFromString($datetime)
{
    return DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $datetime, new DateTimeZone('UTC'));
}
