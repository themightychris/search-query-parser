<?php

class QueryParser
{
    // query state
    protected $query;
    protected $cursorMax;
    protected $terms = [];

    // parser internal state
    protected $qualifier = '';
    protected $term = '';
    protected $cursor = 0;
    protected $quote = null;
    protected $state = self::STATE_READY;

    // parser state modes
    const STATE_READY = 0;
    const STATE_WORD = 1;
    const STATE_QUOTED = 2;


    function __construct($query)
    {
        $this->query = $query;
        $this->cursorMax = strlen($query) - 1;
    }

    public static function parseString($query)
    {
        return (new static($query))->parse();
    }

    protected function parse()
    {
        while ($this->cursor < $this->cursorMax) {
            $character = $this->query[$this->cursor++];

            printf("%u\t%s\t%u\t%s\n", $this->cursor - 1, $character, $this->state, $this->term);

            switch ($this->state) {

                case self::STATE_READY:
                    if (ctype_space($character)) {
                        // flush any buffered term
                        if ($this->term) {
                            $this->terms[] = $this->term;
                            $this->term = '';
                        }

                        // ignore space in ready state
                        continue 2;
                    }

                    if ($character == '"' || $character == '\'') {
                        // start reading quoted term
                        $this->quote = $character;
                        $this->state = self::STATE_QUOTED;
                        continue 2;
                    }

                    if ($character != ':') {
                        // start reading unquoted term
                        $this->state = self::STATE_WORD;
                    }

                    break;

                case self::STATE_WORD:
                    if (ctype_space($character)) {
                        // flush any buffered term
                        if ($this->term) {
                            $this->terms[] = $this->term;
                            $this->term = '';
                        }

                        // finish reading unquoted term
                        $this->state = self::STATE_READY;
                        continue 2;
                    }

                    if ($character == ':') {
                        // start reading a qualified term
                        $this->state = self::STATE_READY;
                    }

                    // continue reading unquoted term
                    break;

                case self::STATE_QUOTED:
                    if ($character == $this->quote) {
                        // finish reading quoted term
                        $this->quote = null;
                        $this->state = self::STATE_READY;
                        continue 2;
                    }

                    // continue reading quoted term
                    break;
            }

            // append charcter to current term if no cases continued the loop
            $this->term .= $character;
        }

        // flush any remaining term
        if ($this->term) {
            $this->terms[] = $this->term;
        }

        return $this->terms;
    }
}


$testString = 'ExperienceType:"Core Studio" Status:Ready "Spaced Qualifier":OK "Another Spaced Qualifier":"With a spaced value" :"Unqualified term with :" "Termless qualifier w/ :": "Bare term with :" \'single quoted string\' : qualifier: :term';
$parsed = QueryParser::parseString($testString);

print("\n\nTest String: $testString\n\n");
print_r($parsed);

