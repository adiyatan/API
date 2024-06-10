<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class TelegramController extends Controller
{
    protected $telegramApiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->apiToken = env('7495550754:AAGZlmFRYn8rpvk4yGNQhrlEJFBq8p0aIOk');
        $this->telegramApiUrl = "https://api.telegram.org/bot{$this->apiToken}/";
    }

    public function setWebhook()
    {
        $client = new Client();
        $response = $client->post($this->telegramApiUrl . 'setWebhook', [
            'json' => ['url' => route('api.setWebhook')]
        ]);

        return response()->json(json_decode($response->getBody(), true));
    }

    public function handleWebhook(Request $request)
    {
        $update = $request->all();

        if (isset($update['poll'])) {
            $pollId = $update['poll']['id'];
            $options = $update['poll']['options'];
            $totalVoterCount = $update['poll']['total_voter_count'];

            // Log poll data to the database
            DB::table('poll_data')->insert([
                'poll_id' => $pollId,
                'options' => json_encode($options),
                'total_voter_count' => $totalVoterCount,
                'date' => now(),
            ]);
        }

        if (isset($update['poll_answer'])) {
            $pollId = $update['poll_answer']['poll_id'];
            $userId = $update['poll_answer']['user']['id'];
            $username = $update['poll_answer']['user']['username'] ?? null;

            // Log voter data to the database
            DB::table('poll_data')->insert([
                'poll_id' => $pollId,
                'user_id' => $userId,
                'username' => $username,
                'date' => now(),
            ]);
        }

        if (isset($update['message'])) {
            $message = $update['message'];
            if (isset($message['text']) && $message['text'] === '/save-member') {
                $chatId = $message['chat']['id'];

                // Fetch all group members
                $client = new Client();
                $response = $client->get($this->telegramApiUrl . 'getChatMembersCount', [
                    'query' => ['chat_id' => $chatId]
                ]);

                $membersCount = json_decode($response->getBody(), true)['result'];

                for ($i = 0; $i < $membersCount; $i++) {
                    $response = $client->get($this->telegramApiUrl . 'getChatMember', [
                        'query' => ['chat_id' => $chatId, 'user_id' => $i]
                    ]);

                    $member = json_decode($response->getBody(), true)['result'];
                    $userId = $member['user']['id'];
                    $username = $member['user']['username'] ?? null;

                    // Save member data to the database
                    DB::table('chat_members')->insert([
                        'chat_id' => $chatId,
                        'user_id' => $userId,
                        'username' => $username,
                        'date' => now(),
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
