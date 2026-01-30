<?php

namespace App\Services;

use ArPHP\I18N\Arabic;

class ArabicTextService
{
    protected $arabic;

    public function __construct()
    {
        $this->arabic = new Arabic();
    }

    /**
     * معالجة النص العربي ليظهر متصلاً بشكل صحيح في PDF
     * يقوم بربط الحروف وتصحيح الاتجاه
     */
    public function shape($text)
    {
        if (empty($text) || !is_string($text)) {
            return $text;
        }

        // التحقق من وجود حروف عربية
        if (!preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text;
        }

        try {
            // 1. ربط الحروف العربية (Glyphs)
            $shapedText = $this->arabic->utf8Glyphs($text);
            
            return $shapedText;
        } catch (\Exception $e) {
            // في حالة الخطأ، نعيد النص الأصلي
            return $text;
        }
    }

    /**
     * معالجة مجموعة من النصوص
     */
    public function shapeArray(array $texts)
    {
        return array_map([$this, 'shape'], $texts);
    }
}
