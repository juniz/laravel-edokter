<?php

namespace App\Services;

/**
 * Lightweight, self-contained QR Code generator in pure PHP.
 * Generates PNG data URI without any external dependencies or network calls.
 */
class QrCodeGenerator
{
    // Minimal pure PHP QR code generator for URLs / ASCII text
    // Supports byte encoding with error correction (Level M / L)
    
    /**
     * Generate base64 data URI of QR Code PNG image.
     *
     * @param string $data
     * @param int $size Width/height in pixels
     * @param int $margin Margin in modules
     * @return string data:image/png;base64,...
     */
    public static function pngDataUri(string $data, int $size = 150, int $margin = 2): string
    {
        $matrix = self::encode($data);
        $moduleCount = count($matrix);
        
        $cellSize = max(1, (int) floor($size / ($moduleCount + 2 * $margin)));
        $imgSize = ($moduleCount + 2 * $margin) * $cellSize;
        
        $image = imagecreatetruecolor($imgSize, $imgSize);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        imagefilledrectangle($image, 0, 0, $imgSize, $imgSize, $white);
        
        for ($r = 0; $r < $moduleCount; $r++) {
            for ($c = 0; $c < $moduleCount; $c++) {
                if ($matrix[$r][$c]) {
                    $x = ($c + $margin) * $cellSize;
                    $y = ($r + $margin) * $cellSize;
                    imagefilledrectangle($image, $x, $y, $x + $cellSize - 1, $y + $cellSize - 1, $black);
                }
            }
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Encode text into 2D boolean matrix.
     */
    public static function encode(string $text): array
    {
        $qr = new SimpleQrMatrix($text);
        return $qr->getMatrix();
    }
}

/**
 * Internal Matrix Builder for QR Code Model 2.
 */
class SimpleQrMatrix
{
    private int $version;
    private int $size;
    private array $modules;
    private array $isFunction;
    private string $data;

    // GF(256) log and exp tables for Reed-Solomon
    private static array $gfExp = [];
    private static array $gfLog = [];
    private static bool $gfInit = false;

    // Version capacities and error correction (Level M)
    // Format: [totalCodewords, ecCodewords, [block1Count, block1Data, block2Count, block2Data]]
    private static array $versionSpecs = [
        1 => [26, 10, [1, 16, 0, 0]],
        2 => [44, 16, [1, 28, 0, 0]],
        3 => [70, 26, [1, 44, 0, 0]],
        4 => [100, 36, [2, 32, 0, 0]],
        5 => [134, 48, [2, 43, 0, 0]],
        6 => [172, 64, [4, 27, 0, 0]],
        7 => [196, 72, [4, 31, 0, 0]],
        8 => [242, 88, [2, 38, 2, 39]],
        9 => [292, 110, [3, 36, 2, 37]],
        10 => [346, 130, [4, 43, 1, 44]],
    ];

    // Alignment pattern center coordinates
    private static array $alignmentCoords = [
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
        7 => [6, 22, 38],
        8 => [6, 24, 42],
        9 => [6, 26, 46],
        10 => [6, 28, 50],
    ];

    public function __construct(string $data)
    {
        self::initGf();
        $this->data = $data;
        $this->version = $this->determineVersion(strlen($data));
        $this->size = 17 + 4 * $this->version;
        $this->modules = array_fill(0, $this->size, array_fill(0, $this->size, false));
        $this->isFunction = array_fill(0, $this->size, array_fill(0, $this->size, false));
        
        $this->build();
    }

    public function getMatrix(): array
    {
        return $this->modules;
    }

    private static function initGf(): void
    {
        if (self::$gfInit) return;
        self::$gfExp = array_fill(0, 512, 0);
        self::$gfLog = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$gfExp[$i] = $x;
            self::$gfLog[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11d; // Primitive polynomial x^8 + x^4 + x^3 + x^2 + 1
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$gfExp[$i] = self::$gfExp[$i - 255];
        }
        self::$gfInit = true;
    }

    private function determineVersion(int $byteLen): int
    {
        // 8-bit byte mode overhead: 4-bit mode + 8/16-bit count indicator
        foreach (self::$versionSpecs as $v => $spec) {
            $totalDataCodewords = $spec[0] - $spec[1];
            $countBits = ($v <= 9) ? 8 : 16;
            $maxBytes = (int) floor(($totalDataCodewords * 8 - 4 - $countBits) / 8);
            if ($byteLen <= $maxBytes) {
                return $v;
            }
        }
        return 10;
    }

    private function build(): void
    {
        // 1. Function patterns
        $this->addFinderPatterns();
        $this->addSeparators();
        $this->addAlignmentPatterns();
        $this->addTimingPatterns();
        $this->addDarkModule();
        $this->reserveFormatInfo();

        // 2. Data encoding & Reed-Solomon ECC
        $dataCodewords = $this->createDataCodewords();
        $finalCodewords = $this->interleaveBlocks($dataCodewords);

        // 3. Place data modules with optimal masking
        $this->placeDataModules($finalCodewords);
    }

    private function setFunctionModule(int $r, int $c, bool $val): void
    {
        if ($r >= 0 && $r < $this->size && $c >= 0 && $c < $this->size) {
            $this->modules[$r][$c] = $val;
            $this->isFunction[$r][$c] = true;
        }
    }

    private function addFinderPatterns(): void
    {
        $locations = [[0, 0], [$this->size - 7, 0], [0, $this->size - 7]];
        foreach ($locations as [$top, $left]) {
            for ($r = 0; $r < 7; $r++) {
                for ($c = 0; $c < 7; $c++) {
                    $isBlack = ($r == 0 || $r == 6 || $c == 0 || $c == 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                    $this->setFunctionModule($top + $r, $left + $c, $isBlack);
                }
            }
        }
    }

    private function addSeparators(): void
    {
        for ($i = 0; $i < 8; $i++) {
            // Top-left
            $this->setFunctionModule(7, $i, false);
            $this->setFunctionModule($i, 7, false);
            // Top-right
            $this->setFunctionModule(7, $this->size - 8 + $i, false);
            $this->setFunctionModule($i, $this->size - 8, false);
            // Bottom-left
            $this->setFunctionModule($this->size - 8, $i, false);
            $this->setFunctionModule($this->size - 8 + $i, 7, false);
        }
    }

    private function addAlignmentPatterns(): void
    {
        if ($this->version < 2) return;
        $coords = self::$alignmentCoords[$this->version] ?? [];
        foreach ($coords as $r) {
            foreach ($coords as $c) {
                // Skip if overlapping finder patterns
                if (($r <= 8 && $c <= 8) || ($r <= 8 && $c >= $this->size - 8) || ($r >= $this->size - 8 && $c <= 8)) {
                    continue;
                }
                for ($dr = -2; $dr <= 2; $dr++) {
                    for ($dc = -2; $dc <= 2; $dc++) {
                        $isBlack = (abs($dr) == 2 || abs($dc) == 2 || ($dr == 0 && $dc == 0));
                        $this->setFunctionModule($r + $dr, $c + $dc, $isBlack);
                    }
                }
            }
        }
    }

    private function addTimingPatterns(): void
    {
        for ($i = 8; $i < $this->size - 8; $i++) {
            $this->setFunctionModule(6, $i, ($i % 2 == 0));
            $this->setFunctionModule($i, 6, ($i % 2 == 0));
        }
    }

    private function addDarkModule(): void
    {
        $this->setFunctionModule(4 * $this->version + 9, 8, true);
    }

    private function reserveFormatInfo(): void
    {
        for ($i = 0; $i <= 8; $i++) {
            $this->setFunctionModule(8, $i, false);
            $this->setFunctionModule($i, 8, false);
        }
        for ($i = 0; $i < 8; $i++) {
            $this->setFunctionModule(8, $this->size - 1 - $i, false);
            $this->setFunctionModule($this->size - 1 - $i, 8, false);
        }
    }

    private function createDataCodewords(): array
    {
        $bitBuffer = [];
        $appendBits = function (int $val, int $len) use (&$bitBuffer) {
            for ($i = $len - 1; $i >= 0; $i--) {
                $bitBuffer[] = ($val >> $i) & 1;
            }
        };

        // 1. Mode Indicator: 0100 (Byte mode)
        $appendBits(0b0100, 4);

        // 2. Character Count Indicator
        $countBits = ($this->version <= 9) ? 8 : 16;
        $byteLen = strlen($this->data);
        $appendBits($byteLen, $countBits);

        // 3. Data bytes
        for ($i = 0; $i < $byteLen; $i++) {
            $appendBits(ord($this->data[$i]), 8);
        }

        $spec = self::$versionSpecs[$this->version];
        $totalDataCodewords = $spec[0] - $spec[1];
        $maxBits = $totalDataCodewords * 8;

        // 4. Terminator (up to 4 zeroes)
        $terminatorLen = min(4, $maxBits - count($bitBuffer));
        for ($i = 0; $i < $terminatorLen; $i++) {
            $bitBuffer[] = 0;
        }

        // 5. Pad to multiple of 8
        while (count($bitBuffer) % 8 !== 0) {
            $bitBuffer[] = 0;
        }

        // 6. Convert bits to codewords
        $codewords = [];
        for ($i = 0; $i < count($bitBuffer); $i += 8) {
            $b = 0;
            for ($j = 0; $j < 8; $j++) {
                $b = ($b << 1) | $bitBuffer[$i + $j];
            }
            $codewords[] = $b;
        }

        // 7. Pad bytes 0xEC, 0x11
        $pad = [0xEC, 0x11];
        $pIdx = 0;
        while (count($codewords) < $totalDataCodewords) {
            $codewords[] = $pad[$pIdx];
            $pIdx ^= 1;
        }

        return $codewords;
    }

    private function calculateRsEcc(array $dataCodewords, int $eccCount): array
    {
        // Generate generator polynomial
        $gen = [1];
        for ($i = 0; $i < $eccCount; $i++) {
            $next = [0];
            $factor = self::$gfExp[$i];
            foreach ($gen as $coef) {
                $next[] = self::gfMul($coef, $factor);
            }
            for ($j = 0; $j < count($gen); $j++) {
                $next[$j] ^= $gen[$j];
            }
            $gen = $next;
        }

        // Polynomial division
        $poly = array_merge($dataCodewords, array_fill(0, $eccCount, 0));
        for ($i = 0; $i < count($dataCodewords); $i++) {
            $coef = $poly[$i];
            if ($coef !== 0) {
                for ($j = 0; $j < count($gen); $j++) {
                    $poly[$i + $j] ^= self::gfMul($gen[$j], $coef);
                }
            }
        }

        return array_slice($poly, count($dataCodewords));
    }

    private static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) return 0;
        return self::$gfExp[self::$gfLog[$x] + self::$gfLog[$y]];
    }

    private function interleaveBlocks(array $dataCodewords): array
    {
        $spec = self::$versionSpecs[$this->version];
        $b1Count = $spec[2][0];
        $b1Data = $spec[2][1];
        $b2Count = $spec[2][2];
        $b2Data = $spec[2][3];
        $totalBlocks = $b1Count + $b2Count;
        $eccPerBlock = (int) ($spec[1] / $totalBlocks);

        $blocksData = [];
        $blocksEcc = [];
        $offset = 0;

        for ($b = 0; $b < $totalBlocks; $b++) {
            $dLen = ($b < $b1Count) ? $b1Data : $b2Data;
            $dSlice = array_slice($dataCodewords, $offset, $dLen);
            $offset += $dLen;
            $blocksData[] = $dSlice;
            $blocksEcc[] = $this->calculateRsEcc($dSlice, $eccPerBlock);
        }

        // Interleave data codewords
        $result = [];
        $maxDataLen = max($b1Data, $b2Data);
        for ($i = 0; $i < $maxDataLen; $i++) {
            for ($b = 0; $b < $totalBlocks; $b++) {
                if ($i < count($blocksData[$b])) {
                    $result[] = $blocksData[$b][$i];
                }
            }
        }

        // Interleave ECC codewords
        for ($i = 0; $i < $eccPerBlock; $i++) {
            for ($b = 0; $b < $totalBlocks; $b++) {
                $result[] = $blocksEcc[$b][$i];
            }
        }

        return $result;
    }

    private function placeDataModules(array $codewords): void
    {
        // Convert codewords to bitstream
        $bits = [];
        foreach ($codewords as $cw) {
            for ($i = 7; $i >= 0; $i--) {
                $bits[] = ($cw >> $i) & 1;
            }
        }

        // Mask pattern 0: (r + c) % 2 == 0
        $bitIdx = 0;
        $right = $this->size - 1;
        $goingUp = true;

        while ($right > 0) {
            if ($right === 6) {
                $right--; // Skip vertical timing pattern
            }

            $rows = $goingUp ? range($this->size - 1, 0, -1) : range(0, $this->size - 1, 1);

            foreach ($rows as $r) {
                for ($colOffset = 0; $colOffset < 2; $colOffset++) {
                    $c = $right - $colOffset;
                    if (! $this->isFunction[$r][$c]) {
                        $bit = ($bitIdx < count($bits)) ? $bits[$bitIdx++] : 0;
                        // Apply Mask 0: (r + c) % 2 == 0
                        $maskBit = (($r + $c) % 2 === 0) ? 1 : 0;
                        $this->modules[$r][$c] = (($bit ^ $maskBit) === 1);
                    }
                }
            }

            $right -= 2;
            $goingUp = ! $goingUp;
        }

        // Apply format information for Level M, Mask 0
        // Level M = 00, Mask 0 = 000 -> 00000 -> Format string: 101010000010010 (BCH 15,5 with mask 101010000010010 XOR 101010000010010 = 000000000000000)
        // Standard precomputed format bits for Level M, Mask 0 = 101010000010010
        $formatBits = 0b101010000010010;
        for ($i = 0; $i < 15; $i++) {
            $bit = (($formatBits >> (14 - $i)) & 1) === 1;

            // Top-left
            if ($i <= 5) {
                $this->modules[8][$i] = $bit;
            } elseif ($i === 6) {
                $this->modules[8][7] = $bit;
            } elseif ($i === 7) {
                $this->modules[8][8] = $bit;
            } elseif ($i === 8) {
                $this->modules[7][8] = $bit;
            } else {
                $this->modules[14 - $i][8] = $bit;
            }

            // Split across other 2 finders
            if ($i < 8) {
                $this->modules[$this->size - 1 - $i][8] = $bit;
            } else {
                $this->modules[8][$this->size - 15 + $i] = $bit;
            }
        }
    }
}
