<?php
$conn_string = ("host=127.0.0.1 port=5432 dbname=messages user=messages password=MES_654GW");
$dbconn = pg_connect($conn_string);
session_start();
?>
<!DOCTYPE html>
    <html lang="ru">
    <head>
        <style>
            .block{position:relative;} 
            .hidden
            {display: none;
            position: absolute;
            bottom: 130%;
            left: 0px;
            background-color: #fff;
            color: #3aaeda;
            padding: 5px;
            text-align: center;
            -moz-box-shadow: 0 1px 1px rgba(0,0,0,.16);
            -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.16);
            box-shadow: 0 1px 1px rgba(0,0,0,.16);
            font-size: 12px;}
            .hover + .hidden:before
            {content: " ";
            position: absolute;
            top: 98%;
            left: 10%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            height: 0;
            width: 0;
            border: 7px solid transparent;
            border-right: 7px solid #fff;
            border-color: #fff transparent transparent transparent;
            z-index: 2;}
            .hover + .hidden:after
            {content: " ";
            position: absolute;
            top: 100%;
            left: 10%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            height: 0;
            width: 0;
            border: 7px solid transparent;
            border-right: 7px solid #fff;
            border-color: rgba(0,0,0,.16) transparent transparent transparent;
            z-index: 1;}

            .hover:hover + .hidden{display: block;}
        </style>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv = "X-RU-Comparible" content = "ie=edge">
        <title>Личный кабинет</title>
    </head>
    <body>

    <p><a>Личный кабинет </a></p>
    <p><a href="History.php">История сообщений</a></p>
    <p><a href="Groups.php">Группы</a></p>
    <form action ="AdminPersonalArea.php" method="post">
        <p><textarea rows="10" cols="45" name="message" autofocus maxlength="900"></textarea></p>
        <?php 
        $rez = pg_query($dbconn, "SELECT DISTINCT ON (group_id) * FROM groups;"); 
        $mas = pg_fetch_all($rez);
        $col = pg_num_rows($rez);
        
        for ($i=0; $i < $col; $i++) { 
            echo '<input type = "checkbox" name = "groups[]" value = "'.$mas[$i]['group_id'].'"/>'.$mas[$i]['group_name'].'<br>';
            
        }
        ?>
        
        <button type ="submit" name = "Send">Отправить</button>
        </p>
    </form>

    <form action = "AdminPersonalArea.php" method = "get">
    <p>
    <button type ="submit" name = "do_logout">Выйти</button>
    </p>
    </form>
    
    </div>
    </body>
    </html>



<?php

function out () {
    session_start();
    $id = $_SESSION['id'];
    unset($_SESSION['id']);
    SetCookie("login", "", time() - 3600); 
    SetCookie("password", "", time() - 3600);
    setCookie("isadmin","", time() - 3600);
    header('Location: index.php');
}

include_once('login.php');

if(login($dbconn) && $_COOKIE['isadmin'] == 1){
    if(isset($_POST['Send']) && count($_POST['groups']) > 1 && !empty($_POST['message'])) {
        echo('<p style="color: green;">'.'Выберите только ОДНУ группу');
    }elseif (isset($_POST['Send']) && !empty($_POST['groups']) && !empty($_POST['message'])) {
        $group_id = $_POST['groups'][0];
        $text = $_POST['message'];
        $sql = "INSERT INTO message_information (message_id, message, group_id) VALUES (nextval('next_message'), '$text', $group_id);";
        pg_query($dbconn, $sql);
        $sql = "INSERT INTO new_messages (message_id, viber_status, telegram_status, vk_status ) VALUES (currval('next_message'), 0, 0, 0);";
        pg_query($dbconn, $sql);
        $res = pg_query($dbconn, "SELECT user_id FROM groups WHERE group_id = $group_id;");
        if (!$res) {
            die("ERROR: ".pg_last_error());
        } else{
            $user_ids = pg_fetch_all($res);
            foreach ($user_ids as &$user_id_row){
                $user_id = $user_id_row['user_id'];
                $dop = "INSERT INTO users_messages (user_id, message_id, isread) VALUES ($user_id, currval('next_message'), false);";
                pg_query($dbconn, $dop);
            }
        }
        
        
        $del = "DELETE FROM new_messages WHERE (viber_status = 2 and telegram_status = 2 and vk_status = 2);";
        pg_query($dbconn, $del);
        
    
        header("Refresh: 0");
    }

    if(isset($_GET['do_logout'])){
        out();
    }
} else {
    header('Location: index.php');
}

?>


