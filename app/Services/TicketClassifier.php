<?php

namespace App\Services;

use App\Models\Ticket;
use App\Enums\Category;
use App\Enums\TicketStatus;
use OpenAI\Laravel\Facades\OpenAI;

class TicketClassifier
{
    public function classify(Ticket $ticket): mixed
    {
        $enabled = env('OPENAI_CLASSIFY_ENABLED', true);

        if (!$enabled) {
            return [
                'status' => fake()->randomElement(TicketStatus::values()),
                'category' => fake()->word(),
                'explanation' => fake()->sentence(),
                'confidence' => fake()->randomFloat(2, 0, 1),
            ];
        }

        $categories = implode(', ', Category::values());

        $systemPrompt = <<<EOT
            You are an AI assistant that helps customer support agents classify support tickets.
            You will be provided with the subject and body of a support ticket.
            Your task is to classify the ticket into one of the following categories based on what you think fits the criteria: $categories if you can't find an appropriate category to use, fallback to 'other'.
            You should also provide a brief explanation of why you chose that category and a confidence score between 0 and 1 indicating how confident you are in your classification.
            Your response should be in JSON format with the following structure:
            {
                "status": "chosen_status",
                "explanation": "brief_explanation",
                "confidence": confidence_score
            }
            Ensure that the JSON is properly formatted.
        EOT;

        $userPrompt = <<<EOT
            Subject: {$ticket->subject}
            Body: {$ticket->body}
        EOT;

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-nano',
            'temperature' => 0.7,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);

        $content = $response['choices'][0]['message']['content'];

        $content = trim($content);

        // strip common markdown fences if present
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*(.*)\s*```$/s', '$1', $content);
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        return $data;
    }
}
