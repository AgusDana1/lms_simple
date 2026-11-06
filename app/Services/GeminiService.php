<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Question;
use App\Models\QuizAnswer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    protected ?string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = Config::get('services.gemini.key');
        $this->model = Config::get('services.gemini.model', 'gemini-3.8-flash');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Send parts to Gemini generateContent endpoint.
     *
     * @param  array<int, array<string, mixed>>  $parts
     * @param  array<string, mixed>  $config
     *
     * @throws \Exception
     */
    public function generateContent(array $parts, array $config = []): string
    {
        if (! $this->isConfigured()) {
            throw new \Exception('GEMINI_API_KEY belum dikonfigurasi di file .env atau config services.');
        }

        $cleanModel = preg_replace('#^models/#', '', $this->model);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$cleanModel}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => array_merge([
                'temperature' => 0.4,
            ], $config),
        ];

        $response = Http::timeout(60)->post($url, $payload);

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            Log::error('Gemini API Error: '.$errorMsg);
            throw new \Exception('Gemini API Error: '.$errorMsg);
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! $text) {
            throw new \Exception('Gemini API tidak mengembalikan teks respons.');
        }

        return $text;
    }

    /**
     * 1. AI Assignment Grading & PDF Submission Summary
     *
     * @return array{
     *     summary: string,
     *     analysis: string,
     *     suggested_score: float,
     *     suggested_feedback: string
     * }
     */
    public function analyzeAssignmentSubmission(Assignment $assignment, AssignmentSubmission $submission): array
    {
        $parts = [];

        $systemPrompt = <<<PROMPT
Anda adalah asisten dosen yang teliti, adil, objektif, dan profesional di perguruan tinggi.
Tugas Anda adalah memindai jawaban dan berkas tugas yang dikumpulkan mahasiswa, lalu memberikan referensi ringkasan dan penilaian untuk membantu dosen pengampu.

INFORMASI TUGAS:
- Judul Tugas: {$assignment->title}
- Instruksi & Deskripsi Tugas: {$assignment->description}
- Bobot Skor Maksimal: {$assignment->max_score}

JAWABAN TEKS MAHASISWA:
{$submission->content}

BERKAS YANG DIUNGGAH:
{$submission->file_name}

INSTRUKSI EVALUASI:
1. Pindai dan berikan "summary" (ringkasan jawaban/isi berkas mahasiswa) yang jelas, ringkas, dan menangkap esensi utama.
2. Berikan "analysis" (analisis kesesuaian): apakah mahasiswa menjawab sesuai instruksi tugas dosen, apa kelebihan dan kekurangannya.
3. Berikan "suggested_score" (rekomendasi skor nilai sementara) secara adil dan proporsional (skala 0 sampai {$assignment->max_score}).
4. Berikan "suggested_feedback" (rekomendasi catatan umpan balik yang konstruktif dan membangun untuk mahasiswa).

PENTING: Output HARUS selalu berupa JSON murni dengan format:
{
  "summary": "Ringkasan jawaban/dokumen...",
  "analysis": "Analisis kesesuaian tugas...",
  "suggested_score": 85,
  "suggested_feedback": "Catatan evaluasi untuk mahasiswa..."
}
PROMPT;

        $parts[] = ['text' => $systemPrompt];

        // Attach file content if available and readable
        if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
            $fullPath = Storage::disk('public')->path($submission->file_path);
            $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
            $fileSize = filesize($fullPath);

            // Gemini supports inline data up to 20MB for images & PDFs
            if ($fileSize > 0 && $fileSize <= 15 * 1024 * 1024 && in_array($mimeType, [
                'application/pdf',
                'image/png',
                'image/jpeg',
                'image/webp',
            ])) {
                $fileBytes = Storage::disk('public')->get($submission->file_path);
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data' => base64_encode($fileBytes),
                    ],
                ];
            } elseif (in_array($mimeType, ['text/plain', 'text/markdown', 'text/html', 'application/json'])) {
                $textData = Storage::disk('public')->get($submission->file_path);
                $parts[] = [
                    'text' => "--- ISI DOKUMEN TEKS MAHASISWA ---\n".substr($textData, 0, 30000),
                ];
            }
        }

        $rawOutput = $this->generateContent($parts, ['temperature' => 0.3]);
        $cleanJson = $this->cleanJsonString($rawOutput);
        $decoded = json_decode($cleanJson, true);

        if (! is_array($decoded)) {
            return [
                'summary' => $rawOutput,
                'analysis' => 'Format respons AI tidak terstruktur.',
                'suggested_score' => round($assignment->max_score * 0.8, 1),
                'suggested_feedback' => 'Hasil pengerjaan telah ditinjau.',
            ];
        }

        $suggestedScore = isset($decoded['suggested_score']) ? (float) $decoded['suggested_score'] : (float) $assignment->max_score;
        $suggestedScore = max(0, min($assignment->max_score, $suggestedScore));

        return [
            'summary' => $decoded['summary'] ?? 'Ringkasan tidak tersedia.',
            'analysis' => $decoded['analysis'] ?? 'Analisis tidak tersedia.',
            'suggested_score' => $suggestedScore,
            'suggested_feedback' => $decoded['suggested_feedback'] ?? 'Tugas telah diperiksa dengan baik.',
        ];
    }

    /**
     * 2. AI Quiz Question Generator
     *
     * @return array<int, array<string, mixed>>
     */
    public function generateQuizQuestions(string $topic, int $count = 5, string $difficulty = 'sedang', ?string $customInstruction = null): array
    {
        $prompt = <<<PROMPT
Anda adalah ahli pembuat butir soal ujian perkuliahan tingkat universitas yang kredibel, akurat, dan mendalam.
Buatkan {$count} butir soal kuis berdasarkan ketentuan berikut:

TOPIK / KATA KUNCI KUIS:
{$topic}

TINGKAT KESULITAN:
{$difficulty}

INSTRUKSI TAMBAHAN DARI DOSEN:
{$customInstruction}

KETENTUAN:
1. Buatkan sebanyak {$count} butir soal yang variatif dan komprehensif.
2. Format soal utama adalah Pilihan Ganda (multiple_choice dengan 4 opsi pilihan, salah satunya bernilai is_correct = true). Anda juga dapat menyertakan soal Benar/Salah (true_false) jika relevan.
3. Pertanyaan harus jelas, akademis, tidak ambigu, dan opsi jawaban pengecoh harus masuk akal (plausible).
4. Format output HARUS selalu berupa JSON murni berbentuk array data:

[
  {
    "type": "multiple_choice",
    "text": "Teks pertanyaan soal pilihan ganda...",
    "options": [
      {"option_text": "Pilihan A", "is_correct": true},
      {"option_text": "Pilihan B", "is_correct": false},
      {"option_text": "Pilihan C", "is_correct": false},
      {"option_text": "Pilihan D", "is_correct": false}
    ]
  },
  {
    "type": "true_false",
    "text": "Teks pernyataan soal benar atau salah...",
    "tf_correct": "true"
  }
]
PROMPT;

        $rawOutput = $this->generateContent([['text' => $prompt]], ['temperature' => 0.6]);
        $cleanJson = $this->cleanJsonString($rawOutput);
        $decoded = json_decode($cleanJson, true);

        if (! is_array($decoded)) {
            Log::warning('Gemini generateQuizQuestions returned invalid JSON: '.$rawOutput);
            throw new \Exception('AI tidak mengembalikan format butir soal yang valid.');
        }

        return $decoded;
    }

    /**
     * 3. AI Essay Answer Summary & Grading Reference for Quizzes
     *
     * @return array{
     *     summary: string,
     *     evaluation: string,
     *     suggested_score: float,
     *     notes: string
     * }
     */
    public function summarizeAndEvaluateEssay(Question $question, QuizAnswer $answer): array
    {
        $prompt = <<<PROMPT
Anda adalah asisten dosen yang membantu mengevaluasi jawaban esai mahasiswa pada kuis perkuliahan.
Jawaban esai seringkali panjang. Bantu dosen dengan membuat intisari jawaban dan memberikan rekomendasi penilaian.

PERTANYAAN SOAL ESAI:
{$question->text}

BOBOT MAKSIMAL SOAL:
{$question->points} Poin

JAWABAN MAHASISWA:
{$answer->answer_text}

TUGAS ANDA:
1. "summary": Buat intisari jawaban mahasiswa dalam 2-3 poin ringkas (apa poin-poin utama yang disampaikan mahasiswa).
2. "evaluation": Evaluasi apakah jawaban mahasiswa menjawab pertanyaan dengan tepat, lengkap, dan relevan.
3. "suggested_score": Rekomendasikan skor sementara (skala 0 sampai {$question->points} poin).
4. "notes": Catatan singkat pertimbangan penilaian untuk dosen.

Format output HARUS selalu berupa JSON murni dengan format:
{
  "summary": "Poin-poin intisari jawaban mahasiswa...",
  "evaluation": "Evaluasi kesesuaian materi...",
  "suggested_score": 8.0,
  "notes": "Pertimbangan rekomendasi skor..."
}
PROMPT;

        $rawOutput = $this->generateContent([['text' => $prompt]], ['temperature' => 0.3]);
        $cleanJson = $this->cleanJsonString($rawOutput);
        $decoded = json_decode($cleanJson, true);

        if (! is_array($decoded)) {
            return [
                'summary' => $rawOutput,
                'evaluation' => 'Format AI tidak terstruktur.',
                'suggested_score' => round($question->points * 0.75, 1),
                'notes' => 'Jawaban esai mahasiswa telah dibaca.',
            ];
        }

        $suggestedScore = isset($decoded['suggested_score']) ? (float) $decoded['suggested_score'] : (float) $question->points;
        $suggestedScore = max(0, min($question->points, $suggestedScore));

        return [
            'summary' => $decoded['summary'] ?? 'Ringkasan tidak tersedia.',
            'evaluation' => $decoded['evaluation'] ?? 'Evaluasi tidak tersedia.',
            'suggested_score' => $suggestedScore,
            'notes' => $decoded['notes'] ?? 'Catatan tidak tersedia.',
        ];
    }

    protected function cleanJsonString(string $text): string
    {
        $text = trim($text);

        // Strip markdown ```json ... ``` wrapper
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $text, $matches)) {
            $text = $matches[1];
        }

        return trim($text);
    }
}
