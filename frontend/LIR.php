<?php
require_once realpath(__DIR__) . '/../bootstrap.php';

$REQUESTS_DIRECTORY = $settings->get('DIRS.APPS.LIR', realpath(__DIR__).'/');
$REGPAT_PATH = $settings->get('DIRS.cache').'/regpats.json';
$DBS_PATH = $settings->get('DIRS.cache').'/databases.json';
$AVAILABLE_REGPAT_ARRAY = file_exists($REGPAT_PATH)?json_decode(file_get_contents($REGPAT_PATH), true):[''];
$AVAILABLE_DBS_ARRAY = file_exists($DBS_PATH)?json_decode(file_get_contents($DBS_PATH), true):[''];

if ($_POST)
{
    function get ($name, $default = null)
    {
        return isset($_POST[$name])?$_POST[$name]:$default;
    }

    $config = ['options'=>''];

    // FILL
    $config['filename'] = get('filename');
    $config['regpat'] = get('regpat');
    $config['database'] = get('database');
    $config['exercise'] = get('exercise');
    $config['period_type'] = get('period_type');
    $config['date_begin'] = get('date_begin');
    $config['date_end'] = get('date_end');
    $config['email'] = get('email');
    $config['options']['worker_net'] = isset($_POST['options']['worker_net'])?$_POST['options']['worker_net']:false;
    $config['options']['worker_down'] = isset($_POST['options']['worker_down'])?$_POST['options']['worker_down']:false;

    // FORMAT
    $config['date_begin'] = str_replace('-', '', $config['date_begin']). ' 00:00';
    $config['date_end'] = str_replace('-', '', $config['date_end']). ' 00:00';
    $config['options']['worker_net'] = $config['options']['worker_net'] == 'on'?true:false;
    $config['options']['worker_down'] = $config['options']['worker_down'] == 'on'?true:false;

    $content = json_encode($config, JSON_PRETTY_PRINT);

    $filename = date("YmdHis\_").uniqid();
    file_put_contents($REQUESTS_DIRECTORY . DIRECTORY_SEPARATOR . $filename . '.json', $content);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>REPORTE LIR</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<?php if ($_POST): ?>
    <div style="padding: 20px; width 100%; font-size: 16px; font-weight: bolder; text-align: center; color:darkgreen; background-color: lightgreen;">REPORTE AÑADIDO A LA COLA</div>
<?php endif; ?>

<form action="" method="POST">
    <div id="popup">
        <h1>LIR REPORT</h1>

        <div class="attribute-group">
            <h2>General Info</h2>
            <div class="content">
                <div class="attribute half">
                    <div class="key">Nombre del Reporte</div>
                    <div class="value">
                        <input name="filename" type="text" />
                    </div>
                </div>

                <div class="attribute half">
                    <div class="key">Registro Patronal</div>
                    <div class="value">
                        <select name="regpat" id="regpat">
                            <?php
                            foreach ($AVAILABLE_REGPAT_ARRAY as $key => $value)
                                echo '<option value="'.$key.'">'.$value.'</option>';
                            ?>
                        </select>
                    </div>
                </div>


                <div class="attribute half">
                    <div class="key">Empresa</div>
                    <div class="value">
                        <select name="database" id="database">
                            <option id='all' value="">Todas las Empresas</option>
                            <?php
                            foreach ($AVAILABLE_DBS_ARRAY as $db_slug => $row)
                                echo '<option data-regpats="'.join($row['regpats'], ',').'" value="'.$db_slug.'">'.$row['string'].'</option>'.PHP_EOL;
                            ?>
                        </select>
                    </div>
                </div>


                <div class="attribute half">
                    <div class="key">Ejercicio</div>
                    <div class="value">
                        <input name="excercise" type="number" value="<?php echo date('Y'); ?>">
                    </div>
                </div>

                <div class="attribute half">
                    <div class="key">Periodo</div>
                    <div class="value">
                        <select name="period_type">
                            <option value="">Todos los Periodos</option>
                            <option value="semanal">Semanal</option>
                            <option value="catorcenal">Catorcenal</option>
                            <option value="mensual">Mensual</option>
                        </select>
                    </div>
                </div>

                <div class="attribute half">
                    <div class="key">Fecha de Inicio</div>
                    <div class="value">
                        <input name="date_begin" type="date">
                    </div>
                </div>

                <div class="attribute half">
                    <div class="key">Fecha de Fin</div>
                    <div class="value">
                        <input name="date_end" type="date">
                    </div>
                </div>

            </div>


            <div class="attribute-group">
                <h2>Extra</h2>
                <div class="content">
                    <div class="attribute half">
                        <div class="key">Correo Electr&oacute;nico</div>
                        <div class="value">
                            <input name="email" type="email">
                        </div>
                    </div>

                    <div class="attribute half">
                        <div class="key">Trabajadores con neto 0</div>
                        <div class="value">
                            <input name="options[worker_net]" type="checkbox">
                        </div>
                    </div>

                    <div class="attribute half">
                        <div class="key">Trabajadores con baja</div>
                        <div class="value">
                            <input name="options[worker_down]" type="checkbox">
                        </div>
                    </div>

                    <div class="attribute">
                        <div class="key"></div>
                        <div class="value">
                            <input type="submit" value="CREAR REPORTE">
                        </div>
                    </div>

                </div>
            </div>

        </div>
</form>


<script>
document.getElementById('regpat').addEventListener('change', function(){
    regpat_selected = this.value;
    options = document.querySelectorAll('#database option');
    document.getElementById('database').value = '';
    for (var i = 0; i < options.length; i++) {
        op = options[i];
        if (op.id == 'all')
            continue;
        if (op.attributes.item('data-regpat').textContent.split(',').indexOf(regpat_selected) != -1) {
            op.style.display = 'block';
        } else {
            op.style.display = 'none';
        }
        //console.log(op.style.display, op)
    }
});

</script>

</body>
</html>