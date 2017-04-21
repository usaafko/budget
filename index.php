<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta http-equiv="content-type" content="text/html; charset=utf8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
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
            background: skyblue;
        }
        .side-right{
            width: 50%;
            float: left;
            background: palegreen;
        }
        .button {
            color: #fff;
            background-color: #6496c8;
            text-shadow: -1px 1px #417cb8;
            border: none;
            cursor: pointer;
        }
        .bottom_pad {
            margin-bottom: 2px;
        }
        @media (max-width: 600px) {
            body {
                font-size: 1.5em;
            }
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
            $('.month.selected').click();
            $('body').on('click', '.button.add', function() {
                var button = $(this)
                    , date = button.attr('date')
                    , wrapper = button.parent()
                    , actiontype = button.attr('actiontype')
                    , text = "<input placeholder='Название' type='text' class='bottom_pad add_title'/><br/>" +
                        "<input placeholder='Сумма' type='text' class='bottom_pad add_value'/><br/>" +
                        "<input class='button add_go' actiontype='"+actiontype+"' date='"+date+"' type='button' value='Добавить'/>";
                wrapper.html(text);
            });
            $('body').on('click', '.button.add_go', function() {
                var button = $(this)
                    , date = button.attr('date')
                    , wrapper = button.parent()
                    , actiontype = button.attr('actiontype')
                    , text = wrapper.find('.add_title').val()
                    , summa = wrapper.find('.add_value').val();
                $.get('get_data.php?func=add_go&date='+date+'&text='+text+'&summa='+summa+'&actiontype='+actiontype, function(data) {
                    $('#getdata').html(data);
                });
            });
            $('body').on('click', '.button.remove', function() {
                var button = $(this)
                    , id = button.attr('rid')
                    , actiontype = button.attr('actiontype')
                    , date = button.attr('date');

                $.get('get_data.php?func=remove&id='+id+'&date='+date+'&actiontype='+actiontype, function(data) {
                    $('#getdata').html(data);
                });
            });
            $('body').on('click', '.button.edit', function() {
                var button = $(this)
                    , id = button.attr('rid')
                    , date = button.attr('date')
                    , actiontype = button.attr('actiontype')
                    , title = button.attr('rtitle')
                    , summa = button.attr('rsumma')
                    , wrapper = button.parent()
                    , text = "<input placeholder='Название' type='text' value='"+title+"' class='bottom_pad edit_title'/><br/>" +
                    "<input placeholder='Сумма' type='text' value='"+summa+"' class='bottom_pad edit_value'/><br/>" +
                    "<input class='button edit_go' date='"+date+"' actiontype='"+actiontype+"' rid='"+id+"' type='button' value='Изменить'/>";;
                wrapper.html(text);
            });
            $('body').on('click', '.button.edit_go', function() {
                var button = $(this)
                    , date = button.attr('date')
                    , id = button.attr('rid')
                    , wrapper = button.parent()
                    , actiontype = button.attr('actiontype')
                    , text = wrapper.find('.edit_title').val()
                    , summa = wrapper.find('.edit_value').val();
                $.get('get_data.php?func=edit_go&id='+id+'&date='+date+'&text='+text+'&summa='+summa+'&actiontype='+actiontype, function(data) {
                    $('#getdata').html(data);
                });
            });
            $('body').on('click', '.button.fact', function() {
                var button = $(this)
                    , id = button.attr('rid')
                    , actiontype = button.attr('actiontype')
                    , date = button.attr('date');
                $.get('get_data.php?func=fact&id='+id+'&date='+date+'&actiontype='+actiontype, function(data) {
                    $('#getdata').html(data);
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
print "Hello $user. Last seen you at: $lastseen <input id='balance' type='text'/> <input id='balance_change' value='Обновить баланс' class='button' type='button'/><br/>";
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
