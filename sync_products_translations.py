import os
import json
import re

def extract_translation_keys(file_paths):
    keys = set()
    for file_path in file_paths:
        if not os.path.exists(file_path):
            print(f"File not found: {file_path}")
            continue
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
            # Regex for {{ __('...') }} or __("...")
            matches = re.findall(r'__\([\'"](.+?)[\'"]\)', content)
            for match in matches:
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
                # If key doesn't exist OR if it exists but the value is equal to the Arabic key (meaning it's not translated yet)
                if key not in data or data[key] == key:
                    data[key] = key
                    added_count += 1
            
            if added_count > 0:
                with open(file_path, 'w', encoding='utf-8') as f:
                    json.dump(data, f, ensure_ascii=False, indent=4)
                print(f"Updated {filename}: added/reset {added_count} keys.")

if __name__ == "__main__":
    files_to_scan = [
        r'c:\xampp\htdocs\system\resources\views\store_owner\products\index.blade.php',
        r'c:\xampp\htdocs\system\resources\views\store_owner\products\partials\table_rows.blade.php'
    ]
    lang_dir = r'c:\xampp\htdocs\system\resources\lang'
    
    # Extra keys that might be missed or are in JS/dynamic
    extra_keys = [
        "إدارة المنتجات", "إضافة منتج جديد", "إجمالي المنتجات", "مخزون منخفض", "نافذ (0 كمية)",
        "التصنيف", "اختر التصنيفات", "كل الحالات", "منتجات فعالة", "منتجات معطلة", "الأعمدة", "بحث...",
        "صورة", "المنتج", "الباركود", "الوحدة", "التكلفة", "سعر البيع", "الربح", "المخزون", "الحالة", "إجراءات",
        "أحجام/وحدات تقديم", "وحدات إضافية", "شامل", "غير قابل للبيع", "فعال", "معطل", "حذف؟",
        "تفاصيل الوحدات الإضافية", "التحويل", "سعر البيع (شامل الضريبة)", "المخزون المتوفر", "لا توجد بيانات"
    ]
    
    extracted_keys = extract_translation_keys(files_to_scan)
    all_keys = extracted_keys.union(set(extra_keys))
    
    print(f"Extracted {len(extracted_keys)} unique keys from scanned files.")
    update_json_files(lang_dir, all_keys)
