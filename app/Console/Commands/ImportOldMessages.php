<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportOldMessages extends Command
{
    protected $signature = 'import:old-messages';
    protected $description = 'Import messages from the old ELP tables';

    public function handle()
    {
        // Clear existing data
        // DB::table('homepages.message_recipients')->truncate();
        // DB::table('homepages.messages')->truncate();

        $twoYearsAgo = Carbon::now()->subYears(2);

        $totalMessages = DB::table('elp.elp_comms_msg')
            ->where('date', '>=', $twoYearsAgo)
            ->count();

        $this->output->progressStart($totalMessages);

        DB::table('elp.elp_comms_msg')
            ->where('date', '>=', $twoYearsAgo)
            ->orderBy('date', 'desc')
            ->chunk(100, function ($oldMessages) {
                foreach ($oldMessages as $oldMessage) {
                    $senderUsername = $oldMessage->sender;
                    if (is_numeric($oldMessage->sender)) {
                        $user = DB::table('elp.ext_userid')
                            ->where('idnumber', $oldMessage->sender)
                            ->first();
                        $senderUsername = $user ? $user->username : $oldMessage->sender;
                    }

                    $messageId = DB::table('homepages.messages')->insertGetId([
                        'sender_username' => $senderUsername,
                        'content' => $oldMessage->message,
                        'sent_at' => Carbon::parse($oldMessage->date),
                        'created_at' => Carbon::parse($oldMessage->date),
                        'updated_at' => Carbon::now(),
                    ]);

                    $recipients = DB::table('elp.elp_comms_sent')
                        ->where('msgid', $oldMessage->id)
                        ->get();

                    foreach ($recipients as $recipient) {
                        $recipientUsername = $recipient->recipient;
                        if (is_numeric($recipient->recipient)) {
                            $user = DB::table('elp.ext_userid')
                                ->where('idnumber', $recipient->recipient)
                                ->first();
                            $recipientUsername = $user ? $user->username : $recipient->recipient;
                        }

                        DB::table('homepages.message_recipients')->insert([
                            'message_id' => $messageId,
                            'recipient_username' => $recipientUsername,
                            'is_read' => $recipient->read == 1,
                            'read_at' => $recipient->read == 1 ? Carbon::parse($oldMessage->date) : null,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }

                    $this->output->progressAdvance();
                }
            });

        $this->output->progressFinish();
        $this->info('Old messages imported successfully.');
    }
}
