<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\Workspace;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Temperature(0.85)]
class BulkCaptionVariations implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        public Workspace $workspace,
        public int $count,
    ) {}

    public function instructions(): string
    {
        $brandName = $this->workspace->name ?? '';
        $brandDescription = $this->workspace->brand_description ?? '';
        $brandTone = $this->workspace->brand_tone ?? '';
        $voiceNotes = $this->workspace->brand_voice_notes ?? '';
        $language = $this->workspace->content_language ?? 'en';

        return <<<PROMPT
You are a senior social media copywriter for the brand below.

Brand name: {$brandName}
Brand description: {$brandDescription}
Brand tone: {$brandTone}
Voice notes: {$voiceNotes}
Output language: {$language}

The user will provide a SHORT brief describing a campaign or theme. Return exactly {$this->count} short caption VARIATIONS of the same base message, suitable to be the caption of an individual social post.

Rules:
- Each variation MUST be a fresh rewording. Do not repeat the same caption twice.
- Keep variations roughly the same length (2-4 sentences each).
- Keep the language natural and human. No AI-tells (no "in today's fast-paced world", no over-the-top emojis, no hashtag walls).
- Stay close to the brand tone above.
- Use the same output language as the brand: {$language}.
- Do NOT number, label, or prefix the variations. Just return the caption text per item.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'variations' => $schema->array()
                ->items($schema->string()->description('A single caption variation, plain text.'))
                ->min($this->count)
                ->max($this->count)
                ->description("Exactly {$this->count} caption variations.")
                ->required(),
        ];
    }

    public function provider(): Lab
    {
        return match (config('ai.default')) {
            'openai' => Lab::OpenAI,
            'anthropic' => Lab::Anthropic,
            default => Lab::Gemini,
        };
    }

    public function model(): string
    {
        return config('ai.default_text_model');
    }
}
