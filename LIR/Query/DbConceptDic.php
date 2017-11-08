<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;
use LIR\Util\StringKey;

class DbConceptDic extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT idconcepto, descripcion, tipoconcepto FROM nom10004 ORDER BY tipoconcepto;";
    }

    public function handle ($query_object)
    {
        $result_cpt = [];
        $result_key = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result_cpt[$db_slug][$row['idconcepto']] = $row;
                $result_key[StringKey::get($row['descripcion'])] = $row;
            }
        }

        return array($result_cpt, $result_key);
    }

}