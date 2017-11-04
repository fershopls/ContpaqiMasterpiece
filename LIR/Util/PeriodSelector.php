<?php

namespace LIR\Util;

class PeriodSelector {

    protected $begin;
    protected $end;
    

    public function setDates ($begin, $end)
    {
        $this->begin = strtotime($begin);
        $this->end = strtotime($end);
    }
    
    public function get ($db_period_dic)
    {
        // idperiodo, ejercicio, idtipoperiodo, numeroperiodo, fechainicio, fechafin
        $beg = $this->begin;
        $end = $this->end;
        $format = 'Y-m-d H:i';
        echo "[DATE] BEG:".date($format,$beg)."\n[DATE] END:".date($format,$end)."\n";
        $stack = array();

        foreach ($db_period_dic as $db_slug => $period)
        {
            $stack[$db_slug] = [];
            foreach ($period as $id => $p)
            {
                $add = false;
                $_beg = strtotime($p['fechainicio']);
                $_end = strtotime($p['fechafin']);

                // Include all period with matching dates
                if ($beg >= $_beg && $beg <= $_end
                || $end >= $_beg && $end <= $_end)
                    $add = true;

                // Add it to stack
                if ($add)
                    $stack[$db_slug][$id] = [
                        'begin' => date($format, $_beg),
                        'end' => date($format, $_end),
                    ];
            }

            // Select for each db the first begin and the last end dates.
            $_keys = array_keys($stack[$db_slug]);
            $stack[$db_slug] = array(
                'begin' => isset($stack[$db_slug][$_keys[0]]['begin'])?$stack[$db_slug][$_keys[0]]['begin']:null,
                'end' => isset($stack[$db_slug][$_keys[count($_keys)-1]]['end'])?$stack[$db_slug][$_keys[count($_keys)-1]]['end']:null,
            );
        }
        return $stack;
    }
    
}