<?php

namespace SUA\Query;

use lib\Database\Query\QueryInterface;

class DatabaseWorkerById extends QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;

    public function getQuery ()
    {
        /// return "SELECT idempleado, [numerosegurosocial],[codigoempleado],[nombrelargo],CURPI, Cast(Replace(Convert(varchar(10),fechanacimiento, 12), '', '') as varchar(10)) as fechanacimiento, CURPF ,RFC AS PrimeraParteRFC,Cast(Replace(Convert(varchar(10),fechanacimiento, 12), '', '') as varchar(10)) as fechanacimiento ,Homoclave as TerceraParteRFC, [zonasalario], [sueldointegrado] as sbc FROM [nom10001];";
        return "SELECT [idempleado], [numerosegurosocial],[codigoempleado],[nombrelargo],CURPI, Cast(Replace(Convert(varchar(10),fechanacimiento, 12), '', '') as varchar(10)) as fechanacimiento, CURPF ,RFC AS PrimeraParteRFC,Cast(Replace(Convert(varchar(10),fechanacimiento, 12), '', '') as varchar(10)) as fechanacimiento ,Homoclave as TerceraParteRFC, [zonasalario], [sueldointegrado] as sbc FROM [nom10001] WHERE ',' + :list_ids +',' LIKE '%,' + CAST([idempleado] AS VARCHAR(10)) + ',%';";
    }

    public function handle ($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $row['rfc']  = $row['PrimeraParteRFC'].'-'.$row['fechanacimiento'].'-'.$row['TerceraParteRFC'];
                $row['curp'] = $row['CURPI'].'-'.$row['fechanacimiento'].'-'.$row['CURPF'];

                $result[$db_slug][$row['idempleado']] = $row;
            }
        }
        return $result;
    }

}