<?php

if($_SERVER["REQUEST_METHOD"] === "POST") {
	include_once "twilioContent.php";

	if(array_key_exists("CallStatus", $_POST)) {
		$callStatus = $_POST["CallStatus"];

		// At the time of writing, no-answer means the recipent did not pick up
		// 	or the recipient's phone is turned off.
		// Behaviour might vary from TelCo. to TelCo.
		// If callStatus is busy or no-answer. It means that there is a chance the
		//	recipient did not receive the call notification at all.
		// Therefore, it is best to notify the backup recipient.
		// At the time of writing, if a call is rejected, the call status will be 'failed'
		// 	SipResponseCode can be used to different between different types of call outcome
		//	But it might not be necessary
		if($callStatus == "no-answer" || $callStatus == "busy") {
			$recipient = $_POST["To"];
			$caller = $_POST["From"];

			//To make sure that random people cannot use this callback api.
			if($caller == TWILIO_NUMBER) {
				$recipientCodeWord = false;

				foreach(NUMBERS_TO_CALL as $who => $nums) {
					$breakOuterLoop = false;
					foreach($nums as $j => $num) {
						if($num === $recipient) {
							$recipientCodeWord = $who;
							$breakOuterLoop = true;
							break;
						}
					}
					if($breakOuterLoop) {
						break;
					}
				}
				if($recipientCodeWord !== false) {
					if(array_key_exists($recipientCodeWord, BACKUP_RECIPIENTS)) {
						$backupNums = BACKUP_RECIPIENTS[$recipientCodeWord];
						foreach($backupNums as $k => $num) {
							// If this fails, that's it. No recursive search for backups of backups if call resource api request fails (Not no-answer or phone call failure).
							sendTwilioCallRequest(TWILIO_NUMBER, $num, TWILIO_ACCT_SID, TWILIO_AUTH_TOKEN);
						}
					}
				}
			}
		}
	}

}

?>