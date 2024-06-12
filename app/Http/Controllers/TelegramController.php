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
        $this->telegramApiUrl = "https://api.telegram.org/bot7495550754:AAGZlmFRYn8rpvk4yGNQhrlEJFBq8p0aIOk/";
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

        if ($chatId) {
            $cekAdd = DB::table('check_add')->where('chat_id', $chatId)->exists();
            Log::info('cek Add bernilai: ' . ($cekAdd ? 'true' : 'false'));
        }

        if (isset($update['poll'])) {
            $this->logPollData($update['poll']);
        }

        if (isset($update['poll_answer']) && !$cekAdd) {
            $this->logMember($update['poll_answer']);
        }

        if (isset($update['poll_answer']) && $cekAdd) {
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

        switch ($message['text']) {
            case '/set-member-bandung':
            case '/set-member-jogja':
                $location = $message['text'] === '/set-member-bandung' ? 'bandung' : 'jogja';
                $this->handleSetMember($chatId, $currentHour, $location, $client);
                break;
        }
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
}
