
const request = require("request");


const contactInfo = {
	"twilioNum": "<your-twilio-number>",
	"numToCall": {
		"NAME_1": ["<NAME_1's number>"],
		"NAME_2": ["<NAME_2's 1st number>", "<NAME_2's 2nd number>"],
		"NAME_3": ["<NAME_3's number>"]
	},
	"backupRecipients": {
		"NAME_1": ["<NAME_1's backup number>"],
		"NAME_2": ["<NAME_2's backup number>"],
		"NAME_3": ["<NAME_3's backup number>"]
	}
}

const authInfo = {
	"acctSid": "<your-twilio-acct-ssid>",
	"acctAuthToken": "<your-twilio-acct-token>"
}

function sendCallRequest(twilioNumber, recipient, acctSid, authToken, statusCallbackUrl, callback) {
	const url = "https://api.twilio.com/2010-04-01/Accounts/" + acctSid + "/Calls.json";
	
	const payload = {
		"To": recipient,
		"From": twilioNumber,
		"Twiml": "<Response><Say>Wake up</Say></Response>",
		"StatusCallbackEvent": "completed",
		"StatusCallback": statusCallbackUrl,
		"Timeout": "15"
	}

	request.post(url, callback).form(payload).auth(acctSid, authToken);

	
}

function handleStatusCallback(reqBody, statusCallbackUrl) {
	const callStatus = reqBody["CallStatus"];
	if(callStatus) {
		if(callStatus === "no-answer" || callStatus === "busy") {
			const recipient = reqBody["To"];
			const caller = reqBody["From"];

			if(caller === contactInfo["twilioNum"]) {
				let recipientCodeWord = false;
				for(const who in contactInfo["numToCall"]) {
					let breakOuterLoop = false;
					contactInfo["numToCall"][who].every(function(num){
						if(num === recipient) {
							recipientCodeWord = who;
							breakOuterLoop = true;
							return false;
						}
						return true;
					});

					if(breakOuterLoop) {
						break;
					}
				}
				
				if(recipientCodeWord) {
					const backupNums = contactInfo["backupRecipients"][recipientCodeWord];
					if(backupNums) {
						backupNums.forEach(function(num) {
							sendCallRequest(contactInfo["twilioNum"], num, authInfo["acctSid"], authInfo["acctAuthToken"], statusCallbackUrl);
						});
					}
				}
			}
		}
	}
}

function getKeyByValue(object, value) {
  return Object.keys(object).find(key => object[key] === value);
}

module.exports = {
	contactInfo: contactInfo,
	authInfo: authInfo,
	sendCallRequest: sendCallRequest,
	handleStatusCallback: handleStatusCallback
}