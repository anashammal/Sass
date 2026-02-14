import os
import json
import re

def extract_translation_keys(file_path):
    keys = set()
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        # Regex for {{ __('...') }} or __("...")
        matches = re.findall(r'__\([\'"](.+?)[\'"]\)', content)
        for match in matches:
            # Handle potential nested quotes if any, but the regex is simple for now
            keys.add(match)
    return keys

def update_json_files(lang_dir, new_keys):
    for filename in os.listdir(lang_dir):
        if filename.endswith('.json'):
            file_path = os.path.join(lang_dir, filename)
            with open(file_path, 'r', encoding='utf-8') as f:
                try:
                    data = json.load(f)
                except json.JSONDecodeError:
                    data = {}
            
            added_count = 0
            for key in new_keys:
                if key not in data:
                    # For en.json, we might want to provide English translations
                    # For others, we might leave it as is (key=key) or placeholders
                    if filename == 'en.json':
                        # Placeholder for manual/automated translation later
                        data[key] = key 
                    else:
                        data[key] = key
                    added_count += 1
            
            if added_count > 0:
                with open(file_path, 'w', encoding='utf-8') as f:
                    json.dump(data, f, ensure_ascii=False, indent=4)
                print(f"Updated {filename}: added {added_count} keys.")

if __name__ == "__main__":
    pos_file = r'c:\xampp\htdocs\system\resources\views\store_owner\pos\index.blade.php'
    lang_dir = r'c:\xampp\htdocs\system\resources\lang'
    
    # Also include the dashboard keys mentioned in task.md if not already there
    additional_keys = [
        "نواقص المخزون", "منتجات منتهية/قريبة", "لوحة تحكم المتجر:", "أهلاً بك في متجرك!", 
        "نقطة البيع (POS)", "(الكاشير)", "إجمالي قائمة الطعام (المنيو)", "باقي", "حد التنبيه", "المتبقي"
    ]
    
    extracted_keys = extract_translation_keys(pos_file)
    all_keys = extracted_keys.union(set(additional_keys))
    
    print(f"Extracted {len(extracted_keys)} keys from POS view.")
    update_json_files(lang_dir, all_keys)
