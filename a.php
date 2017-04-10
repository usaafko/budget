<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta http-equiv="content-type" content="text/html; charset=utf8" />
    <style>
        .year { padding-right: 5px; }
        .month { padding-right: 5px; }
        .selected { background: orange; }
    </style>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript">
        function want(id){
            $.get('get_data.php?func=want&id='+id,function(data){
                $('#want'+id).removeClass('want0').addClass('want1');
            });
        }
    </script>

</head>

<body>
<?php
function showReg(){
    echo "<form action='' method='post'>\n";
    echo "<input type='text' name='login' placeholder='login' />\n";
    echo "<input type='password' placeholder='pass' name='pass' />\n";
    echo "<input type='submit' value='Ok' />\n";
    echo "</form></p>\n";
    exit;
}
include 'base.php'; # подключим базу
# Проверка регистрации по сессиям
session_start();
if (!isset($_SESSION['login'])) {
    if (!isset($_POST['login']) or !isset($_POST['pass'])) showReg();
    # Проверяем логин-пароль
    $user = htmlspecialchars($_POST['login']);
    $pass = md5($_POST['pass']);
    $res = mysql_query("SELECT pass,lastseen FROM user WHERE user='".mysql_real_escape_string ($user,$db)."'", $db);
    $data = mysql_fetch_array($res);
    if (strcmp($data['pass'],$pass) != 0 ) showReg();
    mysql_query("UPDATE user SET lastseen=now() WHERE user='".mysql_real_escape_string ($user,$db)."'");
    $_SESSION['login'] = $user;
    $lastseen = $data['lastseen'];
    $_SESSION['lastseen'] = $lastseen;
    setcookie(session_name(),session_id(),time()+600);

} else {
    $user = $_SESSION['login'];
    $lastseen = $_SESSION['lastseen'];
}

?>
<b>Бюджет</b><br/>
<?php
print "Hello $user. Last seen you at: $lastseen<br/>";
$date = getdate();
for ($i=2017; $i<=2025; $i++) {
    $selected = '';
    if ($i == $date['year']) $selected = 'selected';
    print "<span class='year $selected'>$i</span>";
}
print "<br/>";
$i = ['Янв','Фев','Мар','Апр','Май','Июнь','Июль','Авг','Сен','Окт','Ноя','Дек'];
$j = 0;
foreach($i as $mon) {
    $j++;
    $selected = '';
    if ($j == $date['mon']) $selected = 'selected';
    print "<span class='month $selected'>$mon</span>";
}
?>

<div id="getdata">

</div>

</body>

</html>
