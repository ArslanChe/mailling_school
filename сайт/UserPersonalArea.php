<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv = "X-RU-Comparible" content = "ie=edge">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <title>Личный кабинет</title>
    </head>
    <body>
    <p><a>Личный кабинет </a></p>
    <a>Инструкция</a>
    <p>
    0) Сгенерируйте код
    </p>
    <p>
    1)  Для регистрации в вк: перейдите по ссылке<a href="https://vk.com/public198590337"> Бот_vk </a>и напишите сообщение с кодом, который вы сгенерировали на сайте.</p>
    <p>
    2)  Для регистрации в vibere: отсканируйте QR код в приложении</p> <p>qr code:<img src = "https://sun9-7.userapi.com/impg/LfpdJ3AJKW0xEWlp_Z38Glhfquf78PONuMDDcw/2vg4VpoFOPo.jpg?size=395x394&quality=96&sign=31b5f927ae3f6de3b02f130993f28859&type=album" 
    width = "200" height = "200"> </p>
    <p>
    3)  Для регистрации в телеграмме: перейдите по ссылку <a href ="https://t.me/Fjwifodofwo_bot"> Бот телеграмм </a> и напишите ему код с сайта
    </p>
    
    </body>
    </html>




<form action = "UserPersonalArea.php" method = "get">
<p>
    <button type ="submit" name = "generate_code"> Сгенерировать код </button>
</p>
</form>
<form action = "UserPersonalArea.php" method = "get">
<p>
    <button type ="submit" name = "do_logout">Выйти</button>
</p>
</form>


<?php
$conn_string = ("host=127.0.0.1 port=5432 dbname=messages user=messages password=MES_654GW");
$dbconn = pg_connect($conn_string);
session_start();

function out() {
    unset($_SESSION['id']);
    SetCookie("login", "", time() - 3600); 
    SetCookie("password", "", time() - 3600);
    setCookie("isadmin","", time() - 3600);
    header('Location: index.php');
}


include_once('login.php');


if(login($dbconn)){
    $id = $_SESSION['id'];
    $sql = pg_query($dbconn, "SELECT code FROM user_information where user_id = $id");
    $active_code = pg_fetch_result($sql, 0,0);
    echo($active_code);

    if(isset($_GET['generate_code'])){
        $new_code = random_int(10000, 999999);
        $zap = pg_query($dbconn, "UPDATE user_information set code = $new_code where user_id = $id");
        $_GET['generate_code'] = 0;
        header('Location: UserPersonalArea.php');
    }

    if(isset($_GET['do_logout'])){
        out();
    }
}else{ 
    header('Location: index.php');
}






?>