<?php

namespace App\Libraries;

class ExotelService
{
    protected $apiKey;
    protected $apiToken;
    protected $sid;
    protected $baseUrl;
    protected $callerId;

    public function __construct()
    {
        $this->apiKey = getenv('EXOTEL_API_KEY') ?: '';
        $this->apiToken = getenv('EXOTEL_API_TOKEN') ?: '';
        $this->sid = getenv('EXOTEL_SID') ?: '';
        $this->callerId = getenv('EXOTEL_CALLER_ID') ?: '';
        $this->baseUrl = "https://api.exotel.com/v1/Accounts/{$this->sid}";
    }

    /**
     * Make an outbound call with greeting message
     */
    public function makeCall(string $toNumber): array
    {
        // Use Exotel's passthru applet for simple TTS
        $url = "{$this->baseUrl}/Calls/connect.json";
        
        $appUrl = getenv('APP_URL') ?: '';
        $exotelAppId = getenv('EXOTEL_APP_ID') ?: '';
        
        $postData = [
            'From' => $toNumber,
            'CallerId' => $this->callerId,
        ];

        // Priority: 1) Exotel App ID, 2) Public webhook URL
        if (!empty($exotelAppId)) {
            // Use Exotel App created in dashboard
            $postData['Url'] = "http://my.exotel.com/{$this->sid}/exoml/start_voice/{$exotelAppId}";
        } elseif (!empty($appUrl) && strpos($appUrl, 'localhost') === false) {
            // Use public webhook URL with User-Agent header bypass
            $postData['Url'] = $appUrl . '/calling-agent/voice-response';
        } else {
            throw new \Exception(
                "Exotel requires a public URL for voice responses. " .
                "Either set APP_URL to a public URL (use ngrok for testing) " .
                "or set EXOTEL_APP_ID to use an Exotel App from your dashboard."
            );
        }

        return $this->makeRequest($url, $postData);
    }

    /**
     * Get call status
     */
    public function getCallStatus(string $callSid): array
    {
        $url = "{$this->baseUrl}/Calls/{$callSid}.json";
        return $this->makeRequest($url, [], 'GET');
    }

    /**
     * Make HTTP request to Exotel API
     */
    protected function makeRequest(string $url, array $data = [], string $method = 'POST'): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->apiKey}:{$this->apiToken}");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Exotel API Error: {$error}");
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = $decoded['RestException']['Message'] ?? 'Unknown error';
            throw new \Exception("Exotel API Error: {$errorMsg}");
        }

        return $decoded ?? [];
    }

    /**
     * Generate TwiML/ExoML response for voice
     */
    public function generateVoiceResponse(string $text, bool $gather = true): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Response>';
        
        if ($gather) {
            $xml .= '<Gather input="speech" action="' . getenv('APP_URL') . '/calling-agent/process-speech" method="POST" speechTimeout="auto">';
            $xml .= "<Say voice=\"alice\">{$text}</Say>";
            $xml .= '</Gather>';
            $xml .= '<Say voice="alice">I didn\'t hear anything. Goodbye.</Say>';
        } else {
            $xml .= "<Say voice=\"alice\">{$text}</Say>";
        }
        
        $xml .= '</Response>';
        
        return $xml;
    }
}
