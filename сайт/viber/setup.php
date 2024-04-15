<?php
require_once("../vendor/autoload.php");
use Viber\Client;
$apiKey = '4c6d5b5705400ab9-7031cdd0d5163574-7af45e5976fbc82a';
$webhookUrl = 'https://test.fml31.ru/informer/ViberBot/viberbot.php';
try {
    $client = new Client([ 'token' => $apiKey ]);
    $result = $client->setWebhook($webhookUrl);
    echo "Success!\n";
} catch (Exception $e) {
    echo "Error: ". $e->getError() ."\n";
}
?>