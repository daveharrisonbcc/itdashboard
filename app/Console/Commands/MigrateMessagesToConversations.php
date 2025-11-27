<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Conversation;
use App\Models\ConversationParticipant;

class MigrateMessagesToConversations extends Command
{
    protected $signature = 'migrate:messages-to-conversations';
    protected $description = 'Migrate existing messages and recipients into conversations';

    public function handle()
    {
        Message::with('recipients')->chunk(100, function ($messages) {
            foreach ($messages as $message) {
                foreach ($message->recipients as $recipient) {
                    $conversation = Conversation::create([
                        'title' => null, // Set title if needed
                    ]);

                    $this->info("Created conversation ID: {$conversation->id}");

                    $message->update(['conversation_id' => $conversation->id]);

                    $this->info("Updated message ID: {$message->id} with conversation ID: {$conversation->id}");

                    ConversationParticipant::create([
                        'conversation_id' => $conversation->id,
                        'username' => $message->sender_username,
                    ]);

                    $this->info("Added sender to conversation ID: {$conversation->id}");

                    ConversationParticipant::create([
                        'conversation_id' => $conversation->id,
                        'username' => $recipient->recipient_username,
                    ]);

                    $this->info("Added recipient to conversation ID: {$conversation->id}");
                }
            }
        });

        $this->info('Messages migrated to conversations successfully.');
    }
}