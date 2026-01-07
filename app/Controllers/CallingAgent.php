<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Libraries\ExotelService;
use App\Libraries\OpenAIService;

class CallingAgent extends Controller
{
    /**
     * Show the calling agent UI
     */
    public function index()
    {
        return view('calling_agent');
    }

    /**
     * Test OpenAI pitch (for debugging)
     */
    public function testPitch()
    {
        try {
            $openai = new OpenAIService();
            $greeting = $openai->getInitialGreeting();
            
            return $this->response->setJSON([
                'success' => true,
                'pitch' => $greeting
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Initiate a call to a lead
     */
    public function initiateCall()
    {
        try {
            $json = $this->request->getJSON();
            $phoneNumber = $json->phone_number ?? null;
            $leadName = $json->lead_name ?? 'User';

            if (!$phoneNumber) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phone number is required'
                ])->setStatusCode(400);
            }

            $exotel = new ExotelService();
            $callResponse = $exotel->makeCall($phoneNumber);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Call initiated successfully',
                'call_sid' => $callResponse['Call']['Sid'] ?? null,
                'data' => $callResponse
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Webhook endpoint for Exotel call events
     */
    public function callWebhook()
    {
        $callSid = $this->request->getPost('CallSid');
        $callStatus = $this->request->getPost('Status');
        $from = $this->request->getPost('From');
        
        log_message('info', "Call webhook received: SID={$callSid}, Status={$callStatus}");

        return $this->response->setJSON(['status' => 'received']);
    }

    /**
     * Handle voice input and generate AI response
     */
    public function handleVoiceInput()
    {
        try {
            $json = $this->request->getJSON();
            $userInput = $json->speech_text ?? '';
            $callSid = $json->call_sid ?? '';
            $conversationHistory = $json->conversation_history ?? [];

            if (empty($userInput)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No speech input received'
                ])->setStatusCode(400);
            }

            $openai = new OpenAIService();
            $aiResponse = $openai->generateResponse($userInput, $conversationHistory);
            
            return $this->response->setJSON([
                'success' => true,
                'response' => $aiResponse,
                'call_sid' => $callSid
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get call status
     */
    public function getCallStatus($callSid)
    {
        try {
            $exotel = new ExotelService();
            $status = $exotel->getCallStatus($callSid);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
