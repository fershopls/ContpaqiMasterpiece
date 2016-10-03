<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabaseWorkerKeys extends QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;

    public function getQuery ()
    {
        return "SELECT [idempleado], [clavebajareingreso] as clave, [fecha] FROM [nom10020] WHERE [fecha] BETWEEN :date_beg and :date_end ORDER BY idempleado, fecha;";
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