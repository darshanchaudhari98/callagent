<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/calling-agent', 'CallingAgent::index');

// Calling Agent Routes
$routes->group('calling-agent', function ($routes) {
    $routes->post('initiate', 'CallingAgent::initiateCall');
    $routes->match(['get', 'post'], 'webhook', 'CallingAgent::callWebhook');
    $routes->match(['get', 'post'], 'call-webhook', 'CallingAgent::callWebhook');
    $routes->post('voice-input', 'CallingAgent::handleVoiceInput');
    $routes->get('status/(:segment)', 'CallingAgent::getCallStatus/$1');
    $routes->get('test-pitch', 'CallingAgent::testPitch');
    
    // Voice handler routes (for Exotel webhooks) - support both GET and POST
    $routes->match(['get', 'post'], 'voice-response', 'VoiceHandler::voiceResponse');
    $routes->match(['get', 'post'], 'process-speech', 'VoiceHandler::processSpeech');
    $routes->match(['get', 'post'], 'final-response', 'VoiceHandler::finalResponse');
});
