<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;

class DbPeriodDic extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT idperiodo, ejercicio, idtipoperiodo, numeroperiodo, fechainicio, fechafin FROM nom10002 ORDER BY idtipoperiodo, ejercicio;";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][$row['idperiodo']] = $row;
            }
        }
        return $result;
    }

}