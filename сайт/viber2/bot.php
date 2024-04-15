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

try {
    // create bot instance
    $bot = new Bot(['token' => $apiKey]);
    $bot
        ->onConversation(function ($event) use ($bot, $botSender, $log) {
            $log->info('onConversation ' . var_export($event, true));
            // this event fires if user open chat, you can return "welcome message"
            // to user, but you can't send more messages!
            return (new \Viber\Api\Message\Text())
                ->setSender($botSender)
                ->setText("Бот знает следующие команды:\n/sub <код с сайта> --  привязать мессенджер к своему аккаунту. Например: /sub 123456\n/read -- подтвердить, что вы прочитали сообщение. Нужно писать эту команду после каждого полученного объявления.");
        })
        // ->onText('|/help|', function ($event) use ($bot, $botSender, $log) {
        //     $log->info('onText whois ' . var_export($event, true));
        //     $bot->getClient()->sendMessage(
        //         (new \Viber\Api\Message\Text())
        //             ->setSender($botSender)
        //             ->setReceiver($event->getSender()->getId())
        //             ->setText("Бот понимает следующие команды: \n /sub <код с сайта> -- привязать аккаунт.")
        //     );
        // })
        //subscribe
        ->onText('|/sub|', function ($event) use ($bot, $botSender, $log) {
            global $dbconnect;
            $log->info('onText ' . var_export($event, true));
            $code = substr($event->getMessage()->getText(), 5);
            if (!is_numeric($code)){
                $bot->getClient()->sendMessage(
                    (new \Viber\Api\Message\Text())
                        ->setSender($botSender)
                        ->setReceiver($event->getSender()->getId())
                        ->setText('Код должен быть целым числом')
            );}
            else {
                $code = (int)$code;
                $id_viber = $event->getSender()->getId();
                $id_viber = (string)$id_viber;
                $sql = "UPDATE user_information SET id_viber = '$id_viber' WHERE code = $code;";
                pg_query($dbconnect, $sql);
                $bot->getClient()->sendMessage(
                    (new \Viber\Api\Message\Text())
                        ->setSender($botSender)
                        ->setReceiver($event->getSender()->getId())
                        ->setText("Ваши данные были успешно записаны.\nВаш код: ".$code."\nВаш ID: ".$id_viber)
                );}
        })
        // ->onPicture(function ($event) use ($bot, $botSender, $log) {
        //     $log->info('onPicture ' . var_export($event, true));
        //     $bot->getClient()->sendMessage(
        //         (new \Viber\Api\Message\Text())
        //             ->setSender($botSender)
        //             ->setReceiver($event->getSender()->getId())
        //             ->setText('')
        //     );
        // })
        ->onText('|/read|', function ($event) use ($bot, $botSender, $log) {
            global $dbconnect;
            $id_viber = $event->getSender()->getId();
            $id_viber = (string)$id_viber;
            $sql = "SELECT user_id FROM user_information WHERE id_viber = '$id_viber';";
            $res = pg_query($dbconnect, $sql);
            $user_id = pg_fetch_row($res)[0];
            $sql = "UPDATE users_messages SET isread = true WHERE user_id = $user_id;";
            pg_query($dbconnect, $sql);
            $log->info('onText whois ' . var_export($event, true));
            $bot->getClient()->sendMessage(
                (new \Viber\Api\Message\Text())
                    ->setSender($botSender)
                    ->setReceiver($event->getSender()->getId())
                    ->setText("Сообщения были отмечены как прочитанные.")
            );
        })
        ->onText('||', function ($event) use ($bot, $botSender, $log) {
            $log->info('onText whois ' . var_export($event, true));
            $bot->getClient()->sendMessage(
                (new \Viber\Api\Message\Text())
                    ->setSender($botSender)
                    ->setReceiver($event->getSender()->getId())
                    ->setText("Бот знает следующие команды:\n/sub <код с сайта> --  привязать мессенджер к своему аккаунту. Например: /sub 123456\n/read -- подтвердить, что вы прочитали сообщение. Нужно писать эту команду после каждого полученного объявления.")
            );
        })
        ->on(function ($event) {
            return true; // match all
        }, function ($event) use ($log) {
            $log->info('Other event: ' . var_export($event, true));
        })
        ->run();
} catch (Exception $e) {
    $log->warning('Exception: ', $e->getMessage());
    if ($bot) {
        $log->warning('Actual sign: ' . $bot->getSignHeaderValue());
        $log->warning('Actual body: ' . $bot->getInputBody());
    }
}
