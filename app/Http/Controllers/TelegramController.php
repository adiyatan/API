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
    protected $openAIApiToken;

    public function __construct()
    {
        $this->telegramApiUrl = "https://api.telegram.org/bot7495550754:AAFgq5KcvBWLj1zFUODaeam5dy0haoWxhsE/";
        $this->openAIApiToken = "sk-proj-bMrmGws85N4mo39NbhY2T3BlbkFJ7h58tMDT9zu5SN8zG9Hu";
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

        $chatId = $update['message']['chat']['id'] ?? null;
        $cekAdd = null;

        $pollId = $update['poll_answer']['poll_id'] ?? null;
        if ($pollId) {
            $chatId = $this->getChatIdByPollId($pollId);
        }

        if ($chatId) {
            $cekAdd = DB::table('check_add')->where('chat_id', $chatId)->exists();
            Log::info('cek Add bernilai: ' . ($cekAdd ? 'true' : 'false'));
            $cekAdd = $cekAdd ? 'true' : 'false';
        }

        if (isset($update['poll'])) {
            $this->logPollData($update['poll']);
        }

        if (isset($update['poll_answer']) && $cekAdd == 'false') {
            $this->logMember($update['poll_answer']);
        }

        if (isset($update['poll_answer']) && $cekAdd == 'true') {
            $this->logPollAnswer($update['poll_answer']);
        }

        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        return response()->json(['status' => 'success']);
    }

    protected function logPollData($poll)
    {
        DB::table('poll_data')->insert([
            'poll_id' => $poll['id'],
            'options' => json_encode($poll['options']),
            'total_voter_count' => $poll['total_voter_count'],
            'date' => now(),
            'chat_id' => $this->getChatIdByPollId($poll['id']),
        ]);
    }

    protected function logPollAnswer($pollAnswer)
    {
        DB::table('poll_answers')->insert([
            'poll_id' => $pollAnswer['poll_id'],
            'user_id' => $pollAnswer['user']['id'],
            'username' => $pollAnswer['user']['username'] ?? null,
            'option_ids' => json_encode($pollAnswer['option_ids'] ?? [0]),
            'date' => now(),
            'chat_id' => $this->getChatIdByPollId($pollAnswer['poll_id']),
        ]);
    }

    protected function logMember($pollAnswer)
    {
        $data = [
            'user_id' => $pollAnswer['user']['id'],
            'username' => $pollAnswer['user']['username'] ?? null,
        ];

        $chatId = $this->getChatIdByPollId($pollAnswer['poll_id']);
        if ($chatId == -1001309342664) {
            DB::table('members_bandung')->insert($data);
        } else {
            DB::table('members_jogja')->insert($data);
        }
    }

    protected function handleMessage($message)
    {
        $chatId = $message['chat']['id'];
        $currentHour = date('H');
        $client = new Client();

        if (isset($message['text'])) {
            switch ($message['text']) {
                case '/set-member-bandung':
                case '/set-member-jogja':
                    $location = $message['text'] === '/set-member-bandung' ? 'bandung' : 'jogja';
                    $this->handleSetMember($chatId, $currentHour, $location, $client);
                    break;
                case '/cek-member-bandung':
                case '/cek-member-jogja':
                    $location = $message['text'] === '/cek-member-bandung' ? 'bandung' : 'jogja';
                    $this->handleCekMember($chatId, $location, $client);
                    break;
                default:
                    // Handle other messages with GPT-3.5
                    $responseText = $this->getGPT3Response($message['text']);
                    $this->sendMessage($client, $chatId, $responseText);
                    break;
            }
        }

        // Check if the 'text' key exists in the message
        if (isset($message['text']) && stripos($message['text'], 'jacob') !== false) {
            $responseText = $this->getGPT3Response($message['text']);
            $this->sendMessage($client, $chatId, $responseText);
            return;
        }
    }

    protected function handleCekMember($chatId, $location, $client)
    {
        $tableName = $location === 'bandung' ? 'members_bandung' : 'members_jogja';
        $members = DB::table($tableName)->get(['user_id', 'username'])->toArray();

        $messageText = "Daftar member $location:\n";
        foreach ($members as $member) {
            $messageText .= "User ID: {$member->user_id}, Username: {$member->username}\n";
        }

        if (empty($members)) {
            $messageText = "Tidak ada member yang terdaftar di $location.";
        }

        $this->sendMessage($client, $chatId, $messageText);
    }

    protected function handleSetMember($chatId, $currentHour, $location, $client)
    {
        if ($currentHour < 5) {
            $this->sendMessage($client, $chatId, 'Set member hanya bisa dilakukan setelah jam 12 siang');
        } else {
            $this->sendPoll($chatId, 'Mohon isi vote "daftar" agar terdeteksi absensi', $location, $client);
            DB::table('check_add')->updateOrInsert(
                ['chat_id' => $chatId],
                [
                    'isAddMember' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }

    protected function sendPoll($chatId, $question, $location, $client)
    {
        $response = $client->post($this->telegramApiUrl . 'sendPoll', [
            'json' => [
                'chat_id' => $chatId,
                'question' => $question,
                'options' => ['daftar', 'jangan tekan disini'],
                'is_anonymous' => false
            ]
        ]);

        DB::table('poll_data')->insert([
            'poll_id' => json_decode($response->getBody(), true)['result']['poll']['id'],
            'options' => json_encode(['daftar', 'jangan tekan disini']),
            'date' => now(),
            'total_voter_count' => 0,
            'chat_id' => $chatId,
        ]);

        $this->sendMessage($client, $chatId, 'Dimohon agar mengisi vote secara benar, Jangan isi vote "jangan tekan disini"!');
    }

    protected function sendMessage($client, $chatId, $text)
    {
        $client->post($this->telegramApiUrl . 'sendMessage', [
            'json' => [
                'chat_id' => $chatId,
                'text' => $text
            ]
        ]);
    }

    protected function getChatIdByPollId($pollId)
    {
        return DB::table('poll_data')->where('poll_id', $pollId)->whereNotNull('chat_id')->value('chat_id');
    }

    protected function getGPT3Response($messageText)
    {
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openAIApiToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $messageText],
                ],
                'max_tokens' => 150,
            ],
        ]);

        $responseBody = json_decode($response->getBody(), true);
        return $responseBody['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';
    }
}
