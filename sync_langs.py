import json
import os

base_lang_path = r'c:\xampp\htdocs\system\resources\lang\en.json'
lang_dir = r'c:\xampp\htdocs\system\resources\lang'

with open(base_lang_path, 'r', encoding='utf-8') as f:
    base_data = json.load(f)

for filename in os.listdir(lang_dir):
    if filename.endswith('.json') and filename != 'en.json':
        file_path = os.path.join(lang_dir, filename)
        with open(file_path, 'r', encoding='utf-8') as f:
            try:
                lang_data = json.load(f)
            except:
                lang_data = {}

        # Add missing keys
        updated = False
        for key in base_data:
            if key not in lang_data:
                lang_data[key] = key # Fallback to key itself
                updated = True
        
        if updated:
            with open(file_path, 'w', encoding='utf-8') as f:
                json.dump(lang_data, f, ensure_ascii=False, indent=4)
            print(f"Updated {filename}")

print("Sync completed.")
