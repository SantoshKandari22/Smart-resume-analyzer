<?php

class ResumeParser {

    // ──────────────────────────────────────────────
    //  Main entry: dispatch by extension
    // ──────────────────────────────────────────────
    public static function extractText(string $filePath): string {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'docx') {
            return self::extractFromDocx($filePath);
        } elseif ($ext === 'pdf') {
            return self::extractFromPdf($filePath);
        }
        return '';
    }

    // ──────────────────────────────────────────────
    //  DOCX: unzip → word/document.xml → strip tags
    // ──────────────────────────────────────────────
    private static function extractFromDocx(string $filePath): string {
        if (!class_exists('ZipArchive')) {
            return ''; // ZipArchive extension not loaded
        }
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            return '';
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) return '';

        // Convert <w:p> paragraph tags to newlines before stripping
        $xml = preg_replace('/<w:p[ >]/', "\n<w:p ", $xml);
        return self::clean(strip_tags($xml));
    }

    // ──────────────────────────────────────────────
    //  PDF: read raw bytes, extract printable ASCII.
    //  NOTE: accurate only for text-based (non-scanned) PDFs.
    //  For production use: composer require smalot/pdfparser
    // ──────────────────────────────────────────────
    private static function extractFromPdf(string $filePath): string {
        $raw = @file_get_contents($filePath);
        if (!$raw) return '';

        // Extract content streams between BT ... ET markers (PDF text operators)
        $text = '';
        if (preg_match_all('/BT(.*?)ET/s', $raw, $matches)) {
            foreach ($matches[1] as $block) {
                // Grab strings inside () or <>
                preg_match_all('/\(([^)]*)\)|<([0-9A-Fa-f]+)>/', $block, $m);
                foreach ($m[1] as $s) {
                    $text .= ' ' . $s;
                }
                foreach ($m[2] as $hex) {
                    $text .= ' ' . pack('H*', $hex);
                }
            }
        }

        if (strlen(trim($text)) < 50) {
            // Fallback: strip binary noise from raw content
            $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/', ' ', $raw);
        }

        return self::clean($text);
    }

    // ──────────────────────────────────────────────
    //  Clean extracted text
    // ──────────────────────────────────────────────
    private static function clean(string $text): string {
        // Collapse whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/(\r\n|\r|\n){2,}/', "\n", $text);
        return trim($text);
    }

    // ──────────────────────────────────────────────
    //  Skill keyword extraction
    // ──────────────────────────────────────────────
    public static function extractSkills(string $text): array {
        // Ordered by category for clarity
        $skillDictionary = [
            // Backend
            'PHP', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Go', 'Node.js',
            // Frontend
            'JavaScript', 'TypeScript', 'HTML', 'CSS', 'React', 'Angular',
            'Vue.js', 'Bootstrap', 'Tailwind',
            // Databases
            'SQL', 'MySQL', 'MongoDB', 'PostgreSQL', 'Redis', 'SQLite',
            // Frameworks
            'Laravel', 'Django', 'Spring', 'Flask', 'Express',
            // DevOps / Tools
            'Git', 'Docker', 'Linux', 'REST API', 'GraphQL', 'AWS',
        ];

        $found = [];
        foreach ($skillDictionary as $skill) {
            // Word-boundary match, case-insensitive
            $pattern = '/\b' . preg_quote($skill, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                $found[] = $skill; // Store canonical casing
            }
        }

        return array_unique($found);
    }
}
?>
