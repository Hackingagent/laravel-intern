<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-inactive
                            {--days=7 : Number of days of inactivity before sending reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for inactive users and send reminder notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inactivityDays = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($inactivityDays);

        $this->info("Checking for users inactive since {$cutoffDate->format('Y-m-d H:i:s')}...");

        // Find users who:
        // 1. Have last_login_at before the cutoff date
        // 2. Either have never received a reminder or their last reminder was sent before today
        $inactiveUsers = User::where('last_login_at', '<', $cutoffDate)
            ->where(function ($query) {
                $query->whereNull('reminder_sent_at')
                    ->orWhereDate('reminder_sent_at', '<', Carbon::today());
            })
            ->get();

        if ($inactiveUsers->isEmpty()) {
            $this->info('No inactive users found that need reminders.');
            return Command::SUCCESS;
        }

        $this->info("Found {$inactiveUsers->count()} inactive user(s) to send reminders to.");

        $dispatchedCount = 0;
        foreach ($inactiveUsers as $user) {
            // Dispatch the job to the queue
            SendReminderJob::dispatch($user);
            $dispatchedCount++;

            Log::info('Dispatched reminder job for inactive user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'last_login_at' => $user->last_login_at,
            ]);

            $this->line("  - Dispatched reminder for user: {$user->email}");
        }

        $this->info("Successfully dispatched {$dispatchedCount} reminder job(s) to the queue.");

        return Command::SUCCESS;
    }
}
