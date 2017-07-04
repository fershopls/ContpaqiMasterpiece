<?php
require_once realpath(__DIR__) . '/../bootstrap.php';

use lib\Database\SQLite3\Analytics;
$ana = new Analytics($settings->get('DIRS.cache'));

$rows = $ana->query('SELECT * FROM ANALYTICS ORDER BY id DESC;');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/skeleton/2.0.4/skeleton.min.css">
</head>
<body>

</body>
</html>
<?php

if (!count($rows) > 0)
    exit("0 rows.");

function get_time ($time)
{
    return date('H:i:s l d/m/Y', $time);
}

function get_params ($params)
{
    $data = json_decode($params, true);
    $string = "";
    foreach ($data as $key => $value)
    {
        $string .= '<tr><th>'.ucwords($key).'</th></tr>';
        $value = is_array($value)?json_encode($value):$value;
        $string .= "<tr><td><textarea style='width:100%;'>{$value}</textarea></td></tr>";
    }
    return $string;
}

function get_aprox_sec ($s)
{
    $string = "";
    $string .= ($s)." seconds <br>";
    $string .= round($s/60, 2)." minutes <br>";
    $string .= round($s/(60*60), 2)." hours <br>";
    $string .= round($s/(60*60*24), 2)." days <br>";
    return $string;
}

?>

<?php if (isset($_GET['id'])): ?>
<?php
$r = $ana->get($_GET['id']);
$o = json_decode($r['params_backend'], true);
$of = json_decode($r['params_frontend'], true);
if (!$r)
{
    exit('Id not found');
}
?>
<a href="admin.php">&laquo; Back</a>
<h1 style="text-align:center;"><?= isset($of['filename'])?array_pop(explode(DIRECTORY_SEPARATOR, $of['filename'])):'Unknown' ?></h1>
<div style="text-align:center;">(<?= isset($o['filename'])?$o['filename']:'-' ?>)</div>


<table class="u-full-width">
    <tr>
        <th>#</th>
        <td><?=$r['id']?></td>
    </tr>

    <tr>
        <th>Report</th>
        <td><?=$r['frontend_id']?></td>
    </tr>

    <tr>
        <th>Created at</th>
        <td><?=get_time($r['created_at'])?></td>
    </tr>

    <tr>
        <th>Started at</th>
        <td><?=get_time($r['started_at'])?></td>
    </tr>

    <tr>
        <th>Ended at</th>
        <td><?=get_time($r['ended_at'])?></td>
    </tr>

    <tr>
        <th>Status start</th>
        <td><?=$r['status_start']?></td>
    </tr>

    <tr>
        <th>Status end</th>
        <td><?=str_replace('.', '.<br>', $r['status_end']) ?></td>
    </tr>

    <tr style="background-color: #fafafa;">
        <th colspan="2" style="text-align:center;">Time Factors</th>
    </tr>

    <tr>
        <td colspan="2">
            <table class="u-full-width">
                <tr>
                    <td>Time taked create to start</td>
                    <td><?=get_aprox_sec($r['started_at'] - $r['created_at']) ?></td>
                </tr>
                <tr>
                    <td>Time taked start to end</td>
                    <td><?=get_aprox_sec($r['ended_at'] - $r['started_at']) ?></td>
                </tr>
                <tr>
                    <td>Time taked create to end</td>
                    <td><?=get_aprox_sec($r['ended_at'] - $r['created_at']) ?></td>
                </tr>
            </table>
        </td>
    </tr>

    <tr style="background-color: #fafafa;">
        <th>User Input</th>
        <th>Final Parameters</th>
    </tr>

    <tr>
        <td style="text-align: right;">
            <table class="u-full-width" style="padding:0; margin:0; position: relative; top: -165px;">
                <?=get_params($r['params_frontend']) ?>
            </table>
        </td>
        <td>
            <table class="u-full-width" style="padding:0; margin:0;">
                <?=get_params($r['params_backend']) ?>
            </table>
        </td>
    </tr>
</table>


<hr>
<?php endif; ?>



<table>
    <tr>
        <th>#</th>
        <th></th>
        <th>Name</th>
        <th>Created at</th>
        <th>Ended at</th>
        <th>Status start</th>
        <th>Status end</th>
        <th>More</th>
    </tr>
    <?php foreach ($rows as $r): ?>
        <?php
            $o = json_decode($r['params_backend'], true);
        ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= $r['frontend_id'] ?></td>
            <td><?= isset($o['filename'])?array_pop(explode(DIRECTORY_SEPARATOR, $o['filename'])):'-' ?></td>
            <td><?= get_time($r['created_at']) ?></td>
            <td><?= get_time($r['ended_at']) ?></td>
            <td><?= $r['status_start'] ?></td>
            <td><?= $r['status_end'] ?></td>
            <td><a href="?id=<?= $r['id'] ?>">See More &raquo;</a></td>
        </tr>
    <?php endforeach; ?>
</table>
