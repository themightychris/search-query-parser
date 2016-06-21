<?php

class QueryParser
{
    // public static function parse($query) {
    //     return str_getcsv($query, ' ');
    // }

    const STATE_READY = 0;
    const STATE_WORD = 1;
    const STATE_QUOTED = 2;

    public static function parse($query) {
        $maxIndex = strlen($query) - 1;

        $terms = array();
        $term = '';
        $parsedIndex = 0;
        $cursorIndex = 0;
        $state = self::STATE_READY;
        $quote = null;

        while ($cursorIndex < $maxIndex) {
            $character = $query[$cursorIndex++];

            printf("%u\t%s\t%u\t%s\n", $cursorIndex - 1, $character, $state, $term);

            switch ($state) {

                case self::STATE_READY:
                    if (ctype_space($character)) {
                        // flush any buffered term
                        if ($term) {
                            $terms[] = $term;
                            $term = '';
                        }

                        // ignore space in ready state
                        continue 2;
                    }

                    if ($character == '"' || $character == '\'') {
                        // start reading quoted term
                        $quote = $character;
                        $state = self::STATE_QUOTED;
                        continue 2;
                    }

                    if ($character != ':') {
                        // start reading unquoted term
                        $state = self::STATE_WORD;
                    }

                    break;

                case self::STATE_WORD:
                    if (ctype_space($character)) {
                        // flush any buffered term
                        if ($term) {
                            $terms[] = $term;
                            $term = '';
                        }

                        // finish reading unquoted term
                        $state = self::STATE_READY;
                        continue 2;
                    }

                    if ($character == ':') {
                        // start reading a qualified term
                        $state = self::STATE_READY;
                    }

                    // continue reading unquoted term
                    break;

                case self::STATE_QUOTED:
                    if ($character == $quote) {
                        // finish reading quoted term
                        $quote = null;
                        $state = self::STATE_READY;
                        continue 2;
                    }

                    // continue reading quoted term
                    break;
            }

            // append charcter to current term if no cases continued the loop
            $term .= $character;
        }

        // flush any remaining term
        if ($term) {
            $terms[] = $term;
        }

        return $terms;
    }
}


$testString = 'ExperienceType:"Core Studio" Status:Ready "Spaced Qualifier":OK "Another Spaced Qualifier":"With a spaced value" :"Unqualified term with :" "Termless qualifier w/ :": "Bare term with :" \'single quoted string\' : qualifier: :term';
$parsed = QueryParser::parse($testString);

print("\n\nTest String: $testString\n\n");
print_r($parsed);

