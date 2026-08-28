<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Generador de PDF minimalista en PHP nativo (sin dependencias).
 *
 * Implementa un subconjunto del estándar PDF 1.4 suficiente para imprimir
 * texto (fuente núcleo Helvetica), líneas, rectángulos y colores. Se usa para
 * generar documentos como el carnet de matriculados.
 *
 * Unidades: puntos (1 pt = 1/72 pulgada). Los textos se emiten en WinAnsi.
 */
final class Pdf
{
    public const ORIENT_VERTICAL = 'P';
    public const ORIENT_HORIZONTAL = 'L';

    private array $content = [];   // contenido de cada página (por índice 0-based)
    private array $orient = [];    // orientación de cada página
    private array $sizes = [];     // [w, h] por página
    private int $page = 0;
    private float $w = 0;
    private float $h = 0;
    private int $font = 0;
    private float $fontSize = 12;
    private array $colorText = [0, 0, 0];
    private array $colorFill = [0, 0, 0];
    private array $colorDraw = [0, 0, 0];
    private float $lineWidth = 1;
    private array $imageList = [];  // índice => ['data', 'w', 'h'] (XObjects globales)
    private array $pageImages = []; // página => lista de índices de imágenes usadas

    public function __construct(string $orientation = self::ORIENT_VERTICAL, ?float $customW = null, ?float $customH = null)
    {
        $this->addPage($orientation, $customW, $customH);
    }

    public function addPage(string $orientation = self::ORIENT_VERTICAL, ?float $customW = null, ?float $customH = null): void
    {
        $this->page = count($this->content);
        if ($customW !== null && $customH !== null && $customW > 0 && $customH > 0) {
            $w = $customW;
            $h = $customH;
        } elseif ($orientation === self::ORIENT_HORIZONTAL) {
            $w = 841.89;
            $h = 595.28;
        } else {
            $w = 595.28;
            $h = 841.89;
        }
        $this->orient[$this->page] = $orientation;
        $this->sizes[$this->page] = [$w, $h];
        $this->content[$this->page] = '';
        $this->w = $w;
        $this->h = $h;
    }

    public function setFont(string $family = 'Helvetica', string $style = '', float $size = 12): void
    {
        $style = strtoupper($style);
        if (str_contains($style, 'B') && str_contains($style, 'I')) {
            $this->font = 3;
        } elseif (str_contains($style, 'B')) {
            $this->font = 1;
        } elseif (str_contains($style, 'I')) {
            $this->font = 2;
        } else {
            $this->font = 0;
        }
        $this->fontSize = $size;
    }

    public function setTextColor(int $r, int $g, int $b): void
    {
        $this->colorText = [$r / 255, $g / 255, $b / 255];
    }

    public function setFillColor(int $r, int $g, int $b): void
    {
        $this->colorFill = [$r / 255, $g / 255, $b / 255];
    }

    public function setDrawColor(int $r, int $g, int $b): void
    {
        $this->colorDraw = [$r / 255, $g / 255, $b / 255];
    }

    public function setLineWidth(float $width): void
    {
        $this->lineWidth = $width;
    }

    /** Rectángulo. $style: '' (solo), 'F' relleno, 'D' trazo, 'FD' ambos. */
    public function rect(float $x, float $y, float $w, float $h, string $style = ''): void
    {
        $op = 'n';
        if (str_contains($style, 'F') && str_contains($style, 'D')) {
            $op = 'B';
        } elseif (str_contains($style, 'F')) {
            $op = 'f';
        } elseif (str_contains($style, 'D')) {
            $op = 'S';
        }
        $this->setGraphicsState('');
        $this->out(sprintf('%.2F %.2F %.2F %.2F re %s', $x, $y, $w, $h, $op));
    }

    public function line(float $x1, float $y1, float $x2, float $y2): void
    {
        $this->setGraphicsState('D');
        $this->out(sprintf('%.2F %.2F m %.2F %.2F l S', $x1, $y1, $x2, $y2));
    }

    /** Texto en (x,y) en puntos desde la esquina superior izquierda. */
    public function text(float $x, float $y, string $txt, string $align = 'L'): void
    {
        $pdfY = $this->h - $y;
        if ($align === 'C') {
            $x -= $this->getStringWidth($txt) / 2;
        } elseif ($align === 'R') {
            $x -= $this->getStringWidth($txt);
        }
        $this->out(sprintf(
            'BT /F%d %.2F Tf %.3F %.3F %.3F rg %.2F %.2F Td (%s) Tj ET',
            $this->font,
            $this->fontSize,
            $this->colorText[0],
            $this->colorText[1],
            $this->colorText[2],
            $x,
            $pdfY,
            $this->escape($txt)
        ));
    }

