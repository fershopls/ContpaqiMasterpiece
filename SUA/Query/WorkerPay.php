<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class WorkerPay extends QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;

    public function getQuery ()
    {
        return "SELECT TOP 1 [valor] FROM [nom10029] WHERE [numerotabla] = :numerotabla AND [numerocolumna] = :numerocolumna ORDER BY [valor] desc";
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