<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabaseWorkerMovementDates extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT [idempleado] FROM [nom10007] WHERE ',' + :list_ids +',' LIKE '%,' + CAST(idperiodo AS VARCHAR(10)) + ',%' GROUP BY idempleado;";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            $result[$db_slug] = array();
            foreach ($rows as $row)
            {
                $result[$db_slug][] = $row['idempleado'];
            }
        }
        return $result;
    }

}