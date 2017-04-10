<?php
/**
 * Created by PhpStorm.
 * User: ilya
 * Date: 10.04.2017
 * Time: 18:03
 */
$func = $_GET['func'];
session_start();
if (!isset($_SESSION['login'])) {
    echo 'login first';
    exit;
}
$login = $_SESSION['login'];
$lastseen = $_SESSION['lastseen'];

function getVal($query){
    global $db;
    $data = mysql_query($query,$db);
    return mysql_fetch_array($data)[0];
}
function printDohod($year,$month,$day){
    $dohod = getVal("SELECT ");
    $text = "$day.$month<br/>
<span style='text-decoration:underline;'>Доходы</span><br/>
<b>План доход: </b><br/>
<b>Осталось с пред. месяца: </b><br/>
<b>Сумма: </b><br/><hr/>
<b>Расходы: </b><br/>
<b>Свободные деньги: </b><br/>";
    return $text;
}

function printMonth ($year,$month = 1){

?>
    <div class="wrapper">
        <div class="side-left">
            <?php print printDohod($year,$month,5); ?>
        </div>
        <div class="side-right">
            <?php print printDohod($year,$month,20); ?>
        </div>
    </div>
<?php
}

include 'base.php';
if ($func == 'year') {
    $year = $_GET['year'];
    printMonth($year);

}
if ($func == 'month') {
    $year = $_GET['year'];
    $month = $_GET['month'];
    printMonth($year,$month);

}
?>
