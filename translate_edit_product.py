import json
import os

translations = {
    "تعديل المنتج": {
        "en": "Edit Product",
        "fr": "Modifier le produit",
        "tr": "Ürünü Düzenle",
        "de": "Produkt bearbeiten",
        "ru": "Изменить товар",
        "es": "Editar producto",
        "pt-BR": "Editar produto",
        "pt": "Editar produto",
        "hr": "Uredi proizvod",
        "ja": "製品を編集",
        "zh": "编辑产品"
    },
    "الوحدة الأساسية": {
        "en": "Base Unit",
        "fr": "Unité de base",
        "tr": "Temel Birim",
        "de": "Basiseinheit",
        "ru": "Базовая единица",
        "es": "Unidad base",
        "pt-BR": "Unidade base",
        "pt": "Unidade base",
        "hr": "Osnovna jedinica",
        "ja": "基本単位",
        "zh": "基本单位"
    },
    "سعر الشراء": {
        "en": "Purchase Price",
        "fr": "Prix d'achat",
        "tr": "Satın Alma Fiyatı",
        "de": "Kaufpreis",
        "ru": "Цена покупки",
        "es": "Precio de compra",
        "pt-BR": "Preço de compra",
        "pt": "Preço de compra",
        "hr": "Nabavna cijena",
        "ja": "購入価格",
        "zh": "采购价格"
    },
    "الوحدات الإضافية": {
        "en": "Extra Units",
        "fr": "Unités supplémentaires",
        "tr": "Ekstra Birimler",
        "de": "Zusätzliche Einheiten",
        "ru": "Дополнительные единицы",
        "es": "Unidades adicionales",
        "pt-BR": "Unidades adicionais",
        "pt": "Unidades adicionais",
        "hr": "Dodatne jedinice",
        "ja": "追加単位",
        "zh": "额外单位"
    },
    "تنبيه نقص الكمية والتقادم": {
        "en": "Low Stock and Aging Alert",
        "fr": "Alerte de stock faible et de vieillissement",
        "tr": "Düşük Stok ve Eskime Uyarısı",
        "de": "Warnung bei geringem Lagerbestand und Alterung",
        "ru": "Оповещение о низком запасе и устаревании",
        "es": "Alerta de stock bajo y envejecimiento",
        "pt-BR": "Alerta de estoque baixo e envelhecimento",
        "pt": "Alerta de stock baixo e envelhecimento",
        "hr": "Upozorenje na niske zalihe i starenje",
        "ja": "低在庫およびエイジング・アラート",
        "zh": "低库存和老化警报"
    },
    "عند قرب انتهاء الصلاحية بـ(أيام)": {
        "en": "When expiry is near by (days)",
        "fr": "Lorsque l'expiration est proche de (jours)",
        "tr": "Son kullanma tarihi yaklaştığında (gün)",
        "de": "Wenn der Ablauf kurz bevorsteht (Tage)",
        "ru": "Когда истечение срока близко (дни)",
        "es": "Cuando el vencimiento está cerca de (días)",
        "pt-BR": "Quando o vencimiento está próximo (dias)",
        "pt": "Quando o vencimento está próximo (dias)",
        "hr": "Kada se rok trajanja približava (dani)",
        "ja": "有効期限が近づいた時（日）",
        "zh": "当有效期临近时（天数）"
    },
    "سيصلك إشعار تلقائي قبل انتهاء الصلاحية بالعدد من الأيام": {
        "en": "You will receive an automatic notification before expiry by this number of days",
        "fr": "Vous recevrez une notification automatique avant l'expiration de ce nombre de jours",
        "tr": "Bu gün sayısı kadar son kullanma tarihinden önce otomatik bir bildirim alacaksınız",
        "de": "Sie erhalten eine automatische Benachrichtigung vor Ablauf dieser Anzahl von Tagen",
        "ru": "Вы получите автоматическое уведомление за это количество дней до истечения срока действия",
        "es": "Recibirás una notificación automática antes del vencimiento por este número de días",
        "pt-BR": "Você receberá uma notificação automática antes do vencimento por este número de dias",
        "pt": "Receberá une notificação automática antes do vencimento por este número de dias",
        "hr": "Primit ćete automatsku obavijest prije isteka roka za ovaj broj dana",
        "ja": "この日数までに、有効期限が切れる前に自動通知が届きます",
        "zh": "您将在此天数到期前收到自动通知"
    },
    "حفظ التعديلات": {
        "en": "Save Changes",
        "fr": "Enregistrer les modifications",
        "tr": "Değişiklikleri Kaydet",
        "de": "Änderungen speichern",
        "ru": "Сохранить изменения",
        "es": "Guardar cambios",
        "pt-BR": "Salvar alterações",
        "pt": "Guardar alterações",
        "hr": "Spremi promjene",
        "ja": "変更を保存",
        "zh": "保存更改"
    },
    "إضافة وحدة": {
        "en": "Add Unit",
        "fr": "Ajouter une unité",
        "tr": "Birim Ekle",
        "de": "Einheit hinzufügen",
        "ru": "Добавить единицу",
        "es": "Añadir unidad",
        "pt-BR": "Adicionar unidade",
        "pt": "Adicionar unidade",
        "hr": "Dodaj jedinicu",
        "ja": "単位を追加",
        "zh": "添加单位"
    }
}

lang_files = ["en", "fr", "tr", "de", "ru", "es", "pt", "pt-BR", "hr", "ja", "zh"]
lang_dir = "resources/lang"

for lang in lang_files:
    file_path = os.path.join(lang_dir, f"{lang}.json")
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        for ar_key, translations_dict in translations.items():
            if lang in translations_dict:
                data[ar_key] = translations_dict[lang]
        
        with open(file_path, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)
        print(f"Updated {lang}.json with edit product translations.")
    else:
        print(f"Skipping {lang}.json - file not found.")
