<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;

// Util
use LIR\Util\StringKey;

class DbKeyConceptDic extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT idconcepto, descripcion, tipoconcepto FROM nom10004 ORDER BY tipoconcepto;";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $string = StringKey::get($row['descripcion']);
                $result[$string] = $row;
            }
        }
        return $result;
    }

}