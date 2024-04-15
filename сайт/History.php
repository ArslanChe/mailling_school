<?php
$conn_string = ("host=127.0.0.1 port=5432 dbname=messages user=messages password=MES_654GW");
$dbconn = pg_connect($conn_string);
session_start();
include_once('login.php');
if(login($dbconn) && $_COOKIE['isadmin'] == 1){
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
      #okno {
        width: 300px;
        height: 50px;
        text-align: center;
        padding: 15px;
        border: 3px solid #0000cc;
        border-radius: 10px;
        color: #0000cc;
        display: none;
      }
      #okno:target {display: block;}
      .close {
        display: inline-block;
        border: 1px solid #0000cc;
        color: #0000cc;
        padding: 0 12px;
        margin: 45px;
        text-decoration: none;
        background: #f2f2f2;
        font-size: 10pt;
        cursor:pointer;
      }
      .close:hover {background: #e6e6ff;}
    </style>
</head>
<body>



<p><a href="AdminPersonalArea.php">На главную</a></p>
<table cellpadding="10" border="1" width="40%">
    <tr>
    <th>Id сообщения</th>
    <th>Сообщение </th>
    <th>Получатели</th>
    </tr>
    <?php
    $sql = pg_query($dbconn, "SELECT DISTINCT ON (message) * FROM message_information");
   
    $mas = pg_fetch_all($sql);
    rsort($mas);
    
  
    for ($i=0; $i < count($mas); $i++) { 
      $dop = $mas[$i]['message'];
      $mes_id = $mas[$i]['message_id'];
      $zap_na_blue = pg_query($dbconn, "SELECT * from new_messages where message_id = $mes_id");
      $blue = pg_fetch_assoc($zap_na_blue);
      
      $zap_na_group = pg_query($dbconn, "SELECT group_id from message_information where message = '$dop'");
      $groups = pg_fetch_array($zap_na_group);
      echo('<tr><td>'.$mas[$i]['message_id'].'</td><td>'.$mas[$i]['message'].'</td><td>');
      $gr = $groups[0];
      $zap_na_users = pg_query($dbconn, "SELECT user_id from groups where group_id = $gr");
      
      $users = pg_fetch_all($zap_na_users);
      
      for ($j=0; $j < count($users); $j++) { 
        $usid = $users[$j]['user_id'];
        $zap_na_name = pg_query($dbconn, "SELECT name from user_information where user_id = $usid");
        
        $names = pg_fetch_row($zap_na_name);
        
        for ($g=0; $g < count($names); $g++) {
          $zap_na_color = pg_query($dbconn, "SELECT isread from users_messages where user_id = $usid and message_id = $mes_id");
          $fl = pg_fetch_array($zap_na_color);
          $person =  [$names[$g],$fl[0]];

          if ($blue['vk_status'] != 2  && $blue['viber_status'] != 2 && $blue['telegram_status'] != 2) {
            echo ("<p style='color:blue'>".$person[0]);
            echo(' - не отправлено,</p> ');
          }else {
            if ($person[1] === 't'){ 
              $str = 'Доставлено и прочитано';
              echo ("<p style='color:green'>".$person[0].'-Статус --'.$str.',</p> ');
            }else {$str = 'Доставлено';
              echo ("<p style='color:red'>".$person[0].'-Статус --'.$str.',</p> ');
            }
            


        }
      }
    }
    }
    ?>
</table>
</body>
</html>


<?php


     
     



}else{ 
     header('Location: index.php');
}
?>
