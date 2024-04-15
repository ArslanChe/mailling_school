<?php
function login () {        
    if (isset($_SESSION['id'])){       
        if(isset($_COOKIE['login']) && isset($_COOKIE['password'])){           
        setCookie("login", "", time() - 1);
        setCookie("password","", time() - 1);          
        setcookie ("login", $_COOKIE['login'], time() + 3600);            
        setcookie ("password", $_COOKIE['password'], time() + 3600);                 
        return true;     
        } else {
            $rez =  pg_query($dbconn, "SELECT * FROM user_information id='{$_SESSION['id']}'");
            if (pg_num_rows($rez) == 1){ 
                $row = pg_fetch_assoc($rez);          
                setcookie ("login", $row['login'], time() + 3600);              
                setcookie ("password", md5($row['login'].$row['password']), time() + 3600); 
                return true;
            } else {
                return false;   
            }   
        }   
    } else  {       
        if(isset($_COOKIE['login']) && isset($_COOKIE['password'])){           
            $rez = pg_query($dbconn, "SELECT * FROM user_information WHERE login='{$_COOKIE['login']}'");      
            $row = pg_fetch_assoc($rez);     
            if(pg_num_rows($rez) == 1 && md5($row['login'].$row['password']) == $_COOKIE['password']){               
                $_SESSION['id'] = $row['id'];              
                return true;
            } else{ 
                setCookie("login", "", time() - 3600);
                SetCookie("password", "", time() - 3600);
                return false;
            }       
        } else {           
            return false;
        }   
    } 
}
?>