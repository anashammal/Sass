import json
import os

translations = {
    "en": {
        "اختيار ملف": "Choose File",
        "لم يتم اختيار ملف": "No file chosen"
    },
    "fr": {
        "اختيار ملف": "Choisir un fichier",
        "لم يتم اختيار ملف": "Aucun fichier choisi"
    },
    "tr": {
        "اختيار ملف": "Dosya Seç",
        "لم يتم اختيار ملف": "Dosya seçilmedi"
    },
    "de": {
        "اختيار ملف": "Datei auswählen",
        "لم يتم اختيار ملف": "Keine Datei ausgewählt"
    },
    "ru": {
        "اختيار ملف": "Выбрать файл",
        "لم يتم اختيار ملف": "Файл не выбран"
    },
    "es": {
        "اختيار ملف": "Seleccionar archivo",
        "لم يتم اختيار ملف": "No se ha seleccionado ningún archivo"
    },
    "pt": {
        "اختيار ملف": "Escolher arquivo",
        "لم يتم اختيار ملف": "Nenhum arquivo selecionado"
    },
    "pt-BR": {
        "اختيار ملف": "Escolher arquivo",
        "لم يتم اختيار ملف": "Nenhum arquivo selecionado"
    },
    "hr": {
        "اختيار ملف": "Odaberi datoteku",
        "لم يتم اختيار ملف": "Datoteka nije odabrana"
    },
    "ja": {
        "اختيار ملف": "ファイルを選択",
        "لم يتم اختيار ملف": "ファイルが選択されていません"
    },
    "zh": {
        "اختيار ملف": "选择文件",
        "لم يتم اختيار ملف": "未选择任何文件"
    }
}

lang_dir = r'c:\xampp\htdocs\system\resources\lang'

for lang, mapping in translations.items():
    file_path = os.path.join(lang_dir, lang + ".json")
    if os.path.exists(file_path):
        with open(file_path, 'r', encoding='utf-8') as f:
            try:
                data = json.load(f)
            except:
                data = {}
        
        updated = False
        for ar_key, trans_val in mapping.items():
            data[ar_key] = trans_val
            updated = True
            
        if updated:
            with open(file_path, 'w', encoding='utf-8') as f:
                json.dump(data, f, ensure_ascii=False, indent=4)
            print(f"Updated {lang}.json with file upload translations.")
