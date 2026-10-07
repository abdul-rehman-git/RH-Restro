<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\AnonymousAgent;

class AiChatController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return response()->json([]);
        }
        $sessions = ChatSession::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json($sessions);
    }

    public function show(ChatSession $chatSession)
    {
        if (!Auth::check() || $chatSession->user_id !== Auth::id()) {
            abort(403);
        }
        return response()->json($chatSession->messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|string',
            'session_id' => 'nullable|exists:chat_sessions,id'
        ]);

        $userMessageText = trim($request->message ?? '');
        if (empty($userMessageText) && $request->filled('image')) {
            $userMessageText = 'Please analyze this product image and search for similar products in your inventory.';
        }

        $session = $request->session_id 
            ? ChatSession::findOrFail($request->session_id)
            : ChatSession::create(['user_id' => Auth::id(), 'title' => substr($userMessageText, 0, 30) . '...']);

        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        $attachments = [];
        $savedMessageContent = $userMessageText;

        if ($request->filled('image')) {
            $base64Data = $request->image;
            if (preg_match('/^data:(image\/[a-zA-Z+]+);base64,(.+)$/', $base64Data, $matches)) {
                $mimeType = $matches[1];
                $rawBase64 = $matches[2];
                $attachments[] = new \Laravel\Ai\Files\Base64Image($rawBase64, $mimeType);
                $savedMessageContent = "![" . ($userMessageText ?: "Uploaded Product Image") . "](" . $base64Data . ")" . ($userMessageText ? "\n\n" . $userMessageText : "");
            }
        }

        // We will build the message history
        $history = $session->messages()->orderBy('id')->get()->map(function ($msg) {
            if ($msg->role === 'assistant') {
                return new \Laravel\Ai\Messages\AssistantMessage($msg->content);
            }
            return new \Laravel\Ai\Messages\UserMessage($msg->content);
        })->toArray();

        // Append current message to history with attachments if provided
        if (!empty($attachments)) {
            $history[] = new \Laravel\Ai\Messages\UserMessage($userMessageText, $attachments);
        }

        // Save user message in database
        $session->messages()->create([
            'role' => 'user',
            'content' => $savedMessageContent,
        ]);

        return response()->stream(function () use ($history, $session, $userMessageText) {
            // Send the session ID first so the frontend knows what session was created
            echo "data: " . json_encode(['session_id' => $session->id]) . "\n\n";
            ob_flush();
            flush();

            try {
                $agent = new AnonymousAgent(
                    instructions: 'You are RH AI Assistant, a helpful and polite customer support assistant for the modern online store RH Commerce. Your primary goal is to help customers find products, analyze uploaded product images to find matching inventory, explain shipping, and guide checkout. CRITICAL RULES: 1. Keep your answers EXTREMELY short, clear, and user-friendly (1-2 sentences maximum, unless listing products). 2. If a product image is provided, identify its key features (category, style, color, materials) and call SearchProductsTool with matching keywords to recommend similar items from store inventory. 3. If asked about technical topics (coding, Laravel, Vue), politely say "I only assist with RH Commerce shopping and orders" in one short sentence without over-explaining. 4. Always be helpful but brief. 5. When listing products found from a tool, you MUST return the EXACT raw HTML provided by the tool without altering it, wrapping it in markdown, or summarizing it.',
                    messages: $history,
                    tools: [
                        new \App\Ai\Tools\SearchProductsTool()
                    ]
                );

                // Using stream to get SSE
                $stream = $agent->stream($userMessageText);

                $fullResponse = '';

                foreach ($stream as $responseChunk) {
                    $text = '';
                    if (is_string($responseChunk)) {
                        $text = $responseChunk;
                    } elseif (isset($responseChunk->delta)) {
                        $text = $responseChunk->delta;
                    } elseif (isset($responseChunk->text)) {
                        $text = $responseChunk->text;
                    }

                    $fullResponse .= $text;
                    
                    if (!empty($text)) {
                        echo "data: " . json_encode(['text' => $text]) . "\n\n";
                        ob_flush();
                        flush();
                    }
                }

                // Save the assistant's response to the database
                $session->messages()->create([
                    'role' => 'assistant',
                    'content' => $fullResponse,
                ]);

            } catch (\Throwable $e) {
                $errMsg = $e->getMessage();
                \Illuminate\Support\Facades\Log::error('AI Assistant Stream Error: ' . $errMsg);

                if (str_contains($errMsg, '429') || str_contains($errMsg, 'rate') || str_contains($errMsg, 'quota') || str_contains($errMsg, 'Limit')) {
                    $userFriendlyMessage = "I'm receiving a lot of questions right now! Please wait a few seconds and ask me again. 😊";
                } elseif (str_contains($errMsg, 'API key') || str_contains($errMsg, '401') || str_contains($errMsg, '403') || str_contains($errMsg, 'invalid') || str_contains($errMsg, 'revoked')) {
                    $userFriendlyMessage = "The AI service key needs to be updated in configuration. Please contact store support. ⚙️";
                } elseif (str_contains($errMsg, 'Connection') || str_contains($errMsg, '404') || str_contains($errMsg, 'cURL') || str_contains($errMsg, 'timeout')) {
                    $userFriendlyMessage = "Network connection hiccup. Please check your connection and try again! 🌐";
                } else {
                    $userFriendlyMessage = "I encountered a small glitch. Please try asking your question again! ✨";
                }

                echo "data: " . json_encode(['text' => $userFriendlyMessage]) . "\n\n";
                ob_flush();
                flush();
            }

            echo "data: [DONE]\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}   
