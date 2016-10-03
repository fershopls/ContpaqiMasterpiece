<?php

namespace SUA\Reporter;

use lib\Reporter\ReporterInterface;

class MonthDaysScript extends ReporterInterface {

    public function logic ()
    {
        $index = $this->getMonthDaysIndex($this->parameters['date_y']);
        $month_i = preg_replace("/^0/", "", $this->parameters['date_m']);
        return $index[$month_i];
    }

    public function getMonthDaysIndex ($year = 2016)
    {
        $months = array(
            1 =>  ['string'=>'Enero', 'days'=>31,],
            2 =>  ['string'=>'Febrero', 'days'=>28,],
            3 =>  ['string'=>'Marzo', 'days'=>31,],
            4 =>  ['string'=>'Abril', 'days'=>30,],
            5 =>  ['string'=>'Mayo', 'days'=>31,],
            6 =>  ['string'=>'Junio', 'days'=>30,],
            7 =>  ['string'=>'Julio', 'days'=>31,],
            8 =>  ['string'=>'Agosto', 'days'=>31,],
            9 =>  ['string'=>'Septiembre', 'days'=>30,],
            10 => ['string'=>'Octubre', 'days'=>31,],
            11 => ['string'=>'Noviembre', 'days'=>30,],
            12 => ['string'=>'Diciembre', 'days'=>31,],
        );

        // https://support.microsoft.com/en-us/kb/214019
        if ($year%4 == 0 && $year%100 != 0)
        {
            // Year is lead
            $months[2]['days'] = 29;
        }
        return $months;
    }

}