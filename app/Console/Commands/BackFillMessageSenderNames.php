<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillMessageSenderNames extends Command
{
    protected $signature = 'messages:backfill-names';
    protected $description = 'Backfill sender names for all messages';

    public function handle(UserService $userService)
    {
        $this->info('Starting backfill of message sender names...');
        
        $total = Message::whereNull('sender_name')->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Message::whereNull('sender_name')
            ->chunkById(100, function($messages) use ($userService, $bar) {
                foreach ($messages as $message) {
                    try {
                        $details = $userService->userDetails($message->sender_username);
                        if (isset($details['displayName'])) {
                            $message->sender_name = $details['displayName'];
                            $message->save();
                        } else {
                            Log::info("No display name found for user: {$message->sender_username}");
                        }
                    } catch (\Exception $e) {
                        Log::error("Error fetching name for user {$message->sender_username}: " . $e->getMessage());
                    }
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Backfill complete!');
    }
}