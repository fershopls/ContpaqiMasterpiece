<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabasePeriodDates extends QueryInterface {

    public function getQuery ()
    {
        // return "SELECT [idperiodo] FROM [nom10002] WHERE [fechainicio] between :date_beg and :date_end;";
        return "SELECT [idperiodo] FROM [nom10002] WHERE [fechafin] between :date_beg and :date_end;";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $result[$db_slug][] = $row['idperiodo'];
            }
        }
        return $result;
    }

}