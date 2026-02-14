import json
import os

langs_to_fix = ['fr', 'de', 'pt', 'pt-BR', 'ru', 'hr']
lang_dir = r'c:\xampp\htdocs\system\resources\lang'

translations = {
    'fr': {
        "إجمالي المنتجات": "Total des produits",
        "إجمالي قائمة الطعام (المنيو)": "Total du menu",
        "نواقص المخزون": "Ruptures de stock",
        "خامات ومواد ناقصة": "Matières premières manquantes"
    },
    'de': {
        "إجمالي المنتجات": "Produkte insgesamt",
        "إجمالي قائمة الطعام (المنيو)": "Gesamtmenü",
        "نواقص المخزون": "Lagerengpässe",
        "خامات ومواد ناقصة": "Fehlende Rohstoffe"
    },
    'pt': {
        "إجمالي المنتجات": "Total de produtos",
        "إجمالي قائمة الطعام (المنيو)": "Total do menu",
        "نواقص المخزون": "Faltas de estoque",
        "خامات ومواد ناقصة": "Matérias-primas em falta"
    },
    'pt-BR': {
        "إجمالي المنتجات": "Total de produtos",
        "إجمالي قائمة الطعام (المنيو)": "Total do cardápio",
        "نواقص المخزون": "Produtos em falta",
        "خامات ومواد ناقصة": "Insumos em falta"
    },
    'ru': {
        "إجمالي المنتجات": "Всего товаров",
        "إجمالي قائمة الطعام (المنيو)": "Всего в меню",
        "نواقص المخزون": "Дефицит товаров",
        "خامات ومواد ناقصة": "Недостающие материалы"
    },
    'hr': {
        "إجمالي المنتجات": "Ukupno proizvoda",
        "إجمالي قائمة الطعام (المنيو)": "Ukupno na jelovniku",
        "نواقص المخزون": "Nedostaci zaliha",
        "خامات ومواد ناقصة": "Manjak sirovina"
    }
}

for lang in langs_to_fix:
    file_path = os.path.join(lang_dir, f'{lang}.json')
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        # Update or add missing keys
        for key, val in translations[lang].items():
            data[key] = val
        
        with open(file_path, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)
        print(f"Updated {lang}.json")
    else:
        print(f"File not found: {file_path}")
