<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabaseRegPat extends QueryInterface {

    public function getQuery ()
    {
        return "SELECT cidregistropatronal,cregistroimss FROM NOM10035 WHERE cregistroimss != '00000000000' ORDER BY cidregistropatronal";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][] = $row['cregistroimss'];
            }
        }
        return $result;
    }

}