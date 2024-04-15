<?php

/**
 * Before you run this example:
 * 1. install monolog/monolog: composer require monolog/monolog
 * 2. copy config.php.dist to config.php: cp config.php.dist config.php
 *
 * @author Novikov Bogdan <hcbogdan@gmail.com>
 */

require_once '/var/www/html/informer/viber2/vendor/autoload.php';

use Viber\Bot;
use Viber\Api\Sender;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$config = require('/var/www/html/informer/viber2/config.php');
$apiKey = $config['apiKey'];

// reply name
$botSender = new Sender([
    'name' => 'Informer',
    'avatar' => 'https://cdn.cloudflare.steamstatic.com/steamcommunity/public/images/avatars/62/621772951fce59520db71f463320cfdaf1d4b18b_full.jpg',
]);

//connect to database
$dbconnect = pg_connect("host=127.0.0.1 port=5432 dbname=messages user=messages password=MES_654GW");
if (!$dbconnect){
    die("Could not connect to database: ".pg_last_error());
}

// log bot interaction
$log = new Logger('bot');
$log->pushHandler(new StreamHandler('/tmp/bot.log'));



$res = pg_query($dbconnect, "SELECT message_id FROM new_messages WHERE viber_status = 0 or viber_status = 1;");
    if (!$res) {
        die("ERROR: ".pg_last_error());
    }
$messages = pg_fetch_all($res);
if (!$messages){
    die();
}

foreach ($messages as &$message_id_row) {
    $message_id = $message_id_row['message_id'];
    $res =  pg_query($dbconnect, "UPDATE new_messages SET viber_status = 1 WHERE message_id = $message_id;");
    if (!$res) {
        die("ERROR: ".pg_last_error());
    }


    $res = pg_query($dbconnect, "SELECT group_id FROM message_information WHERE message_id = $message_id");
    if (!$res) {
        die("ERROR: ".pg_last_error());
    }
    $group_id = pg_fetch_row($res)[0];
    if (!$group_id) {
        continue;
    }


    $res = pg_query($dbconnect, "SELECT user_id FROM groups WHERE group_id = $group_id;");
    if (!$res) {
        die("ERROR: ".pg_last_error());
    }
    $user_ids = pg_fetch_all($res);


    foreach ($user_ids as &$user_id_row){
        $user_id = $user_id_row['user_id'];
        if (!$user_id) {
            continue;
        }
        $res = pg_query($dbconnect, "SELECT message FROM message_information WHERE message_id = $message_id;");
        if (!$res) {
            die("ERROR: ".pg_last_error());
        }
        $msg_text = pg_fetch_row($res)[0];

        $res = pg_query($dbconnect, "SELECT id_viber FROM user_information WHERE user_id = $user_id");
        if (!$res) {
            die("ERROR: ".pg_last_error());
        }
        $user_viber_id = pg_fetch_row($res)[0];

        try {
            $bot = new Bot(['token' => $apiKey]); 
            $bot->getClient()->sendMessage(
                (new \Viber\Api\Message\Text())
                    ->setSender($botSender)
                    ->setReceiver($user_viber_id)
                    ->setText($msg_text)
                    //->setText("TEST!")
                );
            $jret = [
                'success' => true,
                'result' => 'Ok'
            ];
        } catch (Exception $e) {
            $jret = [
                'success' => false,
                'result' => 'BOT Exception: '.$e->getMessage()
            ];
        }

        var_dump($jret);
                
            }

    $res = pg_query($dbconnect, "UPDATE new_messages SET viber_status = 2 WHERE message_id = $message_id;");
    if (!$res) {
        die("ERROR: ".pg_last_error());
    }
}
