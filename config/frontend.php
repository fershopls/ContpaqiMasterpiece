<?php

return array(
    //
    // SUA FORM
    //

    'SUA' => array(
        'FORM' => array(
            'texts' => array(
                'filename' => 'Nombre del Reporte',
                'regpat' => 'Registro Patronal',
                'date_type' => 'Periodo',
                'date_m' => 'Mes de Inicio',
                'date_y' => 'Año de Inicio',
                'email' => 'Correo Electrónico',
            ),

            'fields' => array(
                ['name' => 'filename'],
                ['name' => 'regpat', 'type'=>'select', 'source'=>'regpat'],
                ['name' => 'date_type', 'type'=>'select', 'value'=>array(
                    ['key' => 'B', 'value'=>'Bimestral'],
                    ['key' => 'M', 'value'=>'Mensual'],
                )],
                ['name'=>'date_m', 'type'=>'select', 'source'=>'months'],
                ['name'=>'date_y', 'type'=>'number', 'value'=>date('Y')],
                ['name'=>'email', 'type'=>'email'],
            ),

            'sources' => array(
                'months' => (function($settings){
                    $items = array();
                    foreach (range(1,12) as $i)
                    {
                        $items[$i] = ['key'=>$i, 'value'=>$i];
                    }
                    return $items;
                }),
                'regpat' => (function($settings) {
                    $REGPAT_PATH = get_dir('cache', $settings).'/regpats.json';
                    $AVAILABLE_REGPAT_ARRAY =  file_exists($REGPAT_PATH)?json_decode(file_get_contents($REGPAT_PATH), true):[];
                    $values = array();
                    foreach ($AVAILABLE_REGPAT_ARRAY as $key => $val)
                        array_push($values, array('key'=>$key, 'value'=>$val));
                    return $values;
                }),
            ),
        ),
    ),


    //
    // LIR FORM
    //

    'LIR' => array(
        'INCLUDE' => array(
            ['src'=>'lir/RegpatFilter.php'],
        ),
        'FORM' => array(
            'texts' => array(
                'filename' => 'Nombre de Archivo',
                'regpat' => 'Registro Patronal',
                'regpat_info' => 'Última vez actualizados:',
                'regpat_update' => '¿Actualizar Registros Patronales?',
                'database' => 'Empresa',
                'exercise' => 'Ejercicio',
                'period_type' => 'Tipo de Periodo',
                'date_begin' => 'Fecha de Inicio',
                'date_end' => 'Fecha Final',
                'options[worker_net]' => 'Inc. trabajadores con neto 0',
                'options[worker_down]' => 'Inc. trabajadores con baja',
            ),
            'fields' => array(
                ['name' => 'filename'],
                ['name' => 'regpat', 'type'=>'select', 'source'=>'regpat'],
                ['name' => 'regpat_info', 'type'=>'textarea', 'source'=>'regpat_info', 'attr'=>['readonly'=>'readonly'] ],
                ['name' => 'regpat_update', 'type'=>'button', 'source'=>'regpat_update', 'attr'=>['onclick'=>'window.location = "lir/regpat_refresh.php";'] ],
                ['name' => 'database', 'type'=>'select', 'source'=>'database'],
                ['name' => 'exercise', 'type'=>'number', 'value'=>date('Y')],
                ['name' => 'period_type', 'type'=>'select', 'value'=> [
                    ['key' => '', 'value' => 'Todos los Periodos'],
                    ['key' => 'semanal', 'value' => 'Semanal'],
                    ['key' => 'catorcenal', 'value' => 'Catorcenal'],
                    ['key' => 'mensual', 'value' => 'Mensual'],
                ]],
                ['name' => 'date_begin', 'type'=>'date'],
                ['name' => 'date_end', 'type'=>'date'],

                // Extra
                ['name' => 'email', 'type'=>'email'],
                ['name' => 'options[worker_net]', 'type'=>'checkbox'],
                ['name' => 'options[worker_down]', 'type'=>'checkbox'],
            ),
            'sources' => array(
                'regpat_info' => (function($settings){
                    $file = get_dir('request', $settings).'/regpat.json';
                    if (file_exists($file))
                    {
                        $_file = json_decode(file_get_contents($file), true);
                        $time = isset($_file['time_done'])?$_file['time_done']:(isset($_file['time'])?$_file['time']:'Unknown');
                        $did_crash = isset($_file['time_done'])?'':'Buscando registros patronales desde: ';
                        $date = date('H:i:s D d/m/Y', $time);
                        $averange = $did_crash!=''?'':PHP_EOL.'(Tiempo promedio: '.round(($time - $_file['time'])/60, 2).' mins.)';
                        return $did_crash.$date.$averange;
                    } else {
                        return 'Unknown';
                    }
                }),
                'regpat_update' => (function($settings){
                    return 'Update Regpats';
                }),
                'regpat' => (function($settings) {
                    $REGPAT_PATH = get_dir('cache', $settings).'/regpats.json';
                    $AVAILABLE_REGPAT_ARRAY =  file_exists($REGPAT_PATH)?json_decode(file_get_contents($REGPAT_PATH), true):false;
                    $values = $AVAILABLE_REGPAT_ARRAY==false?array(['key'=>'','value'=>'Cache file not found!']):array(['key'=>'', 'value'=>'None']);
                    if (is_array($AVAILABLE_REGPAT_ARRAY))
                    {
                        foreach ($AVAILABLE_REGPAT_ARRAY as $key => $val)
                            array_push($values, array('key'=>$key, 'value'=>$val));
                    }
                    return $values;
                }),
                'database' => (function($settings) {
                    $DBS_PATH = get_dir('cache', $settings).'/databases.json';
                    $AVAILABLE_DBS_ARRAY = file_exists($DBS_PATH)?json_decode(file_get_contents($DBS_PATH), true):false;
                    $values = array(
                        ['key' => '', 'value' => 'Todas las Empresas', 'attr'=>['id'=>'all']]
                    );
                    if (is_array($AVAILABLE_DBS_ARRAY)){
                        foreach ($AVAILABLE_DBS_ARRAY as $db_slug => $row)
                            array_push($values, array('key'=>$db_slug, 'value'=>$row['string'], 'attr'=>['data-regpat'=>join($row['regpats'], ',')]));
                    }
                    return $values;
                }),
            ),
        ),
    ),
);