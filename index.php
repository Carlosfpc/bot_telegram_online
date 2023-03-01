<?php
require_once 'bot.php';

$bot = new TelegramBot('TOKEN');
$bot->sendVoice();
$bot->sendMessage();
$bot->sendChatGPT();
?>
