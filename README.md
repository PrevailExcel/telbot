# Telegram Based Notification System
> System Agnostic Scheduled Notification System powered by a Telegram Bot Service

## How it works
- When command is ran, it retrives data from the API
- It checks if there's an existing `totalUsers` saved, if there's none, it saves it.
- It checks if the returned (current) `totalUsers` is higher than the saved one.
- If it is differennt, it get's the difference and then gets from the response the latest number of users according to the difference of the `totalUsers` from `latestRegistered`. 
- If `old_total` is *140011* and `current_total` is *140016*, the differene is *5*; therefore it gets the first 5 user objects from `latestRegistered` array.
- It then parse this information to the BotService that pushes it to the telegram channel specified.
- If there is no difference, it means there is no new registration yet, so the script is ended.

## Code Structure
This system is built with **Laravel Framework (8.0)** and **Botman Framework (2.0)**.
The core files are presented below
#### Services `/app/Services/`
- NotificationService
- TelegramService
#### Commands `/app/Console/Commands/`
- NotificationCommand
#### Kernel `/app/Console/`
- Kernel

##### NotificationService:
This class takes care of calling the api endpoint to get current data and then processes it and passes it to the telegram service.
```php
class NotificationService
{
    /**
     * This class takes care of calling the api endpoint to get current data 
     * and then processes it and passes it to the telegram service. 
    */
    public function run()
    {
        $url = env('API_URL', 'https://api-mystland.up.railway.app/api/getUsers');
        $response = Http::get($url);    
        $old_total = Cache::get('old_total');
    
        if ($response->ok()) {
            $current_total = $response->json('totalUsers');

            // If old_total is not available, cache it forever.
            if (!$old_total)
                $old_total = Cache::forever('old_total', $current_total);
    
            //Compare the old total and new returned total
            if ($current_total > $old_total) {
    
                //if it's greater, return the difference and call the telegram service
                $difference = $current_total - $old_total;
    
                //process data for parsing
                $users = [];
                $users['list'] = array_slice($response->json('lastRegistered'), 0, $difference);
                $users['active'] = $response->json('activeUsers');
                $users['total'] = $response->json('totalUsers');

                //Send code to telegram service
                $telegramService = new TelegramService();
                return $telegramService->handle(collect($users));
            }
        }
        return true;
    }
}
```

##### TelegramService:
This class takes initiates the bot and sends out the notifications.
```php

//Title message: Showing number of new users, active users and total users
$message = OutgoingMessage::create('You have <b>' . count($users['list']) . "</b> New Users \n \nActive Users: <b>" . $users['active'] . "</b> \nTotal Users: <b>" . $users['total'] . "</b> \n");
$botman->say($message, $channels, \BotMan\Drivers\Telegram\TelegramDriver::class, ["parse_mode" => "HTML"]);

// Create message for each user and send to channel
foreach ($users['list'] as $user) {

    // Build message object
    $confirmed = $user['emailConfirmed'] ? 'Yes' : 'No';
    $referal = $user['isReferral'] ? 'Yes' : 'No';
    $date = Carbon::parse($user['registrationTimestamp'])->format('d M Y');

    $messageString = "NEW USER \n \nUsername: <b>" . $user['login'] . "</b> \nWallet: <b>" . $user['wallet'] . "</b> \nReferral Code: <b>" . $user['referralCode'] . "</b> \nDate: <b>" . $date . "</b> \nConfirmed Email: <b>" . $confirmed . "</b> \nUser was referred: <b>" . $referal . "</b>";

    $message = OutgoingMessage::create($messageString);
    $botman->say($message, $channels, \BotMan\Drivers\Telegram\TelegramDriver::class, ["parse_mode" => "HTML"]);
}
```

##### NotificationCommand:
This is the command that will be called to run script. Command is registered, decribed and handled here.
```php

protected $signature = 'notice:board';
    
    ***
    
public function handle()
{
    $this->info('Starting script.');
    $notify = new NotificationService();
    $this->info('Script initiated.');
    $notify->run();

    $this->info('Notification script ran successfully.');
}
```

##### Kernel:
This is the command is scheduled.
```php
protected function schedule(Schedule $schedule)
{        
    //Run this command every minute
    $schedule->command('notice:board')->everyMinute();
    //->everyFiveMinutes();
    //->everyThirtyMinutes();
    // and more
}
```

## How to Install and Run Script
PHP 8.1 or above must be installed on the system. 
#### Prerequisites to installing system
- -OS - Ubuntu or any Linux distro
- PHP - 8.1 above
- Web Server - Apache2 (prefered) or Nginx
- Composer - PHP Dependency manager
- Git (Optional) - depends on how this script is delivered finally.


## Installation
This system can be installed like any other laravel system.
#### If delivered via zip file
- Unzip source code into root folder, eg `/var/www/html/notification`.
- Open `.env` and add endpoint, telegram bot token and channel id as specified.
- To test if it's ready to go, run this command to execute the command: `php artisan notice:board`.

#### If delivered via Github Repo
- Pull source code into root folder, eg `/var/www/html/notification`.
- Copy and paste `.env.example` file and rename to `.env`.
- Open `.env` and add endpoint, telegram bot token and channel id as specified.
- Run this command via terminal inside the root folder: `composer install`.
- Then run this command via terminal: `php artisan key:generate`.
- To test if it's ready to go, run this command to execute the command: `php artisan notice:board`.

## Installing on Other Platforms
If system is to be hosted on any other system (Cloud or Shared Hosting), The process is very sismilar, differences will be based on individual platforms.
If in doubt, get instructions on how to install laravel on that particular platform.
Or you can reach to the developer for guidance.

### Env Confirguration
Add the correct values for the following keys:

| Key | Value |
| ------ | ------ |
| API_URL | "https://api-mystland.up.railway.app/api/getUsers" |
| TELEGRAM_TOKEN | "**************************" |
| TELEGRAM_CHANNEL_ID | "-100************" |

## Extention
This system can be extended as user sees fit. 
The codes are modular with proper seperation of concerns. 
It is well documented and commented.
Written with well optimized code.