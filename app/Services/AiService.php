<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Génère une liste de repas
     * * @param int $nbRepas Le nombre de plats souhaités (ex: 7)
     * @param string $preferences (ex: "Pas de viande, j'aime le piquant")
     */
    public function generateWeeklyMenu(int $nbRepas, string $preferences = '')
    {
        if ($nbRepas > 7) {
            $nbMidi = floor($nbRepas / 2);
            $nbSoir = ceil($nbRepas / 2);
            $midis = $this->callOpenRouter($nbMidi, $preferences, 'midi');
            $soirs = $this->callOpenRouter($nbSoir, $preferences, 'soirs');
            return array_merge($midis, $soirs);
        }
        return $this->callOpenRouter($nbRepas, $preferences, 'mixte');
    }

    private function callOpenRouter(int $count, string $preferences, string $typeRepas) {
        $contextType = match ($typeRepas) {
            'midi' => "Génère des repas pour le MIDI : privilégie des plats rapides (max 20min), légers ou type lunchbox/salades composées/tartines garnies.",
            'soir' => "Génère des repas pour le SOIR : privilégie des plats familiaux, réconfortants et complets.",
            default => "Génère des repas variés et équilibrés.",
        };

        $contextBelgium = "Tu cuisines pour une famille en Belgique.
        Utilise uniquement des ingrédients courants trouvables chez Colruyt, Delhaize ou Carrefour.
        Utilise le système métrique (grammes, ml).
        Evite les produits typiquement américains introuvables ici.";

        $systemPrompt = <<<EOT
        Tu es un assistant culinaire expert pour les familles.
        {$contextBelgium}
        Ta tâche est de générer une liste de recettes.
        RÈGLES STRICTES :
        1. Tu dois répondre UNIQUEMENT avec un tableau JSON valide (Tableau d'objets).
        2. Aucun texte avant ni après le JSON.
        3. Le format de chaque plat doit être :
        [
            {
                "titre": "Nom du plat",
                "description": "Courte description appétissante",
                "duree_prep": "ex: 30 min",
                "duree_cuisson": "ex: 20 min",
                "ingredients": ["ingrédient 1", "ingrédient 2"],
                "type": "{$typeRepas}"
            }
        ]
    EOT;

        $userPrompt = "Génère-moi une liste de {$count} repas variés pour le context : {$contextType}.";

        if (!empty($preferences)) {
            $userPrompt .= " IMPORTANT - Respecte ces préférences : {$preferences}.";
        }

        try {
            $result = OpenAI::chat()->create([
                'model' => env('OPENROUTER_MODEL'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'extra_headers' => [
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name'),
                ],
                'max_tokens' => 2500,
                'temperature' => 0.7,
            ]);

            $content = $result->choices[0]->message->content;

            $cleanedContent = str_replace(['```json', '```'], '', $content);

            $menu = json_decode($cleanedContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erreur décodage JSON IA : ' . $content);
                return [];
            }

            return $menu;

        } catch (\Exception $e) {
            Log::error("Erreur OpenRouter: " . $e->getMessage());
            return [];
        }
    }
}
