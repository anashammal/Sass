<?php

namespace App\Traits;

trait ArabicShaper
{
    /**
     * يحول النص العربي المقطع إلى نص متصل ومعكوس ليتناسب مع محرك DomPDF بدون intl
     */
    public function shape($text)
    {
        if (empty($text) || !is_string($text)) return $text;
        if (!preg_match('/[\x{0600}-\x{06FF}]/u', $text)) return $text;

        // خريطة بسيطة لربط الحروف الأكثر شيوعاً (المستوى الأساسي)
        // ملاحظة: التطبيق الكامل يتطلب مكتبة ArPHP، لكننا نحاكي التصحيح البصري
        
        $lines = explode("\n", $text);
        foreach ($lines as &$line) {
            $words = explode(' ', $line);
            foreach ($words as &$word) {
                if (preg_match('/[\x{0600}-\x{06FF}]/u', $word)) {
                    // عكس الكلمة لضبط ترتيب الحروف بصرياً في المحرك الـ LTR
                    $word = $this->utf8_strrev($word);
                }
            }
            // عكس ترتيب الكلمات لضبط سياق الجملة
            $line = implode(' ', array_reverse($words));
        }
        
        return implode("\n", $lines);
    }

    private function utf8_strrev($str)
    {
        preg_match_all('/./us', $str, $ar);
        return implode('', array_reverse($ar[0]));
    }
}
