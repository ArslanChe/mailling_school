<?php
$conn_string = ("host=127.0.0.1 port=5432 dbname=messages user=messages password=MES_654GW");
$dbconn = pg_connect($conn_string);
include_once('login.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <p><a href="AdminPersonalArea.php">На главную</a></p>

<form action = "Groups.php" method="post">
<p><textarea name="new_group_name" autofocus maxlength="32"></textarea></p>

<?php 
$rez = pg_query($dbconn, "SELECT DISTINCT ON (user_id) * FROM user_information;"); 
$mas = pg_fetch_all($rez);
$col = pg_num_rows($rez); 
for ($i=0; $i < $col; $i++) { 
    echo '<input type = "checkbox" name = "users[]" value = "'.$mas[$i]['user_id'].'"/>'.$mas[$i]['name']. '<br>';
}
?>

<p><button type ="submit" name = "create_new_group">Добавить группу</button></p>

<?php 
$rez = pg_query($dbconn, "SELECT DISTINCT ON (group_id) * FROM groups;"); 
$mas = pg_fetch_all($rez);
$col = pg_num_rows($rez); 
for ($i=0; $i < $col; $i++) { 
    echo '<input type = "checkbox" name = "groups[]" value = "'.$mas[$i]['group_id'].'"/>'.$mas[$i]['group_name'].'<br>';
}
?>

<button type ="submit" name = "Delete_some_groups">Удалить выбранные группы</button>
</p>
</form>
</head>
<body>
    


<?php

function create_group($dbconn){
    $group_name = $_POST['new_group_name'];
    $zapros_na_id_group =pg_query($dbconn, "SELECT nextval('group_id_generator');");
    $mas = pg_fetch_row($zapros_na_id_group);
    $new_group_id = $mas[0];

    for ($i=0; $i < count($_POST['users']); $i++) { 
        $user_id_chosen = $_POST['users'][$i];
        $zapros_na_add = pg_query($dbconn, "INSERT INTO groups (group_id, group_name, user_id) values ($new_group_id, '$group_name', $user_id_chosen);");
    }
    header("Refresh: 0");

}

function delete_groups($dbconn){
    for ($i=0; $i < count($_POST['groups']); $i++) { 
        $group_id_chosen = $_POST['groups'][$i];
        $zapros_na_del = pg_query($dbconn, "DELETE from groups WHERE group_id = $group_id_chosen;");
    }
    header("Refresh: 0");
}

if(login($dbconn) && $_COOKIE['isadmin'] == 1){
    if(isset($_POST['create_new_group']) && !empty($_POST['users']) && !empty($_POST['new_group_name'])){
        create_group($dbconn);


    }

    if(isset($_POST['Delete_some_groups']) && !empty($_POST['groups'])){
        delete_groups($dbconn);
    }
} else{
    header('Location: index.php');
}
?>
