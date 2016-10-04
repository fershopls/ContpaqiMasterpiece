<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;

class DbConceptDic extends QueryInterface {

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
                $result[$db_slug][$row['idconcepto']] = $row;
            }
        }
        return $result;
    }

}