import json
import os

missing_keys = {
    "ar": {
        "whatsapp_btn": "واتساب",
        "email_btn": "إيميل",
        "back_to_cashier": "العودة للكاشير",
        "invoice_lbl": "فاتورة",
        "product_name_lbl": "المنتج",
        "barcode_lbl": "الباركود",
        "quantity_lbl": "الكمية",
        "final_total": "الإجمالي النهائي"
    },
    "en": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "Email",
        "back_to_cashier": "Back to Cashier",
        "invoice_lbl": "Invoice",
        "product_name_lbl": "Product",
        "barcode_lbl": "Barcode",
        "quantity_lbl": "Quantity",
        "final_total": "Final Total"
    },
    "fr": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "Email",
        "back_to_cashier": "Retour à la Caisse",
        "invoice_lbl": "Facture",
        "product_name_lbl": "Produit",
        "barcode_lbl": "Code-barres",
        "quantity_lbl": "Quantité",
        "final_total": "Total Final"
    },
    "es": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "Correo",
        "back_to_cashier": "Volver a la Caja",
        "invoice_lbl": "Factura",
        "product_name_lbl": "Producto",
        "barcode_lbl": "Código de Barras",
        "quantity_lbl": "Cantidad",
        "final_total": "Total Final"
    },
    "de": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "E-Mail",
        "back_to_cashier": "Zurück zur Kasse",
        "invoice_lbl": "Rechnung",
        "product_name_lbl": "Produkt",
        "barcode_lbl": "Barcode",
        "quantity_lbl": "Menge",
        "final_total": "Endbetrag"
    },
    "tr": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "E-posta",
        "back_to_cashier": "Kasiyere Dön",
        "invoice_lbl": "Fatura",
        "product_name_lbl": "Ürün",
        "barcode_lbl": "Barkod",
        "quantity_lbl": "Miktar",
        "final_total": "Genel Toplam"
    },
    "ru": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "Эл. почта",
        "back_to_cashier": "Вернуться в кассу",
        "invoice_lbl": "Счетфактура",
        "product_name_lbl": "Товар",
        "barcode_lbl": "Штрихкод",
        "quantity_lbl": "Количество",
        "final_total": "Итоговая Сумма"
    },
    "pt": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "E-mail",
        "back_to_cashier": "Voltar ao Caixa",
        "invoice_lbl": "Fatura",
        "product_name_lbl": "Produto",
        "barcode_lbl": "Código de Barras",
        "quantity_lbl": "Quantidade",
        "final_total": "Total Final"
    },
    "pt-BR": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "E-mail",
        "back_to_cashier": "Voltar ao Caixa",
        "invoice_lbl": "Fatura",
        "product_name_lbl": "Produto",
        "barcode_lbl": "Código de Barras",
        "quantity_lbl": "Quantidade",
        "final_total": "Total Final"
    },
    "hr": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "E-mail",
        "back_to_cashier": "Povratak na Blagajnu",
        "invoice_lbl": "Račun",
        "product_name_lbl": "Proizvod",
        "barcode_lbl": "Barkod",
        "quantity_lbl": "Količina",
        "final_total": "Konačni Ukupni Iznos"
    },
    "ja": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "Eメール",
        "back_to_cashier": "レジに戻る",
        "invoice_lbl": "請求書",
        "product_name_lbl": "製品",
        "barcode_lbl": "バーコード",
        "quantity_lbl": "数量",
        "final_total": "最終合計"
    },
    "zh": {
        "whatsapp_btn": "WhatsApp",
        "email_btn": "电子邮件",
        "back_to_cashier": "返回收银台",
        "invoice_lbl": "发票",
        "product_name_lbl": "产品",
        "barcode_lbl": "条形码",
        "quantity_lbl": "数量",
        "final_total": "最终总计"
    }
}

for lang, mapping in missing_keys.items():
    filepath = f"resources/lang/{lang}.json"
    if os.path.exists(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        for k, v in mapping.items():
            data[k] = v
            
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)
        print(f"Updated missing keys for {lang}.json")

print("Done syncing missing keys!")
