<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class WorkerZone extends QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;

    public function getQuery ()
    {
        return "SELECT [numerotabla], [numerocolumna], [descripcion], [tipocolumna] FROM [nom10028] WHERE descripcion = :zone";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug] = $row;
            }
        }
        return $result;
    }

}