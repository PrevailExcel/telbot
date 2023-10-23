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
    public function getList(TelegramService $telegramService)
    {
        $url = env('API_URL', 'https://api-mystland.up.railway.app/api/getUsers');
    
        $response = Http::get($url);
    
        $old_total = Cache::get('old_total');
    
        if ($response->ok()) {
            $current_total = $response->json('totalUsers');
            if (!$old_total)
                $old_total = Cache::forever('old_total', $current_total);
    
            //Compare the old total and new returned total
            if ($current_total > $old_total) {
    
                //if it's greater, return the difference and call the telegram service
                $difference = $current_total - $old_total;
    
                $users = $response->json('lastRegistered');
    
                return $telegramService->handle(collect($users)->slice(-$difference));
            }
        }
        return true;
    }

}