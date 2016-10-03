<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabaseWorkersInc extends QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;

    public function getQuery ()
    {
        return "SELECT [idempleado], [diasautorizados] as inc, [fechainicio] FROM [nom10018];";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][$row['idempleado']] = $row['inc'];
            }
        }
        return $result;
    }

}