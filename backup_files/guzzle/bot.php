<?php
include 'FbBot.php';

$hubVerifyToken = 'mastermaimai';
$accessToken = 'EAABxniO6VYQBANVKSOirZB6pT7FHp2LGetRostBNQFZBn9yeY2ct2rrqtZAqqQcNPK0kJa014LmXyv7sP5Vk2K2jYs3Bn39fYwaEDp8hyIwxMwK2CVOQ6B2Qz37wFZAD1ctugY2NdZBkTSfA4roBToXVqZAN2KFWdxnLbttHcd1wZDZD';

//$challenge = '$_REQUEST['hub_challenge']';
//$tokken = '$_REQUEST['hub_verify_token']';


$bot = new FbBot();
$bot->setHubVerifyToken($hubVerifyToken);
$bot->setaccessToken($accessToken);
//echo $bot->verifyTokken($tokken,$challenge);

$input = json_decode(file_get_contents('php://input'), true);
$message = $bot->readMessage($input);
$textmessage = $bot->sendMessage($message);