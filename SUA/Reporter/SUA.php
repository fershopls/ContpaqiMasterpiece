<?php

namespace SUA\Reporter;

use lib\Reporter\ReporterInterface;
use lib\Util\CSV\CSV;

// Import Objects
use SUA\Reporter\DatabaseFinder;
use SUA\Reporter\MonthDaysScript;

// Import Querys
use SUA\Query\DatabaseRegPat;
use SUA\Query\DatabaseWorkers;
use SUA\Query\DatabaseWorkersAus;
use SUA\Query\DatabaseWorkersInc;
use SUA\Query\DatabaseWorkerKeys;

class SUA extends ReporterInterface {

    public function logic ()
    {
        $constant = array();
        // Inject available dbs
        $dbs = $this->import(DatabaseFinder::class, ['nomGenerales'], $this->pdo)->logic();
        $this->injectDbs($dbs);

        // Days of the month
        $constant['mdays'] = $this->import(MonthDaysScript::class, [], $this->pdo, $this->parameters)->logic()['days'];
        // Regpat
        $constant['regpat'] = $this->parameters['regpat'];

        // Get databases regpat
        $dbs_regpat = $this->query(DatabaseRegPat::class)->execute();

        // Filter databases by $parameters.regpat
        $dbs_filter = array_filter($dbs_regpat, function($e) use ($constant) {
            foreach ($e as $rp)
            {
                if ($rp == $constant['regpat'])
                    return true;
            }
            return false;
        });
        $dbs_filter = array_keys($dbs_filter);

        // Get workers in filtered dbs
        $workers = $this->query(DatabaseWorkers::class)
            ->injectDbs($dbs_filter)
            ->execute();

        // DB > WRK_ID > AUS_INT
        $aus = $this->query(DatabaseWorkersAus::class)
                    ->injectDbs($dbs_filter)
                    ->execute();

        // DB > WRK_ID > INC_INT
        $inc = $this->query(DatabaseWorkersInc::class)
                    ->injectDbs($dbs_filter)
                    ->execute();

        // DB > WRK_ID > KEY_STR
        $year = $this->parameters['date_y'];
        $month = $this->parameters['date_m'];
        $month = strlen($month)===1?'0'.$month:$month;
        $key = $this->query(DatabaseWorkerKeys::class)
                    ->injectDbs($dbs_filter)
                    ->execute([
                        'date_beg' => $year.$month.'01',
                        'date_end' => $year.$month.$constant['mdays'],
                    ]);;

        foreach ($workers as $db_slug => $rows)
        {
            foreach ($rows as $rid => $row)
            {
                $workers[$db_slug][$rid]['inc'] = isset($inc[$db_slug][$row['idempleado']])?$inc[$db_slug][$row['idempleado']]:0;
                $workers[$db_slug][$rid]['aus'] = isset($aus[$db_slug][$row['idempleado']])?$aus[$db_slug][$row['idempleado']]:0;

                $_key = isset($key[$db_slug][$row['idempleado']])?$key[$db_slug][$row['idempleado']]:['clave'=>'', 'fecha'=>''];
                $workers[$db_slug][$rid]['key'] = $_key['clave'];
                $workers[$db_slug][$rid]['date'] = $_key['fecha'];
            }
        }

        $this->dump($workers, $constant);
    }

