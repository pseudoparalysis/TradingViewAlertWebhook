<?php
if($_SERVER["REQUEST_METHOD"] === "POST") {

	include_once "twilioContent.php";

	$rawMessage = file_get_contents('php://input');
	$message = "Trading View Alert:\n<strong>" . $rawMessage . "</strong>";

	//Telegram Information
	$chatIds = array("<telegram-chat-id-to-send-alert-to>");

  	for($i = 0; $i < count($chatIds); $i++) {
	    telegramApiRequest("sendMessage", array(
	    	"chat_id" => $chatIds[$i],
	    	"text" => $message,
	    	"disable_web_page_preview" => true,
	    	"parse_mode"=> "HTML"
	    ));
	}


	foreach(NUMBERS_TO_CALL as $who => $nums) {
		if(stripos($rawMessage, $who) !== false) {
			foreach($nums as $j => $num) {
				$res = sendTwilioCallRequest(TWILIO_NUMBER, $num, TWILIO_ACCT_SID, TWILIO_AUTH_TOKEN);

				//Capture twilio call resource api request error, not failed/unanswered calls.
				if(!$res[0] || $res[1] != 201) {
				  	for($i = 0; $i < count($chatIds); $i++) {
					    telegramApiRequest("sendMessage", array(
					    	"chat_id" => $chatIds[$i],
					    	"text" => $who . " Failed",
					    	"disable_web_page_preview" => true,
					    	"parse_mode"=> "HTML"
					    ));
					}

					if(array_key_exists($who, BACKUP_RECIPIENTS)) {
						$backupNums = BACKUP_RECIPIENTS[$who];
						foreach($backupNums as $k => $num) {
							//If this fails, thats it.
							sendTwilioCallRequest(TWILIO_NUMBER, $num, TWILIO_ACCT_SID, TWILIO_AUTH_TOKEN);
						}
					}
				}
			}
		}
	}
}

function telegramApiRequest($method, $data) {
	$jsonString = "";
	$token = "<your-telegram-bot-token>";

	$header = array(
		"Content-Type: application/json",
		"Accept: application/json"
	);

	$url = 'https://api.telegram.org/bot' . $token . '/' . $method;
	try {
		$jsonString = json_encode($data);
	} catch(Exception $e) {
		return;
	}

	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_URL, $url);
	curl_setopt($curlHandle, CURLOPT_POST, true);
	curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $jsonString);
	curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $header);
	//RETURNTRANSFER change return value of curl_exec to the actual return string isntead of true or false
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($curlHandle);
	curl_close($curlHandle);
}

?>