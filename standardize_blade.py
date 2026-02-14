import os
import re

def standardize_blade(file_path):
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        return
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Mapping of potential variations to standardized keys
    # This is to catch cases where there might be different Alifs or spaces
    replacements = {
        r"إدارة المنتجات": "إدارة المنتجات",
        r"إضافة منتج جديد": "إضافة منتج جديد",
        r"إجمالي المنتجات": "إجمالي المنتجات",
        r"مخزون منخفض": "مخزون منخفض",
        r"نافذ\s*\(0\s*كمية\)": "نافذ (0 كمية)",
        r"اختر التصنيفات": "اختر التصنيفات",
        r"كل الحالات": "كل الحالات",
        r"منتجات فعالة": "منتجات فعالة",
        r"منتجات معطلة": "منتجات معطلة",
        r"الأعمدة": "الأعمدة",
        r"بحث\.\.\.": "بحث...",
        r"صورة": "صورة",
        r"المنتج": "المنتج",
        r"الباركود": "الباركود",
        r"الوحدة": "الوحدة",
        r"التكلفة": "التكلفة",
        r"سعر البيع": "سعر البيع",
        r"الربح": "الربح",
        r"المخزون": "المخزون",
        r"الحالة": "الحالة",
        r"إجراءات": "إجراءات",
        r"أحجام/وحدات تقديم": "أحجام/وحدات تقديم",
        r"وحدات إضافية": "وحدات إضافية",
        r"غير قابل للبيع": "غير قابل للبيع",
        r"فعال": "فعال",
        r"معطل": "معطل",
        r"حذف؟": "حذف؟",
        r"تفاصيل الوحدات الإضافية": "تفاصيل الوحدات الإضافية",
        r"التحويل": "التحويل",
        r"سعر البيع\s*\(شامل الضريبة\)": "سعر البيع (شامل الضريبة)",
        r"المخزون المتوفر": "المخزون المتوفر",
        r"لا توجد بيانات": "لا توجد بيانات",
        r"شامل": "شامل",
        r"التصنيف": "التصنيف"
    }
    
    new_content = content
    for pattern, std_key in replacements.items():
        # Replace occurrences inside {{ __('...') }}
        # We use regex to match the pattern flexibly but replace with the exact std_key
        # We need to handle both single and double quotes
        new_content = re.sub(f"__\\(['\"]{pattern}['\"]\\)", f"__('{std_key}')", new_content)
        
    if new_content != content:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Standardized keys in {file_path}")
    else:
        print(f"No changes needed in {file_path}")

if __name__ == "__main__":
    files = [
        r'c:\xampp\htdocs\system\resources\views\store_owner\products\index.blade.php',
        r'c:\xampp\htdocs\system\resources\views\store_owner\products\partials\table_rows.blade.php'
    ]
    for f in files:
        standardize_blade(f)
