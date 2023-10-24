<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Class NotificationService
 * @package App\Services
 */
class NotificationService
{
    /**
     * This method takes care of calling the api endpoint to get current data 
     * and then processes it and passes it to the telegram service. 
     * 
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