<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabaseAvailable extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT [RutaEmpresa], [NombreEmpresa] FROM [NOM10000];";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][$row['RutaEmpresa']] = $row['NombreEmpresa'];
            }
        }
        return $result;
    }

}