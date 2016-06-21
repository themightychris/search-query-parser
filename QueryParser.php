<?php

class QueryParser
{
    public static $debug = false;
    public static $quotes = '\'"‘’“”';

    // query state
    protected $query;
    protected $cursorMax;
    protected $terms = [];

    // parser internal state
    protected $qualifier = '';
    protected $term = '';
    protected $cursor = 0;
    protected $state = self::STATE_READY;

    // parser state modes
    const STATE_READY = 0;
    const STATE_QUALIFIER = 1;
    const STATE_TERM = 2;


    function __construct($query)
    {
        $this->query = $query;
        $this->cursorMax = strlen($query) - 1;
    }

    public static function parseString($query)
    {
        return (new static($query))->parse();
    }

    protected static function isQuote($character)
    {
        return strpos(static::$quotes, $character) !== false;
    }

    protected static function isSpace($character)
    {
        return ctype_space($character);
    }

    protected static function isDelimiter($character)
    {
        return $character == ':';
    }

    protected function parse()
    {
        static::$debug && printf("Parsing query: %s\n\n", $this->query);

        while ($this->cursor <= $this->cursorMax) {
            static::$debug && printf("%u\t%s\t%u\t%s\t%s\n", $this->cursor, json_encode($this->query[$this->cursor]), $this->state, $this->qualifier, $this->term);

            switch ($this->state) {

                case self::STATE_READY:
                    $character = $this->query[$this->cursor];

                    if (static::isSpace($character)) {
                        // ignore space in ready state
                        $this->cursor++;
                        break;
                    }

                    if (static::isDelimiter($character)) {
                        // ignore delimiter and jump to term mode
                        $this->state = self::STATE_TERM;
                        $this->cursor++;
                        break;
                    }

                    // begin in qualifier mode and continue scan without advancing cursor
                    $this->state = self::STATE_QUALIFIER;
                    break;

                case self::STATE_QUALIFIER:

                    $this->qualifier = $this->readSubstring();

                    if ($this->cursor <= $this->cursorMax && static::isDelimiter($this->query[$this->cursor])) {
                        // consume delimeter and prepare to read term
                        $this->state = self::STATE_TERM;
                        $this->cursor++;
                        break;
                    }

                    // if there is no delimiter coming, this was a term
                    $this->term = $this->qualifier;
                    $this->qualifier = '';
                    $this->flushTerm();
                    break;

                case self::STATE_TERM:
                    $this->term = $this->readSubstring(false);
                    $this->flushTerm();
                    break;
            }
        }

        return $this->terms;
    }

    protected function readSubstring($stopAtDelimiter = true)
    {
        $string = '';
        $quote = null;

        while ($this->cursor <= $this->cursorMax) {
            $character = $this->query[$this->cursor];

            if (!$string && static::isQuote($character)) {
                // advance cursor and begin quote
                $this->cursor++;
                $quote = $character;
            } elseif ($character === $quote) {
                // advance cursor and finish string
                $this->cursor++;
                break;
            } elseif (!$quote && (static::isSpace($character) || ($stopAtDelimiter && static::isDelimiter($character)))) {
                // finish string without advancing cursor
                break;
            } else {
                // advance cursor and consume character
                $this->cursor++;
                $string .= $character;
            }
        }

        static::$debug && printf("Read substring: %s\n", var_export($string, true));
        return $string;
    }

     protected function flushTerm()
     {
        if ($this->term || $this->qualifier) {
            $this->terms[] = [
                'qualifier' => $this->qualifier ?: null,
                'term' => $this->term ?: null
            ];

            $this->qualifier = '';
            $this->term = '';

            static::$debug && printf("Flushed term: %s\n", print_r($this->terms[count($this->terms)-1], true));
        }

        $this->state = self::STATE_READY;
     }
}