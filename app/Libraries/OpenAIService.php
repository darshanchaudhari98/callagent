<?php

namespace App\Libraries;

class OpenAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';
    protected $model = 'gpt-4';

    protected $systemPrompt = <<<PROMPT
You are a friendly and professional sales representative for Trade Diary, a trading journal and analytics platform that helps traders track, analyze, and improve their trading performance.

Your goal is to:
1. Introduce yourself and Trade Diary briefly
2. Understand if the person is an active trader (stocks, forex, crypto, etc.)
3. Explain the key benefits of Trade Diary:
   - Automatic trade journaling
   - Performance analytics and insights
   - Risk management tools
   - Pattern recognition to improve trading
4. Gauge their interest and collect their email for a demo or trial
5. Be respectful of their time and handle objections gracefully

Keep responses concise (2-3 sentences max) since this is a phone call.
Be conversational and natural, not robotic.
If they're not interested, thank them politely and end the call.
If they show interest, try to schedule a demo or get their email.

Start by greeting them and introducing yourself from Trade Diary.
PROMPT;

    public function __construct()
    {
        $this->apiKey = getenv('OPENAI_API_KEY') ?: '';
    }

    /**
     * Generate AI response for conversation
     */
    public function generateResponse(string $userInput, array $conversationHistory = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt]
        ];

        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $messages[] = $msg;
        }

        // Add current user input
        $messages[] = ['role' => 'user', 'content' => $userInput];

        $response = $this->chatCompletion($messages);

        return $response['choices'][0]['message']['content'] ?? 'I apologize, could you please repeat that?';
    }

    /**
     * Get initial greeting for the call
     */
    public function getInitialGreeting(): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
            ['role' => 'user', 'content' => 'Start the call with your greeting.']
        ];

        $response = $this->chatCompletion($messages);

        return $response['choices'][0]['message']['content'] 
            ?? 'Hello! This is calling from Trade Diary. Do you have a moment to talk about improving your trading performance?';
    }

    /**
     * Make chat completion request to OpenAI
     */
    protected function chatCompletion(array $messages): array
    {
        $url = "{$this->baseUrl}/chat/completions";

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 150,
            'temperature' => 0.7,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("OpenAI API Error: {$error}");
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = $decoded['error']['message'] ?? 'Unknown error';
            throw new \Exception("OpenAI API Error: {$errorMsg}");
        }

        return $decoded ?? [];
    }
}
