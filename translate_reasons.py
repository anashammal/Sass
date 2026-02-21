import json
import os

langs = {
    "ar": {
        "reason_quick_adjustment": "تعديل مخزون سريع",
        "reason_quick_adjustment_pos": "تعديل مخزون سريع من نقطة البيع"
    },
    "en": {
        "reason_quick_adjustment": "Quick Stock Adjustment",
        "reason_quick_adjustment_pos": "Quick Stock Adjustment from POS"
    },
    "fr": {
        "reason_quick_adjustment": "Ajustement rapide du stock",
        "reason_quick_adjustment_pos": "Ajustement rapide du stock depuis le point de vente"
    },
    "es": {
        "reason_quick_adjustment": "Ajuste rápido de stock",
        "reason_quick_adjustment_pos": "Ajuste rápido de stock desde TPV"
    },
    "de": {
        "reason_quick_adjustment": "Schnelle Bestandsanpassung",
        "reason_quick_adjustment_pos": "Schnelle Bestandsanpassung über Kasse"
    },
    "tr": {
        "reason_quick_adjustment": "Hızlı Stok Düzenleme",
        "reason_quick_adjustment_pos": "POS Üzerinden Hızlı Stok Düzenleme"
    },
    "ru": {
        "reason_quick_adjustment": "Быстрая корректировка запасов",
        "reason_quick_adjustment_pos": "Быстрая корректировка запасов из POS"
    },
    "pt": {
        "reason_quick_adjustment": "Ajuste Rápido de Estoque",
        "reason_quick_adjustment_pos": "Ajuste Rápido de Estoque do PDV"
    },
    "pt-BR": {
        "reason_quick_adjustment": "Ajuste Rápido de Estoque",
        "reason_quick_adjustment_pos": "Ajuste Rápido de Estoque do PDV"
    },
    "hr": {
        "reason_quick_adjustment": "Brzo prilagođavanje zaliha",
        "reason_quick_adjustment_pos": "Brzo prilagođavanje zaliha s POS-a"
    },
    "ja": {
        "reason_quick_adjustment": "クイック在庫調整",
        "reason_quick_adjustment_pos": "POSからのクイック在庫調整"
    },
    "zh": {
        "reason_quick_adjustment": "快速库存调整",
        "reason_quick_adjustment_pos": "POS快速库存调整"
    }
}

for lang, mapping in langs.items():
    filepath = f"resources/lang/{lang}.json"
    if os.path.exists(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        for k, v in mapping.items():
            data[k] = v
            
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)
        print(f"Updated {filepath} for log reasons")

print("Finished syncing log reasons.")
