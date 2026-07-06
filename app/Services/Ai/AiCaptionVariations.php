<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Ai\Agents\BulkCaptionVariations;
use App\Models\Workspace;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Generates N caption variations from a single user prompt, used by bulk
 * scheduling. Falls back to repeating the raw prompt if the AI call fails so
 * a bulk run never blocks because of a model error.
 */
final class AiCaptionVariations
{
    /**
     * @return array<int, string> Exactly `$count` non-empty strings.
     */
    public static function generate(
        Workspace $workspace,
        string $prompt,
        int $count,
        ?string $userId = null,
    ): array {
        if ($count <= 0) {
            return [];
        }

        $fallback = array_fill(0, $count, $prompt);

        try {
            $agent = new BulkCaptionVariations(workspace: $workspace, count: $count);
            $response = $agent->prompt($prompt);

            RecordAiUsage::recordText(
                workspace: $workspace,
                promptTokens: $response->usage->promptTokens,
                completionTokens: $response->usage->completionTokens,
                provider: (string) config('ai.default'),
                model: (string) config('ai.default_text_model'),
                userId: $userId,
                metadata: ['agent' => 'bulk_caption_variations', 'count' => $count],
            );

            $variations = data_get($response->structured ?? [], 'variations', []);

            $cleaned = array_values(array_filter(
                array_map(fn ($v) => is_string($v) ? trim($v) : '', $variations),
                fn ($v) => $v !== '',
            ));

            if (count($cleaned) < $count) {
                // Pad with the original prompt so the caller can always pair 1:1 with media.
                $cleaned = array_pad($cleaned, $count, $prompt);
            } elseif (count($cleaned) > $count) {
                $cleaned = array_slice($cleaned, 0, $count);
            }

            return $cleaned;
        } catch (Throwable $e) {
            Log::warning('AiCaptionVariations failed, falling back to prompt', [
                'workspace_id' => $workspace->id,
                'count' => $count,
                'error' => $e->getMessage(),
            ]);

            return $fallback;
        }
    }
}
