<?php

session_start();
if (!isset($_SESSION['username']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}

$session = $_SESSION;
session_write_close();

define('NineteenEleven', TRUE);

require_once '../../../includes/config.php';
require_once ABSDIR . 'includes/SourceBansClass.php';
try {
    $sb = new sb;
} catch (Exception $ex) {
    echo "<div class='alert alert-danger' role='alert'>" . $ex->getMessage() . "</div>";
    $log->logError($ex->getMessage(), $ex->getFile(), $ex->getLine());
    die();
}

$numMonths = filter_input(INPUT_POST, 'numMonths', FILTER_SANITIZE_NUMBER_INT);

$now = date('U');
//the first date we need in our array is now.
$months[0] = $now;

$i = 1;

//date() returns strings, we need int.
settype($months[0], 'integer');
settype($numMonths, 'integer');


//put the times of the previous months in an array
while ($i <= $numMonths) {

    $m = $now - ((31 * 86400) * $i);

    $months[$i] = $m;
    $i++;
}

//prepare our sql query.
$stmt = $sb->ddb->prepare("SELECT count(*) as numDonors,sum(current_amount) FROM `donors` WHERE `renewal_date` BETWEEN ? AND ? "
        . "OR `sign_up_date` BETWEEN ? AND ?;");


$i--;


//count down $i and query the database for the data.
while ($i >= 1) {
    $k = $i - 1;
    try {
        $stmt->execute(array($months[$i], $months[$k], $months[$i], $months[$k]));
        $stmt->execute();
    } catch (Exception $ex) {
        echo "<div class='alert alert-danger' role='alert'>" . $ex->getMessage() . "</div>";
        $log->logError($ex->getMessage(), $ex->getFile(), $ex->getLine());
        die();
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (is_null($row['numDonors']) || is_null($row['sum(current_amount)'])) {
        echo "<div class='alert alert-danger' role='alert'>Unable to find data for that range.</div>";
        die();
    }

    //put it in an array so we can use it later.
    $data[$months[$i]] = array($row['numDonors'], $row['sum(current_amount)']);

    $i--;
}


// this sets our variables for Chart.js this code can go in the while loop above,
// but were gonna leave it like this for now.
$monthList = '';
$numDonors = '';
$total = '';
foreach ($data as $key => $val) {

    $monthList .= '"' . date('F', $key) . '",';
    $numDonors .= $val[0] . ',';
    $total .= $val[1] . ',';
}
$monthList = substr($monthList, 0, -1);
$numDonors = substr($numDonors, 0, -1);
$total = substr($total, 0, -1);

//echo $monthList;
//echo $numDonors;
//echo $total;
echo "



    <body>
        <div class='canvasContainer'>
            <div>
                <canvas id='histCanvas' height='350' width='1000'></canvas>
            </div>
        </div>



<script>
    var lineChartData = {
        labels: [$monthList],
        datasets: [
            {
                label: 'Total monies recieved',
                fillColor: 'rgba(220,220,220,0.2)',
                strokeColor: 'rgba(220,220,220,1)',
                pointColor: 'rgba(220,220,220,1)',
                pointStrokeColor: '#fff',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(220,220,220,1)',
                data: [$total]
            },
            {
                label: 'Number of donors',
                fillColor: 'rgba(151,187,205,0.2)',
                strokeColor: 'rgba(151,187,205,1)',
                pointColor: 'rgba(151,187,205,1)',
                pointStrokeColor: '#fff',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(151,187,205,1)',
                data: [$numDonors]
            }
        ]

    }



</script>";

// window.onload = function() {
    //     var ctx = document.getElementById('canvas').getContext('2d');
    //     window.myLine = new Chart(ctx).Line(lineChartData, {
    //         responsive: true
    //     });
    // }