    public function dump($result, $cons)
    {
        $csv_rows = [];
        $zones = $this->import(WorkerPayZone::class);
        foreach ($result as $db_slug => $rows)
        {
            foreach ($rows as $row)
            {
                $date = $row['key']=='B'?'':$row['date'];
                // SUA
                $payzone = $zones->get($db_slug, $row['zonasalario']);

                $days = $cons['mdays'];
                $row['sbc'] = round($row['sbc'], 2);

                $days_with_inc = $days - $row['inc'];
                $days_with_both = $days - $row['aus'] - $row['inc'];

                // Salario mínimo (Según la zona) * Columna (Días Mes) * 0.204
                $fee = $payzone * $days_with_inc * 0.204;
                $fee = round($fee, 2);

                // (Salario mínimo (Según la zona) *3 * Columna (Días Mes) )*0.015
                // (SBC * Columna (Días Mes))*0.015
                $exc = ($payzone*3*$days_with_inc*0.015) - ($row['sbc']*$days_with_inc*0.015);
                $exc = $exc < 0?0:$exc;
                $exc = round($exc, 2);

                // (SBC * Columna (Días Mes))*0.0095
                $ben = $row['sbc']*$days_with_inc*0.0095;
                $ben = round($ben, 2);

                // (SBC * Columna (Días Mes))*0.01425
                $med = $row['sbc']*$days_with_inc*0.01425;
                $med = round($med, 2);

                // (SBC * Columna (Días Mes))*.06
                $rsk = $row['sbc']*$days_with_inc*.06;
                $rsk = round($rsk, 2);

                // (SBC * Columna (Días Mes))*0.02375
                $dis = $row['sbc']*$days_with_both*0.02375;
                $dis = round($dis, 2);

                // (SBC * Columna (Días Mes))*0.01
                $day = $row['sbc']*$days_with_both*0.01;
                $day = round($day, 2);

                $total = $fee + $exc + $ben + $med + $rsk + $dis + $day;
                $total = round($total, 2);

                $csv_row = array(
                    'regpat' => $cons['regpat'],
                    'nss' => $row['numerosegurosocial'],
                    'code' => $row['codigoempleado'],
                    'name' => $row['nombrelargo'],
                    'rfc' => $row['rfc'],
                    'curp' => $row['curp'],
                    'key' => $row['key'],
                    'date' => $date,

                    // BEG NOMINAS
                    'nom_sbc' => $row['sbc'],
                    'nom_mdays' => $cons['mdays'],
                    'nom_inc' => $row['inc'],
                    'nom_aus' => $row['aus'],
                    'nom_cdays' => ($cons['mdays'] - $row['aus']),
                        // BEG SUA
                        'nom_sbc_sua' => '',
                        'nom_mdays_sua' => '',
                        'nom_inc_sua' => '',
                        'nom_aus_sua' => '',
                        'nom_cdays_sua' => '',
                        // END SUA
                        // BEG DIF
                        'nom_sbc_dif' => '',
                        'nom_cdays_dif' => '',
                        // END DIF
                    // END NOMINAS

                    // BEG FIXED FEE
                    'fee_nom' => $fee,
                    'fee_sua' => '',
                    'fee_dif' => '',
                    // END FIXED FEE

                    // BEG EXCESS
                    'exc_nom' => $exc,
                    'exc_sua' => '',
                    'exc_dif' => '',
                    // END EXCESS

                    // BEG BENEFIT
                    'ben_nom' => $ben,
                    'ben_sua' => '',
                    'ben_dif' => '',
                    // END BENEFIT

                    // BEG MEDIAL
                    'med_nom' => $med,
                    'med_sua' => '',
                    'med_dif' => '',
                    // END MEDIAL

                    // BEG RISK
                    'rsk_nom' => $rsk,
                    'rsk_sua' => '',
                    'rsk_dif' => '',
                    // END RISK

                    // BEG DISABILITY
                    'dis_nom' => $dis,
                    'dis_sua' => '',
                    'dis_dif' => '',
                    // END DISABILITY

                    // BEG DAYCARE
                    'day_nom' => $day,
                    'day_sua' => '',
                    'day_dif' => '',
                    // END DAYCARE

                    // BEG TOTAL
                    'total_nom' => $total,
                    'total_sua' => '',
                    'total_dif' => '',
                    // END TOTAL
                );

                $csv_rows[] = $csv_row;

                if ($row['key'] == 'B')
                {
                    $csv_rows[] = array(
                        'regpat' => '',
                        'nss' => '',
                        'code' => '',
                        'name' => '',
                        'rfc' => '',
                        'curp' => '',
                        'key' => '',
                        'date' => $row['date'],

                        // BEG NOMINAS
                        'nom_sbc' => '',
                        'nom_mdays' => '',
                        'nom_inc' => '',
                        'nom_aus' => '',
                        'nom_cdays' => '',
                        // BEG SUA
                        'nom_sbc_sua' => '',
                        'nom_mdays_sua' => '',
                        'nom_inc_sua' => '',
                        'nom_aus_sua' => '',
                        'nom_cdays_sua' => '',
                        // END SUA
                        // BEG DIF
                        'nom_sbc_dif' => '',
                        'nom_cdays_dif' => '',
                        // END DIF
                        // END NOMINAS

                        // BEG FIXED FEE
                        'fee_nom' => '',
                        'fee_sua' => '',
                        'fee_dif' => '',
                        // END FIXED FEE

                        // BEG EXCESS
                        'exc_nom' => '',
                        'exc_sua' => '',
                        'exc_dif' => '',
                        // END EXCESS

                        // BEG BENEFIT
                        'ben_nom' => '',
                        'ben_sua' => '',
                        'ben_dif' => '',
                        // END BENEFIT

                        // BEG MEDIAL
                        'med_nom' => '',
                        'med_sua' => '',
                        'med_dif' => '',
                        // END MEDIAL

                        // BEG RISK
                        'rsk_nom' => '',
                        'rsk_sua' => '',
                        'rsk_dif' => '',
                        // END RISK

                        // BEG DISABILITY
                        'dis_nom' => '',
                        'dis_sua' => '',
                        'dis_dif' => '',
                        // END DISABILITY

                        // BEG DAYCARE
                        'day_nom' => '',
                        'day_sua' => '',
                        'day_dif' => '',
                        // END DAYCARE

                        // BEG TOTAL
                        'total_nom' => '',
                        'total_sua' => '',
                        'total_dif' => '',
                        // END TOTAL
                    );
                }
            }
        }

        file_put_contents(MASTER_DIR . '/test.csv', $this->createCsv($this->getCSVHeaders(), $csv_rows, $this->getCSVFix()));
    }

