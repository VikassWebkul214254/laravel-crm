<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webkul\AI\Services\AIService;

class AIController extends Controller
{
    /**
     * Improve a paragraph via AJAX.
     */
    public function improve(Request $request): JsonResponse
    {
        // Validate request
        $request->validate([
            'paragraph' => 'required|string|min:10|max:10000',
        ]);

        $paragraph = $request->input('paragraph');

        // Call the service to improve the paragraph
        $result = AIService::improveParagraph($paragraph);

        return response()->json($result);
    }

    /**
     * Get AI answer based on prompt and optional context.
     */
    public function answer(Request $request): JsonResponse
    {
        // Validate request
        $request->validate([
            'prompt'  => 'required|string|min:3|max:500',
            'context' => 'nullable|string|max:10000',
        ]);

        $prompt  = $request->input('prompt');
        $context = $request->input('context');

        // Call the service to get answer
        $result = AIService::getAnswer($prompt, $context);

        return response()->json($result);
    }
}
