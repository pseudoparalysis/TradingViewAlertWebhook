# Important
Any new updates/improvements will not be pushed here. This is created on adhoc basis.

# TradingView Webhook for Alerts
This webhook can propagate a TradingView alert to Telegram channels or chats, and call multiple phone numbers via Twilio.


# How It Works
Depending on the TradingView subscription plan, TradingView may allow users to enter a webhook that will be triggered when an alert is hit.
A HTTP/HTTPS POST request will be sent to the webhook and the body of the request contains the alert message that the user specifies when setting the alert.

This webhook checks for specific substrings in the TradingView alert message to decide which number to call when the alert is hit. (See below for example)

# Things To Configure Before Use
In `telegram/telegram-helpers.js`:
- Enter the IDs of the Telegram chats/channels that you want the alert to be sent to in `chatIds` array.
- Set your Telegram bot token as the `botToken` variable.

In `twilio/twilio-helpers.js`:

Enter your Twilio number as the `twilioNum` property in the `contactInfo` object.

Edit the `numToCall` property in `contactInfo` to specify which number to call when an alert is hit.
For example, if the property is defined as shown below:
```
const contactInfo = {
	...
	"numToCall": {
		"NAME_1": ["<NAME_1's number>"],
		"NAME_2": ["<NAME_2's 1st number>", "<NAME_2's 2nd number>"],
		"NAME_3": ["<NAME_3's number>"]
	},
	...
}
```
and the TradingView alert message is `EURUSD crosses 1.2 NAME_1 NAME_2`, only all the numbers that corresponds to `NAME_1` and `NAME_2` will be called.

Edit the `backupRecipients` property in `contactInfo` to specify which backup number to call if a call fails.
For example, if the array is defined as shown below:
```
const contactInfo = {
	...
	"backupRecipients": {
		"NAME_1": ["<NAME_1's backup number>"],
		"NAME_2": ["<NAME_2's backup number>"],
		"NAME_3": ["<NAME_3's backup number>"]
	},
	...
}
```
and the call to a number of `NAME_1` fails, then all the backup numbers that corresponds to `NAME_1` will be called. The other backup numbers will not be called.

Enter your Twilio authentication information in the `authInfo` object.


**Note: The name that corresponds to the numbers can be any string, need not be in the "NAME_number" format. Also, phone numbers should include country code. See Twilio API documentation for more information.**