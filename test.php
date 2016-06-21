<?php

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
    ':'                                                 => [ 'qualifier' => null,                       'term' => ':' ],
    'qualifier:'                                        => [ 'qualifier' => 'qualifier',                'term' => null ],
    ':term'                                             => [ 'qualifier' => null,                       'term' => 'term' ],
    'garbage:after:'                                    => [ 'qualifier' => 'garbage',                  'term' => 'after:' ],
    ':garbage:before'                                   => [ 'qualifier' => null,                       'term' => 'garbage:before' ]
];

$inputTerms = array_keys($tests);

$parsed = QueryParser::parseString(implode(' ', $inputTerms));

$passedCount = 0;
$failedCount = 0;
foreach ($inputTerms AS $i => $inputTerm) {
    printf("\nInput #%u: %s\n\n", $i, $inputTerm);

    if ($i >= count($parsed)) {
        $passed = false;

        printf("\tNot parsed\n");
    } else {
        // TODO: remove this conversion after parser updated
        if (is_string($parsed[$i])) {
            printf("\t        Raw result: %s\n", var_export($parsed[$i], true));

            @list ($qualifier, $term) = explode(':', $parsed[$i], 2);
            if (!$term) {
                $term = $qualifier;
                $qualifier = null;
            }

            $parsed[$i] = [ 'qualifier' => $qualifier ?: null, 'term' => $term ?: null ];
        }

        printf("\tExpected qualifier: %s\n", var_export($tests[$inputTerm]['qualifier'], true));
        printf("\t  Parsed qualifier: %s\n", var_export($parsed[$i]['qualifier'], true));
        printf("\t     Expected term: %s\n", var_export($tests[$inputTerm]['term'], true));
        printf("\t       Parsed term: %s\n", var_export($parsed[$i]['term'], true));
        $passed = (
            $tests[$inputTerm]['qualifier'] === $parsed[$i]['qualifier']
            && $tests[$inputTerm]['term'] === $parsed[$i]['term']
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