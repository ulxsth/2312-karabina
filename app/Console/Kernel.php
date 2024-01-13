<?php

namespace App\Console;

use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SpotifyUserController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $userController = app(SpotifyUserController::class);
            $historyController = app(HistoryController::class);

            // TODO: chunkで分割して処理する
            $response = $userController->all();
            $users = $response->getData();
            foreach ($users as $user) {
                $historyController->fetch($user->spotify_id, $user->access_token, $user->refresh_token);
            }
        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
