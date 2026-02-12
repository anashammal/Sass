<?php
// دالة لتوليد مفاتيح التشفير (RSA Key Pair)
function generateRSAKeys() {
    $config = array(
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    // محاولة استخدام إعدادات افتراضية إذا لم يتم العثور على ملف openssl.cnf
    $res = openssl_pkey_new($config);
    
    if (!$res) {
        // إذا فشل، نحاول بدون مصفوفة الإعدادات (الاكتفاء بالافتراضي)
        $res = openssl_pkey_new();
    }

    // استخراج المفتاح الخاص (Private Key)
    openssl_pkey_export($res, $privKey);

    // استخراج المفتاح العام (Public Key)
    $pubKey = openssl_pkey_get_details($res);
    $pubKey = $pubKey["key"];

    return [
        'private' => $privKey,
        'public' => $pubKey
    ];
}

$keys = generateRSAKeys();

echo "--- [ PUBLIC KEY ] ---\n";
echo "انسخ هذا الجزء وضعه في خانة 'Public Key' في الموقع:\n\n";
echo $keys['public'];

echo "\n\n--- [ PRIVATE KEY ] ---\n";
echo "احفظ هذا الجزء في مكان آمن جداً (سنحتاجه في ملف .env لاحقاً):\n\n";
echo $keys['private'];
?>
