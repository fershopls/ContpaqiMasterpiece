<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;

class DbPeriodTypeDic extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT idtipoperiodo, nombretipoperiodo FROM nom10023 ORDER BY idtipoperiodo;";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][$row['idtipoperiodo']] = $row;
            }
        }
        return $result;
    }

}