    public function getStringWidth(string $txt): float
    {
        $w = 0.0;
        $len = strlen($txt);
        for ($i = 0; $i < $len; $i++) {
            $w += $this->charWidth(ord($txt[$i]));
        }
        return $w * $this->fontSize / 1000;
    }

    /**
     * Dibuja una imagen en (x, y) con tamaño (w, h) en puntos.
     * Acepta JPG/PNG/WEBP/GIF; se normaliza a JPEG (GD) para incrustar.
     * Devuelve true si se incrustó, false si falló (no lanza excepción).
     */
    public function image(float $x, float $y, float $w, float $h, string $filePath): bool
    {
        if (!is_file($filePath)) {
            return false;
        }

        $jpeg = self::normalizeToJpeg($filePath);
        if ($jpeg === null) {
            return false;
        }

        [$data, $pxW, $pxH] = $jpeg;

        $imgIndex = count($this->imageList);
        $this->imageList[$imgIndex] = ['data' => $data, 'w' => $pxW, 'h' => $pxH];

        if (!isset($this->pageImages[$this->page])) {
            $this->pageImages[$this->page] = [];
        }
        $this->pageImages[$this->page][] = $imgIndex;

        $pdfX = $x;
        $pdfY = $this->h - ($y + $h);

        $this->out(sprintf(
            "q %.2F 0 0 %.2F %.2F %.2F cm /Im%d Do Q",
            $w,
            $h,
            $pdfX,
            $pdfY,
            $imgIndex
        ));
        return true;
    }