    public function getCSVHeaders()
    {
        return array(
            'regpat' => 'Registro Patronal',
            'nss' => 'Numero Seguro Social',
            'code' => 'Codigo Empleado',
            'name' => 'Nombre Empleado',
            'rfc' => 'RFC',
            'curp' => 'CURP',
            'key' => 'Clave',
            'date' => 'Fecha',

            // BEG NOMINAS
            'nom_sbc' => 'SBC',
            'nom_mdays' => 'Dias del Mes',
            'nom_inc' => 'Inc.',
            'nom_aus' => 'Aus.',
            'nom_cdays' => 'Dias Cotizados',
                // BEG SUA
                'nom_sbc_sua' => 'SBC',
                'nom_mdays_sua' => 'Dias del Mes',
                'nom_inc_sua' => 'Inc.',
                'nom_aus_sua' => 'Aus.',
                'nom_cdays_sua' => 'Dias Cotizados',
                // END SUA
                // BEG DIF
                'nom_sbc_dif' => 'SBC',
                'nom_cdays_dif' => 'Dias Cotizados',
                // END DIF
            // END NOMINAS

            // BEG FIXED FEE
            'fee_nom' => 'NOMINAS',
            'fee_sua' => 'SUA',
            'fee_dif' => 'DIFERENCIA',
            // END FIXED FEE

            // BEG EXCESS
            'exc_nom' => 'NOMINAS',
            'exc_sua' => 'SUA',
            'exc_dif' => 'DIFERENCIA',
            // END EXCESS

            // BEG BENEFIT
            'ben_nom' => 'NOMINAS',
            'ben_sua' => 'SUA',
            'ben_dif' => 'DIFERENCIA',
            // END BENEFIT

            // BEG MEDIAL
            'med_nom' => 'NOMINAS',
            'med_sua' => 'SUA',
            'med_dif' => 'DIFERENCIA',
            // END MEDIAL

            // BEG RISK
            'rsk_nom' => 'NOMINAS',
            'rsk_sua' => 'SUA',
            'rsk_dif' => 'DIFERENCIA',
            // END RISK

            // BEG DISABILITY
            'dis_nom' => 'NOMINAS',
            'dis_sua' => 'SUA',
            'dis_dif' => 'DIFERENCIA',
            // END DISABILITY

            // BEG DAYCARE
            'day_nom' => 'NOMINAS',
            'day_sua' => 'SUA',
            'day_dif' => 'DIFERENCIA',
            // END DAYCARE

            // BEG TOTAL
            'total_nom' => 'NOMINAS',
            'total_sua' => 'SUA',
            'total_dif' => 'DIFERENCIA',
            // END TOTAL
        );
    }

    public function getCSVFix()
    {
        return array(
            'regpat' => '',
            'nss' => '',
            'code' => '',
            'name' => '',
            'rfc' => '',
            'curp' => '',
            'key' => '',
            'date' => '',

            // BEG NOMINAS
            'nom_sbc' => 'Bases del cálculo',
            'nom_mdays' => '',
            'nom_inc' => '',
            'nom_aus' => '',
            'nom_cdays' => '',
            // BEG SUA
            'nom_sbc_sua' => '',
            'nom_mdays_sua' => '',
            'nom_inc_sua' => '',
            'nom_aus_sua' => '',
            'nom_cdays_sua' => '',
            // END SUA
            // BEG DIF
            'nom_sbc_dif' => '',
            'nom_cdays_dif' => '',
            // END DIF
            // END NOMINAS

            // BEG FIXED FEE
            'fee_nom' => 'Cuota Fija',
            'fee_sua' => '',
            'fee_dif' => '',
            // END FIXED FEE

            // BEG EXCESS
            'exc_nom' => 'Excedente',
            'exc_sua' => '',
            'exc_dif' => '',
            // END EXCESS

            // BEG BENEFIT
            'ben_nom' => 'Prestaciones',
            'ben_sua' => '',
            'ben_dif' => '',
            // END BENEFIT

            // BEG MEDIAL
            'med_nom' => 'Gastos Médicos',
            'med_sua' => '',
            'med_dif' => '',
            // END MEDIAL

            // BEG RISK
            'rsk_nom' => 'Riesgo Trabajo',
            'rsk_sua' => '',
            'rsk_dif' => '',
            // END RISK

            // BEG DISABILITY
            'dis_nom' => 'Invalidez y Vida',
            'dis_sua' => '',
            'dis_dif' => '',
            // END DISABILITY

            // BEG DAYCARE
            'day_nom' => 'Guarderías',
            'day_sua' => '',
            'day_dif' => '',
            // END DAYCARE

            // BEG TOTAL
            'total_nom' => 'Totales Liquidación',
            'total_sua' => '',
            'total_dif' => '',
            // END TOTAL
        );
    }

}