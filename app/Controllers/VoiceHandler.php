<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class VoiceHandler extends Controller
{
    /**
     * Initial voice response when call connects
     */
    public function voiceResponse()
    {
        log_message('info', 'Voice response webhook called');
        
        $greeting = "Hello! This is a call from Trade Diary, India's leading trading journal platform. "
            . "We help traders like you track, analyze, and improve your trading performance. "
            . "Are you currently trading in stocks, forex, or crypto? Press 1 for yes, or press 2 for no.";
        
        $xml = $this->buildExoML($greeting, true);
        
        return $this->response
            ->setHeader('Content-Type', 'application/xml')
            ->setBody($xml);
    }

    /**
     * Process DTMF or speech input from caller
     */
    public function processSpeech()
    {
        $digits = $this->request->getPost('digits') ?? $this->request->getGet('digits') ?? '';
        $callSid = $this->request->getPost('CallSid') ?? '';
        
        log_message('info', "Input received: digits={$digits}, CallSid={$callSid}");

        $response = '';
        
        if ($digits == '1') {
            $response = "Great! Trade Diary offers automatic trade journaling, performance analytics, "
                . "and risk management tools to help you become a more profitable trader. "
                . "Would you like us to send you more information? Press 1 for yes, or press 2 for no.";
            $xml = $this->buildExoML($response, true, 'final-response');
        } elseif ($digits == '2') {
            $response = "No problem! Thank you for your time. Have a great day. Goodbye!";
            $xml = $this->buildExoML($response, false);
        } else {
            $response = "I didn't catch that. Press 1 if you are interested, or press 2 to end the call.";
            $xml = $this->buildExoML($response, true);
        }
        
        return $this->response
            ->setHeader('Content-Type', 'application/xml')
            ->setBody($xml);
    }

    /**
     * Final response handler
     */
    public function finalResponse()
    {
        $digits = $this->request->getPost('digits') ?? $this->request->getGet('digits') ?? '';
        
        log_message('info', "Final response: digits={$digits}");

        if ($digits == '1') {
            $response = "Wonderful! Our team will reach out to you shortly with more details about Trade Diary. "
                . "Thank you for your interest. Have a great trading day. Goodbye!";
        } else {
            $response = "No problem! If you change your mind, visit trade diary dot com. Thank you for your time. Goodbye!";
        }
        
        $xml = $this->buildExoML($response, false);
        
        return $this->response
            ->setHeader('Content-Type', 'application/xml')
            ->setBody($xml);
    }

    /**
     * Build ExoML response
     */
    private function buildExoML(string $text, bool $gather = true, string $nextAction = 'process-speech'): string
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost:8080';
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Response>';
        
        if ($gather) {
            $xml .= '<Gather action="' . $appUrl . '/calling-agent/' . $nextAction . '" numDigits="1" timeout="10">';
            $xml .= '<Say voice="woman" language="en-IN">' . htmlspecialchars($text) . '</Say>';
            $xml .= '</Gather>';
            // Fallback if no input
            $xml .= '<Say voice="woman" language="en-IN">We did not receive any input. Goodbye.</Say>';
            $xml .= '<Hangup/>';
        } else {
            $xml .= '<Say voice="woman" language="en-IN">' . htmlspecialchars($text) . '</Say>';
            $xml .= '<Hangup/>';
        }
        
        $xml .= '</Response>';
        
        return $xml;
    }
}
