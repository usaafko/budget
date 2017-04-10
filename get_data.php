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
    $data = mysql_query($query,$db) or trigger_error("Mysql error: ".mysql_error(),E_USER_ERROR);
    if ($data) {
        return mysql_fetch_array($data)[0];
    }else{
        return 0;
    }
}
function ostalos($year,$month,$day){
    $query = "SELECT summa FROM balance WHERE change_date >= '$year-$month-$day' ORDER BY id DESC LIMIT 1";
    $last_balance = getVal($query);
    trigger_error($query,E_USER_WARNING);
    trigger_error("last_balance: $last_balance, year: $year, month: $month, day: $day",E_USER_WARNING);
    if ($last_balance > 0) return $last_balance;
    if ($day == 20){
        $day = 5;
    } else {
        $day = 20;
        if ($month == 1){
            $year--;
            $month = 12;
        }else{
            $month--;
        }
    }
    $ostalos_pred = ostalos($year,$month,$day);
    $plan_dohod = getVal("SELECT sum(summa) FROM dohod_periodic WHERE den='$day'");
    $dohod = getVal("SELECT sum(summa) FROM dohod WHERE add_date='$year-$month-$day'");
    $summa = $ostalos_pred + $plan_dohod + $dohod;
    $rashod_periodic = getVal("SELECT sum(summa) FROM rashod_periodic WHERE den='$day'");
    $rashod = getVal("SELECT sum(summa) FROM rashod WHERE add_date='$year-$month-$day'");
    return $summa - $rashod - $rashod_periodic;
}
function printDohod($year,$month,$day){
    $plan_dohod = getVal("SELECT sum(summa) FROM dohod_periodic WHERE den='$day'");
    $dohod = getVal("SELECT sum(summa) FROM dohod WHERE add_date='$year-$month-$day'");
    $ostalos = ostalos($year,$month,$day);
    $summa = $ostalos + $plan_dohod + $dohod;
    $rashod_periodic = getVal("SELECT sum(summa) FROM rashod_periodic WHERE den='$day'");
    $rashod = getVal("SELECT sum(summa) FROM rashod WHERE add_date='$year-$month-$day'");
    $svobodnie = $summa - $rashod_periodic - $rashod;
    $text = "$day.$month<br/>
<span style='text-decoration:underline;'>Доходы</span><br/>
<b>План доход: </b>$plan_dohod<br/>
<b>Осталось с пред. месяца: </b>$ostalos<br/>
<b>Сумма: </b>" . ($ostalos+$plan_dohod) ."<br/><hr/>
<b>Расходы: </b>".($rashod+$rashod_periodic)."<br/>
<b>Свободные деньги: </b>$svobodnie<br/>";
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
if ($func == 'balance_change'){
    $balance = mysql_real_escape_string($_GET['balance'],$db);
    mysql_query("INSERT INTO balance (summa,change_date) VALUES ('$balance',now())",$db);
}
?>
