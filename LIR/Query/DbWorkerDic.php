<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;

class DbWorkerDic extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT idempleado, nombrelargo, codigoempleado, bajaimss, campoextra1 FROM nom10001 ORDER BY codigoempleado;";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][$row['idempleado']] = $row;
            }
        }
        return $result;
    }

}