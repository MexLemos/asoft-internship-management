<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Course;
use PDO;

class YouTubePlaylistImporter
{
    /**
     * Import playlist or batch video URLs into a module.
     * Returns array with 'success' (bool), 'count' (int), and 'message' (string).
     */
    public static function import(int $moduleId, string $input, int $defaultDuration = 15): array
    {
        $input = trim($input);
        if (empty($input)) {
            return ['success' => false, 'count' => 0, 'message' => 'Nenhum link ou identificador fornecido.'];
        }

        $items = [];

        // Check if input is a single playlist URL or playlist ID
        $playlistId = self::extractPlaylistId($input);
        if ($playlistId && strpos($input, "\n") === false) {
            $items = self::fetchFromPlaylist($playlistId);
        }

        // If no playlist items found, or if input is multi-line, parse as batch URLs
        if (empty($items)) {
            $items = self::parseBatchUrls($input, $defaultDuration);
        }

        if (empty($items)) {
            return [
                'success' => false,
                'count' => 0,
                'message' => 'Nenhum vídeo válido foi identificado. Certifique-se de fornecer links válidos do YouTube ou colar uma lista de URLs.'
            ];
        }

        // Determine starting order_index in module
        $pdo = Database::getConnection();
        $stmtOrder = $pdo->prepare("SELECT COALESCE(MAX(order_index), 0) FROM lessons WHERE module_id = ?");
        $stmtOrder->execute([$moduleId]);
        $currentOrder = (int)$stmtOrder->fetchColumn();

        $createdCount = 0;
        $pdo->beginTransaction();

        try {
            foreach ($items as $item) {
                $currentOrder++;
                $title = !empty($item['title']) ? trim((string)$item['title']) : ("Aula " . $currentOrder);
                $duration = (int)($item['duration'] ?? $defaultDuration);
                if ($duration <= 0) {
                    $duration = 15;
                }
                $videoUrl = $item['url'];

                // 1. Create Lesson
                $lessonId = Course::addLesson($moduleId, $title, $currentOrder);

                // 2. Create Learning Content
                Course::addContent(
                    $lessonId,
                    $title,
                    'youtube_video',
                    $videoUrl,
                    null,
                    $duration,
                    1
                );

                $createdCount++;
            }

            $pdo->commit();
            return [
                'success' => true,
                'count' => $createdCount,
                'message' => "{$createdCount} aulas criadas com sucesso a partir dos vídeos do YouTube!"
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'success' => false,
                'count' => 0,
                'message' => 'Erro ao salvar aulas no banco de dados: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parses multiple URLs pasted line by line.
     * Supports:
     * - https://www.youtube.com/watch?v=XXXX
     * - https://youtu.be/XXXX
     * - https://www.youtube.com/watch?v=XXXX | Título Customizado
     */
    public static function parseBatchUrls(string $text, int $defaultDuration = 15): array
    {
        $lines = preg_split('/[\r\n]+/', $text);
        $items = [];
        $index = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $customTitle = null;
            if (strpos($line, '|') !== false) {
                $parts = explode('|', $line, 2);
                $line = trim($parts[0]);
                $customTitle = trim($parts[1]);
            }

            $videoId = self::extractVideoId($line);
            if (!$videoId) {
                continue;
            }

            $standardUrl = "https://www.youtube.com/watch?v={$videoId}";
            $title = $customTitle;

            if (empty($title)) {
                $title = self::fetchVideoTitle($videoId);
            }

            if (empty($title)) {
                $title = "Aula {$index}";
            }

            $items[] = [
                'id' => $videoId,
                'title' => $title,
                'url' => $standardUrl,
                'duration' => $defaultDuration
            ];
            $index++;
        }

        return $items;
    }

    /**
     * Attempts to fetch video list from a YouTube playlist ID via public mirror APIs.
     */
    public static function fetchFromPlaylist(string $playlistId): array
    {
        // Try public Invidious instances
        $instances = [
            'https://inv.tux.pizza',
            'https://invidious.nerdvpn.de',
            'https://vid.puffyan.us',
            'https://invidious.jing.rocks'
        ];

        foreach ($instances as $instance) {
            $apiUrl = "{$instance}/api/v1/playlists/{$playlistId}";
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                if (!empty($data['videos']) && is_array($data['videos'])) {
                    $results = [];
                    foreach ($data['videos'] as $v) {
                        $vidId = $v['videoId'] ?? null;
                        $vidTitle = $v['title'] ?? null;
                        $vidLength = $v['lengthSeconds'] ?? 900;
                        if ($vidId && $vidTitle) {
                            $results[] = [
                                'id' => $vidId,
                                'title' => $vidTitle,
                                'url' => "https://www.youtube.com/watch?v={$vidId}",
                                'duration' => max(1, round((int)$vidLength / 60))
                            ];
                        }
                    }
                    if (!empty($results)) {
                        return $results;
                    }
                }
            }
        }

        return [];
    }

    /**
     * Resolves official YouTube video title using YouTube oEmbed.
     */
    public static function fetchVideoTitle(string $videoId): ?string
    {
        $oembedUrl = "https://www.youtube.com/oembed?url=https://www.youtube.com/watch?v={$videoId}&format=json";
        $ch = curl_init($oembedUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $json = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($json)) {
            $data = json_decode($json, true);
            if (!empty($data['title'])) {
                return trim((string)$data['title']);
            }
        }

        return null;
    }

    /**
     * Extracts YouTube 11-char video ID from any standard URL.
     */
    public static function extractVideoId(string $url): ?string
    {
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $m)) {
            return $m[1];
        }
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', trim($url))) {
            return trim($url);
        }
        return null;
    }

    /**
     * Extracts YouTube playlist ID (usually starts with PL, UU, FL, RD, OLAK5uy_...) from a URL or raw string.
     */
    public static function extractPlaylistId(string $url): ?string
    {
        if (preg_match('/[?&]list=([a-zA-Z0-9_-]+)/i', $url, $m)) {
            return $m[1];
        }
        $trimmed = trim($url);
        if (preg_match('/^(PL|UU|FL|RD|OLAK5uy_)[a-zA-Z0-9_-]+$/i', $trimmed)) {
            return $trimmed;
        }
        return null;
    }
}
