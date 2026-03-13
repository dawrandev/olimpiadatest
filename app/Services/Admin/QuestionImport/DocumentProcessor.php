<?php

namespace App\Services\Admin\QuestionImport;

use Illuminate\Support\Facades\Log;

class DocumentProcessor
{
    protected string $documentText = '';
    protected array $extractedImages = [];

    public function extractContentAsHtml(string $docxPath, string $tempDir): string
    {
        $pandocCheck = shell_exec('pandoc --version 2>&1');
        if (empty($pandocCheck) || stripos($pandocCheck, 'pandoc') === false) {
            throw new \Exception('Pandoc o\'rnatilmagan.');
        }

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mediaDir = $tempDir . '/media_' . uniqid();
        if (!file_exists($mediaDir)) {
            mkdir($mediaDir, 0755, true);
        }

        $tempHtml = $tempDir . '/content_' . uniqid() . '.html';
        $docxPathEscaped = '"' . str_replace('"', '""', $docxPath) . '"';
        $htmlPathEscaped = '"' . str_replace('"', '""', $tempHtml) . '"';
        $pandoc = $this->getPandocPath();

        $command = "{$pandoc} {$docxPathEscaped} -f docx -t html --mathml --wrap=none --extract-media=\"{$mediaDir}\" -o {$htmlPathEscaped} 2>&1";

        $output = shell_exec($command);

        if (!file_exists($tempHtml)) {
            throw new \Exception('Pandoc HTML yaratishda xatolik: ' . $output);
        }

        $this->documentText = file_get_contents($tempHtml);

        if (empty($this->documentText)) {
            throw new \Exception('HTML fayl bo\'sh.');
        }

        $this->processExtractedMedia($mediaDir);

        @unlink($tempHtml);
        $this->deleteDirectory($mediaDir);

        return $this->documentText;
    }

    protected function cleanLaTeXFormat(string $html): string
    {
        if (substr_count($html, '<math') > 0) {
            $html = preg_replace('/\\\\\(.*?\\\\\)/s', '', $html);
            $html = preg_replace('/\\\\\[.*?\\\\\]/s', '', $html);
        }

        return $html;
    }

    protected function getPandocPath(): string
    {
        $path = env('PANDOC_PATH', 'pandoc');
        return '"' . str_replace('"', '""', $path) . '"';
    }

    protected function processExtractedMedia(string $mediaDir): void
    {
        $mediaPath = $mediaDir . '/media';

        if (!file_exists($mediaPath) || !is_dir($mediaPath)) {
            return;
        }

        $files = scandir($mediaPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourcePath = $mediaPath . '/' . $file;

            if (!is_file($sourcePath)) {
                continue;
            }

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $fileName = 'question_' . time() . '_' . uniqid() . '.' . $extension;
            $destinationPath = public_path('storage/questions/' . $fileName);

            $directory = public_path('storage/questions');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            if (copy($sourcePath, $destinationPath)) {
                $this->documentText = preg_replace(
                    '/<img[^>]*src="[^"]*' . preg_quote($file, '/') . '"[^>]*>/i',
                    '<img data-image="' . $fileName . '">',
                    $this->documentText
                );
            }
        }
    }

    public function extractImagesFromDocx(string $docxPath): array
    {
        $images = [];

        try {
            $zip = new \ZipArchive();

            if ($zip->open($docxPath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);

                    if (strpos($filename, 'word/media/') === 0) {
                        $imageData = $zip->getFromIndex($i);

                        if ($imageData !== false) {
                            $extension = pathinfo($filename, PATHINFO_EXTENSION);
                            $images[] = [
                                'data' => $imageData,
                                'extension' => $extension,
                                'original_name' => basename($filename)
                            ];
                        }
                    }
                }

                $zip->close();
            }
        } catch (\Exception $e) {
            Log::error('Image extraction error: ' . $e->getMessage());
        }

        return $images;
    }

    public function extractLinesFromDom(\DOMNode $node): array
    {
        $lines = [];
        $currentLine = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                $textLines = explode("\n", $text);

                foreach ($textLines as $i => $textLine) {
                    if ($i > 0 && !empty($currentLine)) {
                        $lines[] = $currentLine;
                        $currentLine = '';
                    }
                    $currentLine .= $textLine;
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                if ($child->nodeName === 'img') {
                    $imgHtml = $child->ownerDocument->saveHTML($child);
                    $currentLine .= $imgHtml;
                } elseif ($child->nodeName === 'math') {
                    $mathHtml = $child->ownerDocument->saveHTML($child);
                    $currentLine .= $mathHtml;
                } elseif (
                    $child->nodeName === 'span' &&
                    ($child->getAttribute('class') === 'math display' ||
                        $child->getAttribute('class') === 'math inline')
                ) {
                    $spanHtml = $child->ownerDocument->saveHTML($child);
                    $currentLine .= $spanHtml;
                } elseif (in_array($child->nodeName, ['p', 'div', 'br'])) {
                    if (!empty($currentLine)) {
                        $lines[] = $currentLine;
                        $currentLine = '';
                    }

                    $childLines = $this->extractLinesFromDom($child);
                    $lines = array_merge($lines, $childLines);
                } elseif (in_array($child->nodeName, ['sup', 'sub', 'strong', 'em', 'i', 'b'])) {
                    $inlineHtml = $child->ownerDocument->saveHTML($child);
                    $currentLine .= $inlineHtml;
                } else {
                    $inlineHtml = $child->ownerDocument->saveHTML($child);
                    $currentLine .= $inlineHtml;
                }
            }
        }

        if (!empty($currentLine)) {
            $lines[] = $currentLine;
        }

        return $lines;
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
