import json
import os

lang_dir = r'c:\xampp\htdocs\system\resources\lang'

t = {
    "إجمالي قائمة الطعام (المنيو)": {
        "en": "Total Menu Items", "tr": "Toplam Menü Öğeleri", "es": "Total de Elementos del Menú",
        "ja": "メニュー項目合計", "zh": "菜单项总数", "fr": "Total des Éléments du Menu",
        "de": "Menüpunkte insgesamt", "ru": "Всего пунктов меню", "pt": "Total de Itens do Menu",
        "hr": "Ukupno stavki jelovnika"
    }
}

for f in os.listdir(lang_dir):
    if not f.endswith('.json'): continue
    file_path = os.path.join(lang_dir, f)
    lang_code = f.split('.')[0]
    
    with open(file_path, 'r', encoding='utf-8') as file:
        try: data = json.load(file)
        except: data = {}
        
    updated = False
    for k, trans in t.items():
        if lang_code in trans:
            data[k] = trans[lang_code]
            updated = True
        elif lang_code == 'pt-BR' and 'pt' in trans:
            data[k] = trans['pt']
            updated = True
            
    if updated:
        with open(file_path, 'w', encoding='utf-8') as file:
            json.dump(data, file, ensure_ascii=False, indent=4)
        print(f"Updated {f}")
