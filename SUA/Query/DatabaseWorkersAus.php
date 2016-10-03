<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabaseWorkersAus extends QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;

    public function getQuery ()
    {
        return "SELECT idempleado, SUM(valor) as aus FROM nom10009 group by idempleado;";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][$row['idempleado']] = $row['aus'];
            }
        }
        return $result;
    }

}