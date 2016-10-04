<?php

namespace LIR\Util;

class StringKey {

    protected $string_raw;

    public static function get ($string)
    {
        #$string = self::string_raw;
        $string = self::clearGrammar($string);
        $string = strtolower($string);
        $words = self::splitIntoWords($string);
        foreach ($words as $i => $word)
        {
            $word = self::clearPlurals($word);
            $words[$i] = $word;
        }
        return implode('', $words);
    }

    public static function clearGrammar($string)
    {
        $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
            ','=>'', '.'=>'', ':'=>'', ';'=>'', '%'=>'', '$'=>'', '/'=>'', '-'=>'', '('=>'', ')'=>'', '['=>'', ']'=>'');
        return strtr($string, $unwanted_array);
    }

    public static function splitIntoWords ($string) {
        return preg_split('/\s/i', $string);
    }

    public static function clearPlurals ($string)
    {
        return preg_replace("/e?s?$/i", "", $string);
    }
}