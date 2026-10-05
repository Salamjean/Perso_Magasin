<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class BarcodeService
{
    /**
     * Génère un code-barres EAN-13 unique et valide.
     */
    public function generateUniqueEan13(): string
    {
        do {
            // Préfixe interne standard 200 + 9 chiffres aléatoires
            $code = '200'.str_pad((string) mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
            $checksum = 0;
            for ($i = 0; $i < 12; $i++) {
                $checksum += (int) $code[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $checkDigit = (10 - ($checksum % 10)) % 10;
            $barcode = $code.$checkDigit;
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Génère un fichier SVG de code-barres et le stocke dans le disque public.
     * Retourne le chemin relatif du fichier créé.
     */
    public function generateBarcodeImage(string $barcode, string $productName = ''): string
    {
        $svgContent = $this->renderSvg($barcode, $productName);
        $fileName = 'barcodes/'.$barcode.'.svg';

        Storage::disk('public')->put($fileName, $svgContent);

        return $fileName;
    }

    /**
     * Rendu SVG pur autonome d'un code-barres (Code 128B / EAN-13).
     */
    public function renderSvg(string $code, string $label = ''): string
    {
        // Encodage Code 128
        $patterns = [
            '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
            '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
            '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
            '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
            '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
            '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
            '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
            '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
            '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
            '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
            '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
        ];

        $startCode = 104; // Start B
        $chars = str_split($code);
        $encoded = [$startCode];
        $checksum = $startCode;

        foreach ($chars as $i => $char) {
            $val = ord($char) - 32;
            if ($val < 0 || $val > 95) {
                $val = 0;
            }
            $encoded[] = $val;
            $checksum += $val * ($i + 1);
        }

        $checkDigit = $checksum % 103;
        $encoded[] = $checkDigit;
        $encoded[] = 106; // Stop

        $binary = '';
        foreach ($encoded as $val) {
            $pattern = $patterns[$val] ?? $patterns[0];
            $isBar = true;
            for ($k = 0; $k < strlen($pattern); $k++) {
                $width = (int) $pattern[$k];
                $binary .= str_repeat($isBar ? '1' : '0', $width);
                $isBar = ! $isBar;
            }
        }

        $barWidth = 2;
        $barHeight = 55;
        $padding = 20;
        $totalWidth = strlen($binary) * $barWidth + ($padding * 2);
        $totalHeight = $barHeight + 45;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$totalWidth.' '.$totalHeight.'" width="'.$totalWidth.'" height="'.$totalHeight.'">';
        $svg .= '<rect width="100%" height="100%" fill="#ffffff" rx="8" />';

        // Barres
        $x = $padding;
        for ($i = 0; $i < strlen($binary); $i++) {
            if ($binary[$i] === '1') {
                $svg .= '<rect x="'.$x.'" y="12" width="'.$barWidth.'" height="'.$barHeight.'" fill="#0f172a" />';
            }
            $x += $barWidth;
        }

        // Texte du code-barres en dessous
        $svg .= '<text x="'.($totalWidth / 2).'" y="'.($barHeight + 28).'" font-family="monospace, sans-serif" font-size="13" font-weight="bold" fill="#334155" text-anchor="middle" letter-spacing="3">'.htmlspecialchars($code).'</text>';

        $svg .= '</svg>';

        return $svg;
    }
}
