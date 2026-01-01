# Trade Diary Calling Agent

A basic calling agent using Exotel for making calls and OpenAI for AI-powered conversations for lead acquisition.

## Setup

### 1. Configure Environment Variables

Copy `env` to `.env` and update these values:

```env
# OpenAI Configuration
OPENAI_API_KEY = sk-your-openai-api-key-here

# Exotel Configuration
EXOTEL_API_KEY = your-exotel-api-key
EXOTEL_API_TOKEN = your-exotel-api-token
EXOTEL_SID = your-exotel-account-sid
EXOTEL_CALLER_ID = your-exotel-caller-id

# App URL (must be publicly accessible for webhooks)
APP_URL = https://your-domain.com
```

### 2. Run Database Migration

```bash
php spark migrate
```

### 3. Expose Your Local Server (for testing)

Use ngrok or similar to expose your local server:
```bash
ngrok http 8080
```

Update `APP_URL` in `.env` with the ngrok URL.

## API Endpoints

### Initiate a Call
```
POST /calling-agent/initiate
Content-Type: application/json

{
    "phone_number": "+919876543210",
    "lead_name": "John Doe"
}
```

### Get Call Status
```
GET /calling-agent/status/{call_sid}
```

### Webhook Endpoints (for Exotel)
- `POST /calling-agent/voice-response` - Initial voice response
- `POST /calling-agent/process-speech` - Process speech input
- `POST /calling-agent/webhook` - Call status updates

## How It Works

1. **Initiate Call**: Your app calls the `/calling-agent/initiate` endpoint with a phone number
2. **Exotel Connects**: Exotel makes the outbound call to the lead
3. **AI Greeting**: When connected, OpenAI generates a greeting for Trade Diary
4. **Conversation Loop**: 
   - Lead speaks → Exotel captures speech
   - Speech sent to OpenAI → AI generates response
   - Response played back to lead
5. **Call Ends**: Conversation ends naturally or when lead declines

## Customizing the AI Prompt

Edit the `$systemPrompt` in `app/Libraries/OpenAIService.php` to customize:
- Company introduction
- Product benefits
- Conversation flow
- Objection handling

## Files Created

- `app/Controllers/CallingAgent.php` - Main API controller
- `app/Controllers/VoiceHandler.php` - Handles voice webhooks
- `app/Libraries/ExotelService.php` - Exotel API integration
- `app/Libraries/OpenAIService.php` - OpenAI API integration
- `app/Models/CallLogModel.php` - Call logging model
- `app/Database/Migrations/` - Database migration for call logs
