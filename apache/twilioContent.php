<?php

//Take note that depending on how the NUMBERS_TO_CALL and BACKUP_RECIPIENTS are setup, there is a possibility of infinite calling loop
// Recipient 1 busy/no-answer -> Call Recipient 2 (Recipient 1's backup) busy/no-answer -> Call Recipient 1 (because Recipient 1 is Recipient 2's backup) busy/no-answer-> ...
// The loop will break when a recipient in the loop answers the call or rejects a call.

//Twilio Information
const TWILIO_NUMBER = "<your-twilio-number>";
const NUMBERS_TO_CALL = array(
	"NAME_1" => array("<NAME_1's number>"),
	"NAME_2" => array("<NAME_2's 1st number>", "<NAME_2's 2nd number>"),
	"NAME_3" => array("<NAME_3's number>")
);
const BACKUP_RECIPIENTS = array(
	"NAME_1" => array("<NAME_1's backup number>"),
	"NAME_2" => array("<NAME_2's backup number>"),
	"NAME_3" => array("<NAME_3's backup number>")
);
const TWILIO_ACCT_SID = "<your-twilio-acct-ssid>";
const TWILIO_AUTH_TOKEN = "<your-twilio-acct-token>";

function sendTwilioCallRequest($twilioNumber, $recipient, $acctSid, $authToken) {
	$url = "https://api.twilio.com/2010-04-01/Accounts/" . $acctSid . "/Calls.json";
	$curlHandle = curl_init();
	// Timeout is set to previous activation of voicemail, which is counted as call answered.
	$postFields = array(
		"To" => $recipient,
		"From" => $twilioNumber,
		"Twiml" => "<Response><Say>Wake up</Say></Response>",
		"StatusCallbackEvent" => "completed",
		"StatusCallback" => "https://" . $_SERVER["SERVER_NAME"] . "/twilio-callback.php",
		"Timeout" => "15"
	);

	curl_setopt($curlHandle, CURLOPT_URL, $url);
	curl_setopt($curlHandle, CURLOPT_POST, true);
	curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $postFields);
	curl_setopt($curlHandle, CURLOPT_USERPWD, $acctSid . ":" . $authToken); 
	//RETURNTRANSFER change return value of curl_exec to the actual return string instead of true or false
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($curlHandle);
	$responseCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
	curl_close($curlHandle);

	return array(
		$response,
		$responseCode
	);
	
}


?>