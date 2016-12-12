<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;

class DbWorkerStatusDic extends QueryInterface {

    protected $queryFetchMode = \PDO::FETCH_ASSOC;
    protected $debugQuery = false;

    const DEFAULT_STATUS = 'A';
    protected $status_types = array(
        '0' => 'C',
        '1' => 'C',
        '2' => 'C',
        '3' => 'T',
    );

    public function getQuery()
    {
        return "SELECT  [IdDocumento], [IdEmpleado], [IdPeriodo], [Estado], [TimeStamp] FROM [nom10043] WHERE IdEmpleado = :id_empleado and IdPeriodo = :id_periodo";
    }

    public function handle($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows) {
            foreach ($rows as $id_row => $row)
            {
                $row['status'] = $this->getStatusType($row['Estado']);
                $row['status_date'] = $this->getStatusDate($row['TimeStamp']);
                $result[$db_slug] = $row;
            }
        }
        return $result;
    }

    public function getStatusType($index)
    {
        if (isset($this->status_types[$index]))
            return $this->status_types[$index];
        return self::DEFAULT_STATUS;
    }

    public function getStatusDate($timestamp)
    {
        return date('d-m-Y', strtotime($timestamp));
    }

}