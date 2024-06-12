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

        $chatId = $update['message']['chat']['id'] ?? null;

        if ($chatId) {
            $cekAdd = DB::table('check_add')->where('chat_id', $chatId)->first();
        }

        if (isset($update['poll'])) {
            $this->logPollData($update['poll']);
        }

        if (isset($update['poll_answer'])) {
            $cekAdd ? $this->logMember($update['poll_answer']) : $this->logPollAnswer($update['poll_answer']);
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

        DB::table('members_bandung')->insert($data);
        DB::table('members_jogja')->insert($data);
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
        if ($currentHour < 12) {
            $this->sendMessage($client, $chatId, 'Set member hanya bisa dilakukan setelah jam 12 siang');
        } else {
            $this->sendPoll($chatId, 'Mohon isi vote "daftar" agar terdeteksi absensi', $location, $client);
            DB::table('check_add')->updateOrInsert(
                ['chat_id' => $chatId],
                ['isAddMember' => true]
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

        $pollId = json_decode($response->getBody(), true)['result']['poll']['id'];
        Log::info('Poll sent:', ['chat_id' => $chatId, 'poll_id' => $pollId, 'location' => $location]);

        $this->sendMessage($client, $chatId, 'vote akan di tutup 1 jam setelah pengiriman');
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
