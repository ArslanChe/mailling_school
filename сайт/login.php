<?php
function login($dbconn){       
    if (isset($_SESSION['user_id'])){
        if(isset($_COOKIE['login']) && isset($_COOKIE['password'])&&  isset($_COOKIE['isadmin'])){
            setCookie("login", "", time() - 3600);
            setCookie("password","", time() - 3600);
            setCookie("isadmin","", time() - 3600);
            setcookie ("login", $_COOKIE['login'], time() + 3600);
            setcookie ("password", $_COOKIE['password'], time() + 3600);
            setcookie ("isadmin", $_COOKIE['isadmin'], time() + 3600);
            return true;
        } else {
            $rez =  pg_query($dbconn, "SELECT * FROM user_information where id='{$_SESSION['id']}'");
            if (pg_num_rows($rez) == 1){
                $row = pg_fetch_assoc($rez);          
                setcookie("login", $row['login'], time() + 3600);              
                setcookie("password", md5($row['password']), time() + 3600);
                setcookie ("isadmin", $row['isadmin'], time() + 3600);
                return true;
            } else {
                return false;   
            }   
        }   
    } else  {       
        if(isset($_COOKIE['login']) && isset($_COOKIE['password']) && isset($_COOKIE['isadmin'])){           
            $rez = pg_query($dbconn, "SELECT * FROM user_information WHERE login='{$_COOKIE['login']}'");
            $row = pg_fetch_assoc($rez);     
            if(pg_num_rows($rez) == 1 && md5($row['password']) == $_COOKIE['password']){               
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['isadmin'] = $row['isadmin'];     
                return true;
            } else{ 
                setCookie("login", "", time() - 3600);
                SetCookie("password", "", time() - 3600);
                setCookie("isadmin","", time() - 3600);
                return false;
            }       
        } else {           
            return false;
        }   
    }
}
?>