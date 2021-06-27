const express = require('express');
const twilio = require("./twilio/twilio-helpers");
const telegram = require("./telegram/telegram-helpers");
const request = require("request");
const app = express();
const port = 80;

const bodyParser = require('body-parser');
const twilioNum = twilio["contactInfo"]["twilioNum"];
const numToCall = twilio["contactInfo"]["numToCall"];
const backupRecipients = twilio["contactInfo"]["backupRecipients"];
const twilioAuth = twilio["authInfo"];

app.post('/twilio/events', bodyParser.urlencoded({extended: true}), (req, res) => {
  const body = req.body;
  const statusCallbackUrl = req.protocol + '://' + req.get('host') + req.originalUrl;
  twilio.handleStatusCallback(body, statusCallbackUrl);
});

app.post("/trading-view-webhook", bodyParser.text(), (req, res) => {
	const body = req.body;
	const message = "Trading View Alert:\n<strong>" + body + "</strong>";
	const statusCallbackUrl = req.protocol + '://' + req.get('host') + "/twilio/events";

	telegram.sendMessage(message);

	for(const who in numToCall) {
		if(message.toUpperCase().includes(who)) {
			const nums = numToCall[who];
			nums.forEach(function(num) {
				twilio.sendCallRequest(twilioNum, num, twilioAuth["acctSid"], twilioAuth["acctAuthToken"], statusCallbackUrl, function(err, res, body) {
					if(res.statusCode !== 201) {
						telegram.sendMessage(who + " Failed");
						if(backupRecipients[who]) {
							const backupNum = backupRecipients[who];
							backupNum.forEach(function(num) {
								twilio.sendCallRequest(twilioNum, num, twilioAuth["acctSid"], twilioAuth["acctAuthToken"], statusCallbackUrl);
							});
						}
					}

				});
			});
		}
	}

});

app.listen(port, () => {
  console.log(`Server started, listening at http://localhost:${port}`);
});