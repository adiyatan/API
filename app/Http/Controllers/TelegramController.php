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
        Log::info('Webhook received:', $update); // Add logging

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

        DB::table('poll_answers')->insert([
            'poll_id' => $pollId,
            'user_id' => $userId,
            'username' => $username,
            'date' => now(),
        ]);
    }

    protected function handleMessage($message)
    {
        if (isset($message['text']) && $message['text'] === '/set-member') {
            $chatId = $message['chat']['id'];
            $this->saveChatMembers($chatId);
        }
    }

    protected function saveChatMembers($chatId)
    {
        $client = new Client();
        $response = $client->get($this->telegramApiUrl . 'getChatMembersCount', [
            'query' => ['chat_id' => $chatId]
        ]);

        $membersCount = json_decode($response->getBody(), true)['result'];
        Log::info("Members count: {$membersCount}");

        for ($i = 0; $i < $membersCount; $i++) {
            $response = $client->get($this->telegramApiUrl . 'getChatMember', [
                'query' => ['chat_id' => $chatId, 'user_id' => $i]
            ]);

            $member = json_decode($response->getBody(), true)['result'];
            Log::info('Member data:', $member);

            $userId = $member['user']['id'];
            $username = $member['user']['username'] ?? null;

            DB::table('chat_members')->insert([
                'chat_id' => $chatId,
                'user_id' => $userId,
                'username' => $username,
                'date' => now(),
            ]);
        }
    }
}
