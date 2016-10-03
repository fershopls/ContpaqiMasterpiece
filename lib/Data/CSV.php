<?php

namespace lib\Data;

class CSV {
    const CHAR_SEPARATOR = ",";
    const CHAR_ENCLOSURE = "\"";
    const CHAR_ENDOFLINE = PHP_EOL;

    protected $body = "";

    public function write ($array_bidimensional_rows)
    {
        foreach ($array_bidimensional_rows as $row)
        {
            $this->writerow($row);
        }
    }

    public function writerow (Array $array_rows) {
        $line = "";
        foreach ($array_rows as $raw) {
            $row = str_replace(self::CHAR_ENCLOSURE, "\\".self::CHAR_ENCLOSURE, $raw);
            $row = self::CHAR_ENCLOSURE. $row .self::CHAR_ENCLOSURE;
            $line .= $row . self::CHAR_SEPARATOR;
        }
        $this->body .= preg_replace("/\,$/", self::CHAR_ENDOFLINE, $line);
    }

    public function get () {
        return $this->body;
    }
}