    /**
     * Convierte cualquier imagen soportada a JPEG. Devuelve [datos, ancho, alto] o null.
     */
    private function normalizeToJpeg(string $filePath): ?array
    {
        $mime = strtolower((string)(mime_content_type($filePath) ?: ''));

        if ($mime === 'image/jpeg') {
            $data = @file_get_contents($filePath);
            $info = @getimagesize($filePath);
            if ($data === false || $info === false) {
                return null;
            }
            return [$data, $info[0], $info[1]];
        }

        $img = match ($mime) {
            'image/png'  => @imagecreatefrompng($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            'image/gif'  => @imagecreatefromgif($filePath),
            default      => false,
        };

        if ($img === false) {
            return null;
        }

        if (imageistruecolor($img) === false) {
            imagepalettetotruecolor($img);
        }

        ob_start();
        $ok = imagejpeg($img, null, 85);
        $data = ob_get_clean();
        imagedestroy($img);

        if (!$ok || $data === false) {
            return null;
        }
        $info = @getimagesize($filePath);

        return [$data, $info[0] ?? 0, $info[1] ?? 0];
    }

    private function setGraphicsState(string $kind): void
    {
        // Texto: estado de color de dibujo/relleno y grosor de línea por operación.
        if ($kind === 'D') {
            $this->out(sprintf('%.2F w %.3F %.3F %.3F RG', $this->lineWidth, $this->colorDraw[0], $this->colorDraw[1], $this->colorDraw[2]));
        } else {
            $this->out(sprintf('%.2F w %.3F %.3F %.3F RG', $this->lineWidth, $this->colorDraw[0], $this->colorDraw[1], $this->colorDraw[2]));
            $this->out(sprintf('%.3F %.3F %.3F rg', $this->colorFill[0], $this->colorFill[1], $this->colorFill[2]));
        }
    }

    private function charWidth(int $c): float
    {
        $widths = [
            32 => 278, 33 => 278, 34 => 355, 35 => 556, 36 => 556, 37 => 889, 38 => 667, 39 => 191,
            40 => 333, 41 => 333, 42 => 389, 43 => 584, 44 => 278, 45 => 333, 46 => 278, 47 => 278,
            48 => 556, 49 => 556, 50 => 556, 51 => 556, 52 => 556, 53 => 556, 54 => 556, 55 => 556,
            56 => 556, 57 => 556, 58 => 278, 59 => 278, 60 => 584, 61 => 584, 62 => 584, 63 => 556,
            64 => 1015, 65 => 667, 66 => 667, 67 => 722, 68 => 722, 69 => 667, 70 => 611, 71 => 778,
            72 => 722, 73 => 278, 74 => 500, 75 => 667, 76 => 556, 77 => 833, 78 => 722, 79 => 778,
            80 => 667, 81 => 778, 82 => 722, 83 => 667, 84 => 611, 85 => 722, 86 => 667, 87 => 944,
            88 => 667, 89 => 667, 90 => 611, 91 => 278, 92 => 278, 93 => 278, 94 => 469, 95 => 556,
            96 => 333, 97 => 556, 98 => 556, 99 => 500, 100 => 556, 101 => 556, 102 => 278, 103 => 556,
            104 => 556, 105 => 222, 106 => 222, 107 => 500, 108 => 222, 109 => 833, 110 => 556, 111 => 556,
            112 => 556, 113 => 556, 114 => 333, 115 => 500, 116 => 278, 117 => 556, 118 => 500, 119 => 722,
            120 => 500, 121 => 500, 122 => 500, 123 => 334, 124 => 260, 125 => 334, 126 => 584, 127 => 350,
        ];
        return $widths[$c] ?? 500;
    }

    private function escape(string $s): string
    {
        $s = $this->utf8ToCp1252($s);
        return strtr($s, [
            '\\' => '\\\\',
            '(' => '\\(',
            ')' => '\\)',
            "\r" => '\\r',
            "\n" => '\\n',
        ]);
    }

    private function utf8ToCp1252(string $s): string
    {
        return mb_convert_encoding($s, 'CP1252', 'UTF-8');
    }

    private function out(string $op): void
    {
        $this->content[$this->page] .= $op . "\n";
    }

    /** Devuelve el contenido binario del PDF. */
    public function output(): string
    {
        $objects = [];
        $offsets = [];

        /** Fuentes núcleo estándar (no requieren incrustación). */
        $fontNames = ['Helvetica', 'Helvetica-Bold', 'Helvetica-Oblique', 'Helvetica-BoldOblique'];
        $fontCount = count($fontNames);

        // Objetos de fuente /F0..F3 (objetos 1..4)
        for ($i = 0; $i < $fontCount; $i++) {
            $objects[$i + 1] = sprintf(
                '<< /Type /Font /Subtype /Type1 /BaseFont /%s /Encoding /WinAnsiEncoding >>',
                $fontNames[$i]
            );
        }

        // Numeración: 1..4 fuentes, 5 catálogo, 6 páginas, 7+ página/stream
        $catalogObj = 5;
        $pagesObj = 6;
        $next = 7;

        $pageObjects = [];
        $streamObjects = [];
        foreach ($this->content as $idx => $ignored) {
            $pageObjects[$idx] = $next++;
            $streamObjects[$idx] = $next++;
        }

        // Los XObjects de imagen van después de todas las páginas/streams.
        $imgObjBase = $next;

        // Catálogo
        $objects[$catalogObj] = sprintf('<< /Type /Catalog /Pages %d 0 R >>', $pagesObj);

        // Nodo padre de páginas
        $kidsList = [];
        foreach ($pageObjects as $idx => $pageObjNum) {
            $kidsList[] = $pageObjNum . ' 0 R';
        }
        $objects[$pagesObj] = sprintf(
            '<< /Type /Pages /Kids [%s] /Count %d >>',
            implode(' ', $kidsList),
            count($kidsList)
        );

        // Páginas individuales + streams
        foreach ($this->content as $idx => $contentStream) {
            $size = $this->sizes[$idx];

            $imgs = '';
            if (!empty($this->pageImages[$idx])) {
                $dict = [];
                foreach ($this->pageImages[$idx] as $imgIdx) {
                    $dict[] = sprintf('/Im%d %d 0 R', $imgIdx, $imgObjBase + $imgIdx);
                }
                $imgs = ' /XObject << ' . implode(' ', $dict) . ' >>';
            }

            $objects[$pageObjects[$idx]] = sprintf(
                '<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F0 %d 0 R /F1 %d 0 R /F2 %d 0 R /F3 %d 0 R >>%s >> /Contents %d 0 R >>',
                $pagesObj,
                $size[0],
                $size[1],
                1,
                2,
                3,
                4,
                $imgs,
                $streamObjects[$idx]
            );
            $objects[$streamObjects[$idx]] = $this->streamObject($contentStream);
        }

        // Objetos de imagen (XObject JPEG / DCTDecode)
        foreach ($this->imageList as $imgIdx => $img) {
            $objects[$imgObjBase + $imgIdx] = $this->imageObject($img['data'], $img['w'], $img['h']);
        }

        // Ordenar objetos por número e imprimir
        ksort($objects);
        $pdf = "%PDF-1.4\n";
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $num, $body);
        }

        $xrefOffset = strlen($pdf);
        $max = max(array_keys($offsets));
        $pdf .= sprintf("xref\n0 %d\n", $max + 1);
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $max; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= sprintf("trailer\n<< /Size %d /Root %d 0 R >>\nstartxref\n%d\n%%%%EOF", $max + 1, $catalogObj, $xrefOffset);
        return $pdf;
    }

    private function streamObject(string $data): string
    {
        return sprintf(
            "<< /Length %d >>\nstream\n%s\nendstream",
            strlen($data),
            $data
        );
    }

    private function imageObject(string $jpegData, int $width, int $height): string
    {
        $body = sprintf(
            "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
            $width,
            $height,
            strlen($jpegData),
            $jpegData
        );
        return $body;
    }
}
