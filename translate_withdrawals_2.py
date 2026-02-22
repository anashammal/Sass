import json
import os

langs = {
    "ar": {
        "details_btn": "التفاصيل",
        "return_btn_label": "إرجاع",
        "no_withdrawals_recorded": "لا توجد مسحوبات مسجلة"
    },
    "en": {
        "details_btn": "Details",
        "return_btn_label": "Return",
        "no_withdrawals_recorded": "No withdrawals recorded"
    },
    "fr": {
        "details_btn": "Détails",
        "return_btn_label": "Retourner",
        "no_withdrawals_recorded": "Aucun retrait enregistré"
    },
    "es": {
        "details_btn": "Detalles",
        "return_btn_label": "Devolver",
        "no_withdrawals_recorded": "No hay retiros registrados"
    },
    "de": {
        "details_btn": "Details",
        "return_btn_label": "Zurückgeben",
        "no_withdrawals_recorded": "Keine Entnahmen registriert"
    },
    "tr": {
        "details_btn": "Detaylar",
        "return_btn_label": "İade",
        "no_withdrawals_recorded": "Kayıtlı çekim bulunamadı"
    },
    "ru": {
        "details_btn": "Детали",
        "return_btn_label": "Возврат",
        "no_withdrawals_recorded": "Нет зарегистрированных изъятий"
    },
    "pt": {
        "details_btn": "Detalhes",
        "return_btn_label": "Devolver",
        "no_withdrawals_recorded": "Nenhum saque registrado"
    },
    "pt-BR": {
        "details_btn": "Detalhes",
        "return_btn_label": "Devolver",
        "no_withdrawals_recorded": "Nenhum saque registrado"
    },
    "hr": {
        "details_btn": "Detalji",
        "return_btn_label": "Natrag",
        "no_withdrawals_recorded": "Nema zabilježenih povlačenja"
    },
    "ja": {
        "details_btn": "詳細",
        "return_btn_label": "返品",
        "no_withdrawals_recorded": "引き出し記録がありません"
    },
    "zh": {
        "details_btn": "详情",
        "return_btn_label": "退回",
        "no_withdrawals_recorded": "没有记录提款"
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
        print(f"Updated {filepath} for missing table buttons")

print("Finished syncing table translations.")
