<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta http-equiv="content-type" content="text/html; charset=utf8" />
    <style>
        .year, .month {
            padding: 1px 2px;
            cursor: pointer;
            margin-right: 5px;
        }
        p {
            margin: 4px 0;
        }
        .selected { background: orange; }
        .wrapper{
            width: 100%;
            margin: 0 auto;
        }
        .side-left{
            width: 49%;
            float: left;
            margin-right: 1%;
        }
        .side-right{
            width: 50%;
            float: left;
        }
        @media screen and (max-width: 720px) {
            .side-left{
                width: 100%;
                clear: both;
            }
            .side-right{
                border-top: solid 2px red;
                margin-top: 10px;
                width: 100%;
                clear: both;
            }
        }
    </style>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.year').click(function () {
                var me = $(this), year = me.html();
                $('.year.selected').removeClass('selected');
                me.addClass('selected');
                $('.month.selected').removeClass('selected');
                $('.month[data=1]').addClass('selected');
                $.get('get_data.php?func=year&year='+year, function(data) {
                    $('#getdata').html(data);
                });
            });
            $('.month').click(function () {
                var me = $(this)
                    , month = me.attr('data')
                    , year = $('.year.selected').html();
                $('.month.selected').removeClass('selected');
                me.addClass('selected');
                $.get('get_data.php?func=month&year='+year+'&month='+month, function(data) {
                    $('#getdata').html(data);
                });
            });
            $('#balance_change').click(function () {
                var me=$('#balance');
                $.get('get_data.php?func=balance_change&balance='+me.val(), function(data) {
                    $('#getdata').html(data);
                    me.val('');
                });
            });
        });
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
print "Hello $user. Last seen you at: $lastseen <input id='balance' type='text'/><input id='balance_change' value='Обновить баланс' type='button'/><br/>";
$date = getdate();
print "<p>";
for ($i=2017; $i<=2025; $i++) {
    $selected = '';
    if ($i == $date['year']) $selected = 'selected';
    print "<span class='year $selected'>$i</span>";
}
print "</p><p>";
$i = ['Янв','Фев','Мар','Апр','Май','Июнь','Июль','Авг','Сен','Окт','Ноя','Дек'];
$j = 0;
foreach($i as $mon) {
    $j++;
    $selected = '';
    if ($j == $date['mon']) $selected = 'selected';
    print "<span data='$j' class='month $selected'>$mon</span>";
}
?>
</p>
<div id="getdata">

</div>

</body>

</html>
