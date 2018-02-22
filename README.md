# Telegram API WebServer in PHP/RDBMS
Do you want to manage a Telegram bot on a web server with PHP+SQL architecture?

Do you want to integrate your client applications (Java Desktop, Android, iOS, whatever) with Telegram API, so that you/your application can send/receive messages and photos?

Then this web server, lightweight and easy to configure, will do for your case.

## Requirements
1. Any web hosting service with PHP+SQL plan (Even free tier options, for example https://www.000webhost.com/);
2. The API key of your Telegram bot (Read Step 1 to discover how to create a bot: https://github.com/fabnicolas/telegram-bot-readytouse/blob/master/README.md)

## Setup (Web Server)
1. Execute this SQL code (Tested on MySQL):
```sql
CREATE TABLE IF NOT EXISTS `updates` (
  `update_id` bigint(20) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `from_id` bigint(20) NOT NULL,
  `from_username` varchar(255) NOT NULL,
  `date` bigint(20) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`update_id`,`message_id`)
)
```
This table will store all the basic contents relative to messages sent from users towards Telegram bot.

2. Create file `include/config.php` with the following content:
```php
<?php
define('APP_TELEGRAM_SECRET_TOKEN_STRING', "your_key");
define('DB_HOST', "localhost");
define('DB_USER', "your_username");
define("DB_PASSWORD", "your_password");
define('DB_NAME', "your_database");
?>
```

3. Start use the web server!

## Coded functionalities
Make sure you use the following snippets when `TelegramBot` and `DB` objects are ready to use.
Copy `send_message.php` and use the copy as easiest template to extend functionalities.

### Send a message
```php
$chat_id=12345678; // Chat ID of the target the bot sends message to.
$telegram_bot->sendMessage("I'm a bot; I'll be loyal forever to you, unless you make me crash.", $chat_id);
```

### Send a photo
```php
$image_path='./image.png';  // Any path; send_photo.php contains an implementation to handle images sent by user
$chat_id=12345678; // Chat ID of the target the bot sends message to.
$telegram_bot->sendPhoto($image_path, $chat_id, function() use ($image_path){
	unlink($image_path); // Callback to remove image from web server after you sent it.
});
```

### Get incoming messages from users to Telegram bot
Be careful to not expose this code as an API for users; keep user privacy in mind.

The easiest way is:
```php
$result = $telegram_bot->getParsedUpdates();
if($result){
    foreach($result as $key=>$message){
    	// Analyze each $message as an array (update_id, message_id, from_id, from_username, date, text).
    }
}else{
    // No new messages incoming.
}
```
Check https://core.telegram.org/bots/api#getting-updates , get_updates.php and TelegramBot class for more details about the structure and the underlying APIs.

In particular, `get_updates.php` in the web server takes care of handling new messages and saving them into a database table; in future it will be possible to retrieve saved messages (And delete them to mark them as read) thanks to the API wrapper. 



To retrieve more details and handle data manually:
```php
    // For some parameters details/usage, check get_updates.php, TelegramBot class and .
    $update_id=0;   // Update ID; clients send 0 the first time, then uses last possible number.
    $offset=0; // Optional, default 0; used to acknowledge messages. Read Telegram API for details.
    $limit=100; // Optional, default 100; used to limit the number of updates handled at the same time.
    $timeout=0; // Optional, default 0 (short polling); if you want to use long polling, set a value >0.
    $result = json_decode($telegram_bot->getUpdates($update_id));
    // Analyze the JSON structure to find interesting data.
```

### Get incoming messages from a specific user
Use the same request as before; parse the JSON to retrieve matching `from_id` that equals a given `chat_id`.


## API endpoints
The following endpoints were heavily tested with Advanced Rest Client. Postman is an equivalent. Treat this server like a REST server.

As base URL we will consider https://your_url.com/ for examples.
Remember that each Telegram user has its own chat_id: for testing purposes, retrieve yours by contacting https://t.me/RawDataBot ; also remember the user you want to send requests to must have /start-ed your Telegram bot.

### Send a message
```
POST https://your_url.com/send_message.php
Body Content-Type: application/x-www-form-urlencoded
Payload: chat_id=YOUR_CHAT_ID; message: Your message
```
Answer (success):
```json
{status: 0, message: 'Message sent.'}
```
Effect: Telegram bot will send "Your message" to the given user with that chat_id.

### Send a photo
```
POST https://your_url.com/send_photo.php
Body Content-Type: multipart/form-data
Payload: chat_id=YOUR_CHAT_ID; image=[YOUR IMAGE]
```
Answer (success):
```json
{status: 0, message: 'Photo sent.'}
```
Effect: Telegram bot will send the photo attached to the request to the given user with that chat_id.

### Get incoming messages from a specific user
Notice that this is not implemented yet; the actual implementation retrieves all users datas for testing purposes --- remember that people privacy is important and you should not expose this API.

Notice: `update_id` is 0 at start of your application and should become last `update_id`+1 to ensure new messages are handled correctly.
```
POST https://your_url.com/get_updates.php
Body Content-Type: application/x-www-form-urlencoded
Payload: chat_id=YOUR_CHAT_ID; update_id=UPDATE_ID_CURRENT_VALUE
```
Sample answer (success):
```json
{
    status: 0, 
    message: [
        {
        "update_id": 100001,
        "message_id": 1,
        "from_id": 12345678,
        "from_username": "fabnicolas",
        "date": 1519320627,
        "text": "Hello! Are you there?"
        },
        {
        "update_id": 100002,
        "message_id": 2,
        "from_id": 12345678,
        "from_username": "fabnicolas",
        "date": 1519320668,
        "text": "Are you a bot?"
        }
    ]
}
```
Effect: the effect is determined by your application (That parses this JSON as an array of messages and decides what to do for each message).