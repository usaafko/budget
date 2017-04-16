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
class mydate{
    public $pdate = '';
    public $year;
    public $month;
    public $day;
    public $date;
    function __construct($year,$month = 1, $day = 5){
        $this->pdate = $year.'-'.$month.'-'.$day;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->date = ['year'=>$year,'month'=>$month,'day'=>$day];
    }
    public function calcDate(){
        $date = $this->date;
        if ($date['day'] == 20){
            $date['day'] = 5;
        } else {
            $date['day'] = 20;
            if ($date['month'] == 1){
                $date['year']--;
                $date['month'] = 12;
            }else{
                $date['month']--;
            }
        }
        return new mydate($date['year'],$date['month'],$date['day']);
    }
    public function changeDay($day){
        $this->pdate = $this->year.'-'.$this->month.'-'.$day;
        $this->day = $day;
        $this->date['day'] = $day;
    }

}
function svobodnie($cldate){
    $sv['plan_dohod'] = getVal("SELECT sum(summa) FROM dohod_periodic WHERE den='".$cldate->day."'");
    $sv['dohod'] = getVal("SELECT sum(summa) FROM dohod WHERE add_date='".$cldate->pdate."'");
    $sv['ostalos'] = ostalos($cldate);
    $sv['summa'] = $sv['ostalos'] + $sv['plan_dohod'] + $sv['dohod'];
    $sv['rashod_periodic'] = getVal("SELECT sum(summa) FROM rashod_periodic WHERE den='".$cldate->day."'");
    $sv['rashod'] = getVal("SELECT sum(summa) FROM rashod WHERE add_date='".$cldate->pdate."'");
    $sv['svobodnie'] = $sv['summa'] - $sv['rashod_periodic'] - $sv['rashod'];
    return $sv;
}
function ostalos($cldate){
    $newdate = $cldate->calcDate();
    $query = "SELECT summa FROM balance WHERE change_date <= '".$cldate->pdate."' AND change_date > '".$newdate->pdate."' ORDER BY change_date DESC LIMIT 1";
    $q_data = mysql_query ($query);
    if (mysql_num_rows($q_data) > 0) return mysql_fetch_array($q_data)[0];

    $sv = svobodnie($newdate);
    return $sv['summa'] - $sv['rashod'] - $sv['rashod_periodic'];
}
function printDohod($cldate){
    $sv = svobodnie($cldate);
    return "<span style='text-decoration:underline;'>Доходы</span><br/>
<b>План доход: </b>".$sv['plan_dohod']."<br/>
<b>Осталось с пред. месяца: </b>".$sv['ostalos']."<br/>
<b>Сумма: </b>" . ($sv['ostalos']+$sv['plan_dohod']) ."<br/>
<b>Расходы: </b>".($sv['rashod']+$sv['rashod_periodic'])."<br/>
<b>Свободные деньги: </b>".$sv['svobodnie']."<br/><hr/>";
}

function printRashod($cldate){
    $text = "";
    global $db;
    $res = mysql_query("SELECT * FROM rashod_periodic WHERE den='".$cldate->day."'",$db);
    $i = 0;
    while ($data = mysql_fetch_assoc($res)){
        $i++;
        $text .= "$i. ".$data['title']." ".$data['summa']."<br/>";
    }
    $res = mysql_query("SELECT * FROM rashod WHERE add_date='".$cldate->pdate."'",$db);
    while ($data = mysql_fetch_assoc($res)){
        $i++;
        $text .= $i.". ".$data['title']." ".$data['summa']." "
            ."<input class='button editrashod' type='button' value='изменить' rid='".$data['id']."'/> "
            ."<input type='button' value='удалить' rid='".$data['id']."' class='button removerashod' date='".$cldate->pdate."'/><br/>";
    }
    $text .= "<span><input class='button addrashod' type='button' date='".$cldate->pdate."' value='новый'/></span>";
    return $text;
}
function printBalance($cldate){
    $res = mysql_query("SELECT * FROM balance WHERE change_date <= '".$cldate->pdate."' AND change_date > '".$cldate->calcDate()->pdate."'");
    if (mysql_num_rows($res) > 0) {
        $text = '<b>Изменения баланса</b><br/>';
        while ($data = mysql_fetch_assoc($res)){
            $text .= $data['change_date']." ".$data['summa']."<br/>";
        }
    }else{
        $text = 'Изменений баланса не было';
    }
    return $text;
}
function printPartMonth($cldate,$day){
    $cldate->changeDay($day);
    return "<div>".$cldate->month.".".$cldate->day."</div>
    <div>".printBalance($cldate)."</div>
    <div>".printDohod($cldate)."</div>
    <div>".printRashod($cldate)."</div>";
}
function printMonth ($cldate){

?>
    <div class="wrapper">
        <div class="side-left">
            <?php print printPartMonth($cldate,5); ?>
        </div>
        <div class="side-right">
            <?php print printPartMonth($cldate,20); ?>
        </div>
    </div>
<?php
}

include 'base.php';
if ($func == 'year') {
    $year = $_GET['year'];
    $cldate = new mydate($year);
    printMonth($cldate);

}
if ($func == 'month') {
    $year = $_GET['year'];
    $month = $_GET['month'];
    $cldate = new mydate($year,$month);
    printMonth($cldate);

}
if ($func == 'balance_change'){
    $balance = mysql_real_escape_string($_GET['balance'],$db);
    mysql_query("INSERT INTO balance (summa,change_date) VALUES ('$balance',now())",$db);
}
if ($func == 'addrashod_go'){
    $text = $_GET['text'];
    $summa = $_GET['summa'];
    $date = $_GET['date'];
    list ($year,$month,$day) = split("-",$date);
    mysql_query("INSERT INTO rashod (add_date,summa,title) VALUE ('$date','$summa','$text')");
    printMonth(new mydate($year,$month,$day));
}
if ($func == 'remove_rashod'){
    $id = $_GET['id'];
    $date = $_GET['date'];
    mysql_query("DELETE FROM rashod WHERE id=$id");
    list ($year,$month,$day) = split("-",$date);
    printMonth(new mydate($year,$month,$day));
}
?>
