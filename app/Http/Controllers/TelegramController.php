<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Log::info('Webhook received:', $update);

        if (isset($update['poll'])) {
            $this->logPollData($update['poll']);
        }

        if (isset($update['poll_answer'])) {
            $this->logPollAnswer($update['poll_answer']);
        }

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        return response()->json(['status' => 'success']);
    }

    protected function logPollData($poll)
    {
        $pollId = $poll['id'];
        $options = $poll['options'];
        $totalVoterCount = $poll['total_voter_count'];

        DB::table('poll_data')->insert([
            'poll_id' => $pollId,
            'options' => json_encode($options),
            'total_voter_count' => $totalVoterCount,
            'date' => now(),
        ]);
    }

    protected function logPollAnswer($pollAnswer)
    {
        $pollId = $pollAnswer['poll_id'];
        $userId = $pollAnswer['user']['id'];
        $username = $pollAnswer['user']['username'] ?? null;
        $optionIds = $pollAnswer['option_ids'] ?? [0];

        DB::table('poll_answers')->insert([
            'poll_id' => $pollId,
            'user_id' => $userId,
            'username' => $username,
            'option_ids' => json_encode($optionIds),
            'date' => now(),
        ]);
    }

    protected function logMember($pollAnswer)
    {
        $pollId = $pollAnswer['poll_id'];
        $userId = $pollAnswer['user']['id'];
        $username = $pollAnswer['user']['username'] ?? null;

        // DB::table('members_bandung')->insert([
        //     'poll_id' => $pollId,
        //     'user_id' => $userId,
        //     'username' => $username,
        //     'date' => now(),
        // ]);

        // DB::table('members_jogja')->insert([
        //     'poll_id' => $pollId,
        //     'user_id' => $userId,
        //     'username' => $username,
        //     'date' => now(),
        // ]);
    }

    protected function handleMessage($message)
    {
        $client = new Client();
        if (isset($message['text'])) {
            switch ($message['text']) {
                case '/set-member-bandung':
                    $chatId = $message['chat']['id'];
                    $client->post('https://api.telegram.org/bot7495550754:AAGZlmFRYn8rpvk4yGNQhrlEJFBq8p0aIOk/' . 'sendMessage', [
                        'json' => [
                            'chat_id' => $chatId,
                            'text' => 'anda bukan admin, undang @adiyatan terlebih dahulu'
                        ]
                    ]);
                    // $this->sendPoll($chatId, 'Mohon isi vote "daftar" agar terdeteksi absensi', 'bandung');
                    break;

                case '/set-member-jogja':
                    $chatId = $message['chat']['id'];
                    $client->post('https://api.telegram.org/bot7495550754:AAGZlmFRYn8rpvk4yGNQhrlEJFBq8p0aIOk/' . 'sendMessage', [
                        'json' => [
                            'chat_id' => $chatId,
                            'text' => 'anda bukan admin, undang @adiyatan terlebih dahulu'
                        ]
                    ]);
                    // $this->sendPoll($chatId, 'Mohon isi vote "daftar" agar terdeteksi absensi', 'jogja');
                    break;
            }
        }
    }

    protected function sendPoll($chatId, $question, $location)
    {
        $client = new Client();
        $response = $client->post($this->telegramApiUrl . 'sendPoll', [
            'json' => [
                'chat_id' => $chatId,
                'question' => $question,
                'options' => ['daftar', 'jangan tekan disini'],
                'is_anonymous' => false
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);
        $pollId = $responseBody['result']['poll']['id'];
        Log::info('Poll sent:', ['chat_id' => $chatId, 'poll_id' => $pollId, 'location' => $location]);

        $messageResponse = $client->post($this->telegramApiUrl . 'sendMessage', [
            'json' => [
                'chat_id' => $chatId,
                'text' => 'vote akan di tutup 1 jam setelah pengiriman'
            ]
        ]);

        Log::info('Follow-up message sent:', ['chat_id' => $chatId, 'response' => json_decode($messageResponse->getBody(), true)]);
    }
}
