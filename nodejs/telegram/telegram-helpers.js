
const request = require("request");

const botToken = "<your-telegram-bot-token>";
const chatIds = ["<telegram-chat-id-to-send-alert-to>"];


function sendTelegramApiPostRequest(method, msg) {
	const url = "https://api.telegram.org/bot" + botToken + "/" + method;

	request({url: url, method: "POST", json: msg});
}


function sendMessage(msg) {
	chatIds.forEach(function(id) {
		sendTelegramApiPostRequest("sendMessage", {"chat_id": id, "text": msg, "disable_web_page_preview": true, "parse_mode": "HTML"});
	});
}

module.exports = {
	sendMessage: sendMessage
}