<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The user instance.
     */
    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Simulate sending reminder email
        $message = sprintf(
            "Reminder: User '%s' (%s) has been inactive since %s. Please come back and check out our new features!",
            $this->user->name,
            $this->user->email,
            $this->user->last_login_at?->format('Y-m-d H:i:s') ?? 'unknown'
        );

        // Log the reminder
        Log::info('Sending reminder to inactive user', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'last_login_at' => $this->user->last_login_at,
            'message' => $message,
        ]);

        // Update the reminder_sent_at timestamp to prevent duplicate processing
        $this->user->update(['reminder_sent_at' => now()]);
    }
}
