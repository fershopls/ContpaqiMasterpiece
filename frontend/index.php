<?php

// Init Script
require_once realpath(__DIR__) . '/../bootstrap.php';

// Work on existent frontend
$frontends_available = $settings->get('FRONTEND');
$frontend_id = isset($_GET['frontend'])?$_GET['frontend']:'';
if (!isset($frontends_available[$frontend_id]))
{
    echo "Selecciona un Reporte a crear:";
    foreach ($frontends_available as $id => $value)
    {
        echo '<br><a href="?frontend='.$id.'">'.$id.'</a>';
    }
    exit();
}

// Instance FormBuilder with specific settings
use lib\Frontend\FormBuilder;
$FormBuilder = new FormBuilder($frontends_available[$frontend_id]['FORM']);
$FormBuilder->setSourceParameters([$settings]);

// When the form has been sent
if ($_POST)
{
    $json = json_encode($FormBuilder->receive($_POST), JSON_PRETTY_PRINT);
    $save_path = get_dir(strtoupper($frontend_id), $settings, get_dir('request', $settings));
    $filename = date("Ymd_His").'.json';
    file_put_contents($save_path . DIRECTORY_SEPARATOR . $filename, $json);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo strtoupper($frontend_id); ?> - Formato</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<?php if ($_POST): ?>
    <div style="padding: 20px; width 100%; font-size: 16px; font-weight: bolder; text-align: center; color:darkgreen; background-color: lightgreen;">REPORTE AÑADIDO A LA COLA!</div>
    <pre><?php echo "[".$save_path."]\n>> ".$filename."\n----\n".$json."\n----"; ?></pre>
<?php endif; ?>

<a href="/">&laquo; Volver a Reportes</a>

<form action="" method="POST">
    <div id="popup">
        <h1><?php echo strtoupper($frontend_id); ?> REPORT</h1>

        <div class="attribute-group">
            <h2>Parámetros del Reporte</h2>
            <div class="content">

                <?php foreach ($FormBuilder->dumpFields() as $id => $field): ?>
                    <div class="attribute of-<?php echo $id;?> half">
                        <div class="key"><?php echo $field['label'];?></div>
                        <div class="value">
                            <?php echo $field['html']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="attribute">
                    <div class="value">
                        <input type="submit" value="SOLICITAR REPORTE">
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>


<?php
// Frontend Includes
if (isset($frontends_available[$frontend_id]['INCLUDE']))
    foreach ($frontends_available[$frontend_id]['INCLUDE'] as $file)
        include(realpath(__DIR__) . DIRECTORY_SEPARATOR . $file['src']);
?>

</body>
</html>