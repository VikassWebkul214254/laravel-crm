<?php

namespace Webkul\AI\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class AIService
{
    /**
     * API endpoint for OpenRouter AI service.
     */
    const OPEN_ROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * Maximum character limit for input paragraph.
     */
    const MAX_CHARS = 10000;

    /**
     * Improve a given paragraph using OpenRouteAI.
     *
     * @param  string  $paragraph  The paragraph to improve
     * @return array Response from AI or error
     */
    public static function improveParagraph($paragraph)
    {
        try {
            // Validate input
            if (empty($paragraph)) {
                throw new Exception('Paragraph cannot be empty.');
            }

            if (strlen($paragraph) > self::MAX_CHARS) {
                throw new Exception('Paragraph is too long. Maximum '.self::MAX_CHARS.' characters allowed.');
            }

            // Get API configuration
            $model = core()->getConfigData('general.magic_ai.settings.other_model') ?: core()->getConfigData('general.magic_ai.settings.model');
            $apiKey = core()->getConfigData('general.magic_ai.settings.api_key');

            if (! $apiKey || ! $model) {
                throw new Exception('API key or model not configured. Please check your Magic AI settings.');
            }

            // Send to AI for improvement
            return self::callOpenRouteAI($paragraph, $model, $apiKey);

        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Call OpenRouteAI API to improve the paragraph.
     *
     * @param  string  $paragraph
     * @param  string  $model
     * @param  string  $apiKey
     * @return array
     */
    private static function callOpenRouteAI($paragraph, $model, $apiKey)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
            ])->post(self::OPEN_ROUTER_URL, [
                'model'    => $model,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => self::getSystemPrompt(),
                    ],
                    [
                        'role'    => 'user',
                        'content' => $paragraph,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 2000,
            ]);

            if ($response->failed()) {
                throw new Exception('API request failed: '.$response->body());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                throw new Exception('API error: '.$data['error']['message']);
            }

            // Extract improved text from response
            $improvedText = $data['choices'][0]['message']['content'] ?? null;

            if (! $improvedText) {
                throw new Exception('No improved text received from AI.');
            }

            return [
                'success'     => true,
                'original'    => $paragraph,
                'improved'    => trim($improvedText),
                'model_used'  => $model,
                'tokens_used' => $data['usage']['total_tokens'] ?? 0,
            ];

        } catch (Exception $e) {
            throw new Exception('Failed to improve paragraph: '.$e->getMessage());
        }
    }

    /**
     * Get system prompt for paragraph improvement.
     *
     * @return string
     */
    private static function getSystemPrompt()
    {
        return <<<'PROMPT'
                    You are an expert writing assistant. Your task is to improve the given paragraph by:

                    1. Enhancing clarity and readability
                    2. Improving grammar and sentence structure
                    3. Making the language more engaging and professional
                    4. Maintaining the original meaning and tone
                    5. Ensuring proper flow between sentences

                    Rules:
                    - Keep the improved version roughly the same length as the original
                    - Maintain the original intent and key information
                    - Use clear, concise language
                    - Return only the improved paragraph without any additional commentary
                    - Do not add new information that wasn't in the original text

                    Please improve the following paragraph:
                PROMPT;
    }

    /**
     * Get AI answer based on a prompt and optional context.
     *
     * @param  string  $prompt  The instruction/question
     * @param  string|null  $context  Optional context from textarea
     * @return array Response from AI or error
     */
    public static function getAnswer($prompt, $context = null)
    {
        try {
            // Validate input
            if (empty($prompt)) {
                throw new Exception('Prompt cannot be empty.');
            }

            // Get API configuration
            $model = core()->getConfigData('general.magic_ai.settings.other_model') ?: core()->getConfigData('general.magic_ai.settings.model');
            $apiKey = core()->getConfigData('general.magic_ai.settings.api_key');

            if (! $apiKey || ! $model) {
                throw new Exception('API key or model not configured. Please check your Magic AI settings.');
            }

            // Build user message
            $userMessage = $prompt;
            if ($context) {
                $userMessage = "Context: {$context}\n\nInstruction: {$prompt}";
            }

            // Send to AI
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
            ])->post(self::OPEN_ROUTER_URL, [
                'model'    => $model,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a helpful AI assistant. Follow the user\'s instructions carefully and provide clear, useful responses.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $userMessage,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 2000,
            ]);

            if ($response->failed()) {
                throw new Exception('API request failed: '.$response->body());
            }

            $data = $response->json();

            if (isset($data['error'])) {
                throw new Exception('API error: '.$data['error']['message']);
            }

            // Extract answer from response
            $answer = $data['choices'][0]['message']['content'] ?? null;

            if (! $answer) {
                throw new Exception('No answer received from AI.');
            }

            return [
                'success'     => true,
                'prompt'      => $prompt,
                'context'     => $context,
                'answer'      => trim($answer),
                'model_used'  => $model,
                'tokens_used' => $data['usage']['total_tokens'] ?? 0,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
