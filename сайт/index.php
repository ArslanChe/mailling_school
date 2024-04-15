<?php
$conn_string = ("host=127.0.0.1 port=5432 dbname=messages user=messages password=MES_654GW");
$dbconn = pg_connect($conn_string);
session_start();

include_once('login.php');

function enter ($dbconn){ 
    $error = array(); 
    if ($_POST['login'] != "" && $_POST['password'] != "") {       
        $login = $_POST['login']; 
        $password = $_POST['password'];
        $rez = pg_query($dbconn, "SELECT * FROM user_information WHERE login = '$login';"); 
        if (pg_num_rows($rez) == 1){ 
            $row = pg_fetch_assoc($rez);            
            if (md5($password) == $row['password']){ 
                $_SESSION['id'] = $row['user_id'];
                $_SESSION['isadmin'] = $row['isadmin'];
                setcookie ("login", $row['login'], time() + 3600);
                setcookie ("password", md5($row['password']), time() + 3600);
                setcookie ("isadmin", $row['isadmin'], time() + 3600);
                return $error;          
            }else {       
                $error[] = "Неверный логин или пароль";                                       
                return $error;}       
        }else {    
            $error[] = "Неверный логин или пароль";           
            return $error;}   
    }else{       
        $error[] = "Поля не должны быть пустыми!";              
        return $error;} 
}





$base = pg_dbname();
if(login($dbconn)){ 
    $UID = $_SESSION['user_id'];
    $admin = $_SESSION['isadmin'];
    switch ($admin) {
        case 0:
            header('Location: UserPersonalArea.php');
            break;
        case 1:
            header('Location: AdminPersonalArea.php');
            break;
        }
}

if(isset($_POST['do_login'])){
    $error = enter($dbconn);
    if (count($error) == 0){
        $UID = $_SESSION['user_id'];
        $admin = $_SESSION['isadmin'];
        switch ($admin) {
            case 0:
                header('Location: UserPersonalArea.php');
                break;
            case 1:
                header('Location: AdminPersonalArea.php');
                break;
        }
    } else {
        echo ($error[0]);
    }
}


?>

<form action = "index.php" method = "post">

    <p>
        <p><strong>Ваш логин</strong>:</p>
        <input type = "text" name = "login">

    </p>

    <p>
        <p><strong>Ваш пароль</strong>:</p>
        <input type = "password" name = "password" type = "hidden">
        
    </p>
    <p>
        <button type ="submit" name = "do_login">Войти</button>
    </p>
</form>