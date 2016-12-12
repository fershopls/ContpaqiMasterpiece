<?php

namespace LIR\Query;

use lib\Database\Query\QueryInterface;

class DbWorkerDic extends QueryInterface
{

    protected $payment_types = array(
        '01' => 'efectivo',
        '02' => 'cheque nominativo',
        '03' => 'transferencia electrónica de fondos',
        '04' => 'tarjeta de crédito',
        '05' => 'monedero electrónico',
        '06' => 'dinero electrónico',
        '08' => 'vales de despensa',
        '28' => 'tarjeta de débito',
        '29' => 'tarjeta de servicio',
        '99' => 'otros',
    );

    public function getQuery()
    {
        return "SELECT idempleado, nombrelargo, codigoempleado, bajaimss, fechabaja, campoextra1, formapago FROM nom10001 ORDER BY codigoempleado;";
    }

    public function handle($query_object)
    {
        $result = [];
        foreach ($query_object as $db_slug => $rows) {
            foreach ($rows as $row) {
                $row['payment_type'] = $this->getPaymentType($row['formapago']);
                $result[$db_slug][$row['idempleado']] = $row;
            }
        }
        return $result;
    }

    public function getPaymentType($index)
    {
        if (isset($this->payment_types[$index]))
            return $this->payment_types[$index];
        return '';
    }

}