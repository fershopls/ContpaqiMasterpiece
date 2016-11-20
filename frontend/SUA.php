<?php

$REQUESTS_DIRECTORY = 'C:\Users\FershoPls\Google Drive\OpenSource\ReporterInterface\support\request\SUA';
$AVAILABLE_REGPAT_ARRAY = json_decode(file_get_contents(realpath(__DIR__) . '/regpats.json'), true);

if ($_POST)
{
    function get ($name, $default = null)
    {
        return isset($_POST[$name])?$_POST[$name]:$default;
    }

    $config = [];

    // FILL
    $config['filename'] = get('filename').'.csv';
    $config['regpat'] = get('regpat');
    $config['date_type'] = get('date_type');
    $config['date_y'] = get('date_y');
    $config['date_m'] = get('date_m');
    $config['email'] = get('email');

    $content = json_encode($config, JSON_PRETTY_PRINT); 

    $filename = date("YmdHis\_").uniqid();
    file_put_contents($REQUESTS_DIRECTORY . DIRECTORY_SEPARATOR . $filename . '.json', $content);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>REPORTE SUA</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<?php if ($_POST): ?>
    <div style="padding: 20px; width 100%; font-size: 16px; font-weight: bolder; text-align: center; color:darkgreen; background-color: lightgreen;">REPORTE AÑADIDO A LA COLA</div>
<?php endif; ?>

<form action="" method="POST">
    <div id="popup">
        <h1>SUA REPORT</h1>

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
                        <select name="regpat">
                            <?php
                            foreach ($AVAILABLE_REGPAT_ARRAY as $key => $value)
                                echo '<option value="'.$key.'">'.$value.'</option>';
                            ?>
                        </select>
                    </div>
                </div>

                <div class="attribute half">
                    <div class="key">Periodo</div>
                    <div class="value">
                        <select name="date_type">
                            <option value="B">Bimestral</option>
                            <option value="M">Mensual</option>
                        </select>
                    </div>
                </div>

                <div class="attribute half">
                    <div class="key">Mes de Inicio</div>
                    <div class="value">
                        <select name="date_m">
                            <?php
                            foreach (range(1, 12) as $value)
                                echo '<option value="'.$value.'">'.$value.'</option>';
                            ?>
                        </select>
                    </div>
                </div>

                <div class="attribute half">
                    <div class="key">Año de Inicio</div>
                    <div class="value">
                        <input name="date_y" type="number" value="<?php echo date("Y"); ?>">
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

</body>
</html