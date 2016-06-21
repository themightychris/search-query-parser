<?php

set_time_limit(1);

require('./QueryParser.php');
QueryParser::$debug = true;


$tests = [
    'ExperienceType:"Core Studio"'                      => [ 'qualifier' => 'ExperienceType',           'term' => 'Core Studio' ],
    'Status:Ready'                                      => [ 'qualifier' => 'Status',                   'term' => 'Ready' ],
    '"Spaced Qualifier":OK'                             => [ 'qualifier' => 'Spaced Qualifier',         'term' => 'OK' ],
    '"Another Spaced Qualifier":"With a spaced value"'  => [ 'qualifier' => 'Another Spaced Qualifier', 'term' => 'With a spaced value' ],
    ':"Unqualified term with :"'                        => [ 'qualifier' => null,                       'term' => 'Unqualified term with :' ],
    '"Termless qualifier w/ :":'                        => [ 'qualifier' => 'Termless qualifier w/ :',  'term' => null ],
    '"Bare term with :"'                                => [ 'qualifier' => null,                       'term' => 'Bare term with :' ],
    '\'single quoted string\''                          => [ 'qualifier' => null,                       'term' => 'single quoted string' ],
    ':'                                                 => null,
    'qualifier:'                                        => [ 'qualifier' => 'qualifier',                'term' => null ],
    ':term'                                             => [ 'qualifier' => null,                       'term' => 'term' ],
    'garbage:after:'                                    => [ 'qualifier' => 'garbage',                  'term' => 'after:' ],
    ':garbage:before'                                   => [ 'qualifier' => null,                       'term' => 'garbage:before' ],
    'middle"quote'                                      => [ 'qualifier' => null,                       'term' => 'middle"quote' ]
];

$inputTerms = array_keys($tests);

$parsed = QueryParser::parseString("  \t".implode(' ', $inputTerms));

$passedCount = 0;
$failedCount = 0;
$nullCount = 0;
foreach ($inputTerms AS $i => $inputTerm) {
    printf("\nInput #%u: %s\n\n", $i, $inputTerm);

    $resultIndex = $i - $nullCount;

    if ($resultIndex >= count($parsed)) {
        $passed = false;

        printf("\End of results\n");
    } elseif (!$tests[$inputTerm]) {
        $passed = true;
        $nullCount++;

        printf("\tShould not have result\n");
    } else {
        printf("\tExpected qualifier: %s\n", var_export($tests[$inputTerm]['qualifier'], true));
        printf("\t  Parsed qualifier: %s\n", var_export($parsed[$resultIndex]['qualifier'], true));
        printf("\t     Expected term: %s\n", var_export($tests[$inputTerm]['term'], true));
        printf("\t       Parsed term: %s\n", var_export($parsed[$resultIndex]['term'], true));
        $passed = (
            $tests[$inputTerm]['qualifier'] === $parsed[$resultIndex]['qualifier']
            && $tests[$inputTerm]['term'] === $parsed[$resultIndex]['term']
        );

    }

    printf("\t            Result: %s\n", $passed ? 'PASSED' : 'FAILED');

    if ($passed) {
        $passedCount++;
    } else {
        $failedCount++;
    }
}

printf("\n\nPassed: %u\nFailed: %u\n", $passedCount, $failedCount);



// print_r(['cursor' => $this->cursor, 'character' => $this->query[$this->cursor], 'remaining' => substr($this->query, $this->cursor), 'qualifier' => $this->qualifier, 'term' => $this->term])