import os
import re

def localize_file(file_path):
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        return
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Mapping of Arabic strings to localized versions
    # We use regex to find the strings and wrap them in {{ __('...') }}
    # We need to be careful not to wrap something already wrapped.
    
    replacements = [
        ("إضافة منتج جديد", "إضافة منتج جديد"),
        ("منتج فعال", "منتج فعال"),
        ("البيانات الأساسية", "البيانات الأساسية"),
        ("اسم المنتج \(عربي\)", "اسم المنتج (عربي)"),
        ("مثال: شيبس ليز ملح", "مثال: شيبس ليز ملح"),
        ("بحث في جوجل صور", "بحث في جوجل صور"),
        ("اسم المنتج \(إنجليزي\)", "اسم المنتج (إنجليزي)"),
        ("Ex: Lays Chips Salt", "Ex: Lays Chips Salt"),
        ("التصنيف", "التصنيف"),
        ("-- اختر تصنيف --", "-- اختر تصنيف --"),
        ("الوصف", "الوصف"),
        ("الوحدة الأساسية \(أصغر وحدة\)", "الوحدة الأساسية (أصغر وحدة)"),
        ("صورة الوحدة", "صورة الوحدة"),
        ("اسم الوحدة", "اسم الوحدة"),
        ("قطعة", "قطعة"),
        ("كيلو", "كيلو"),
        ("علبة", "علبة"),
        ("مخصص\.\.", "مخصص.."),
        ("سعر الشراء \(للعبوة\)", "سعر الشراء (للعبوة)"),
        ("عدد القطع بالعبوة", "عدد القطع بالعبوة"),
        ("باركود الوحدة", "باركود الوحدة"),
        ("تلقائي إذا فارغ", "تلقائي إذا فارغ"),
        ("شراء", "شراء"),
        ("بيع", "بيع"),
        ("سعر البيع", "سعر البيع"),
        ("التكلفة \(للقطعة\)", "التكلفة (للقطعة)"),
        ("الربح %", "الربح %"),
        ("الضريبة المضافة", "الضريبة المضافة"),
        ("السعر مع الضريبة", "السعر مع الضريبة"),
        ("الوحدات الإضافية \(كرتون، درزن\.\.\.\)", "الوحدات الإضافية (كرتون، درزن...)"),
        ("إضافة وحدة", "إضافة وحدة"),
        ("حد التنبيه للمخزون", "حد التنبيه للمخزون"),
        ("حفظ المنتج", "حفظ المنتج"),
        ("كرتون", "كرتون"),
        ("درزن", "درزن"),
        ("شريط", "شريط"),
        ("اكتب الاسم", "اكتب الاسم"),
        ("التحويل", "التحويل"),
        ("الباركود", "الباركود"),
        ("التكلفة \(آلي\)", "التكلفة (آلي)"),
        ("الرجاء كتابة اسم المنتج بالعربي أولاً", "الرجاء كتابة اسم المنتج بالعربي أولاً"),
        ("نبهني قبل X يوم", "نبهني قبل X يوم"),
        ("تعديل المنتج:", "تعديل المنتج:"),
        ("فاتورة شراء جديدة", "فاتورة شراء جديدة"),
        ("العودة", "العودة"),
        ("المورد", "المورد"),
        ("مورد جديد", "مورد جديد"),
        ("ابحث عن مورد\.\.\.", "ابحث عن مورد..."),
        ("تاريخ وتوقيت الفاتورة", "تاريخ وتوقيت الفاتورة"),
        ("رقم الفاتورة \(للمورد\)", "رقم الفاتورة (للمورد)"),
        ("مثال: INV-1001", "مثال: INV-1001"),
        ("قم بالبحث لإضافة منتجات", "قم بالبحث لإضافة منتجات"),
        ("ملخص الدفع", "ملخص الدفع"),
        ("المجموع الفرعي:", "المجموع الفرعي:"),
        ("خصم إضافي", "خصم إضافي"),
        ("الصافي النهائي:", "الصافي النهائي:"),
        ("المدفوعات", "المدفوعات"),
        ("💰 نقدي", "💰 نقدي"),
        ("💳 بطاقة", "💳 بطاقة"),
        ("🏦 تحويل", "🏦 تحويل"),
        ("المتبقي:", "المتبقي:"),
        ("رصيد \(لك\):", "رصيد (لك):"),
        ("متبقي \(عليك\):", "متبقي (عليك):"),
        ("خالص \(تم الدفع بالكامل\)", "خالص (تم الدفع بالكامل)"),
        ("حفظ الفاتورة", "حفظ الفاتورة"),
        ("إضافة مورد جديد", "إضافة مورد جديد"),
        ("الاسم \*", "الاسم *"),
        ("الشركة", "الشركة"),
        ("حفظ وإضافة", "حفظ وإضافة"),
        ("إضافة منتج سريع", "إضافة منتج سريع"),
        ("قريباً\.\.\.", "قريباً..."),
        ("الوحدات الإضافية", "الوحدات الإضافية"),
        ("أحجام/وحدات تقديم", "أحجام/وحدات تقديم"),
        ("أصغر وحدة", "أصغر وحدة"),
        ("للمورد", "للمورد"),
        ("خالص", "خالص")
    ]
    
    new_content = content
    
    for pattern, std_key in replacements:
        # Match outside of existing __() or skip if already localized
        # We replace text in tags, placeholders, and JS strings
        
        # Header/Text in tags: <h5>...</h5>
        new_content = re.sub(f">\\s*{pattern}\\s*<", f"> {{{{ __('{std_key}') }}}} <", new_content)
        
        # Labels: <label>...</label>
        new_content = re.sub(f"<label([^>]*)>\\s*{pattern}\\s*</label>", f"<label\\1> {{{{ __('{std_key}') }}}} </label>", new_content)
        
        # Placeholders: placeholder="..."
        new_content = re.sub(f"placeholder=[\"']{pattern}[\"']", f"placeholder=\"{{{{ __('{std_key}') }}}}\"", new_content)
        
        # Options: <option>...</option>
        new_content = re.sub(f"<option([^>]*)>\\s*{pattern}\\s*</option>", f"<option\\1> {{{{ __('{std_key}') }}}} </option>", new_content)
        
        # Buttons/I tags text
        new_content = re.sub(f"</i>\\s*{pattern}\\s*</", f"</i> {{{{ __('{std_key}') }}}} </", new_content)
        new_content = re.sub(f"</i>\\s*{pattern}\\s*<", f"</i> {{{{ __('{std_key}') }}}} <", new_content)
        
        # JS alerts/messages
        new_content = re.sub(f"alert\\([\"']{pattern}[\"']\\)", f"alert(\"{{{{ __('{std_key}') }}}}\")", new_content)
        new_content = re.sub(f"confirm\\([\"']{pattern}[\"']\\)", f"confirm(\"{{{{ __('{std_key}') }}}}\")", new_content)
        
        # Titles
        new_content = re.sub(f"title=[\"']{pattern}[\"']", f"title=\"{{{{ __('{std_key}') }}}}\"", new_content)

        # Spans
        new_content = re.sub(f"<span([^>]*)>\\s*{pattern}\\s*</span>", f"<span\\1>{{{{ __('{std_key}') }}}}</span>", new_content)

    # Some manual cleanup for cases where tags are nested
    # Especially for "إضافة منتج جديد" which might be with <i> tag
    
    if new_content != content:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Localized {file_path}")
    else:
        print(f"No changes in {file_path}")

if __name__ == "__main__":
    files = [
        r'c:\xampp\htdocs\system\resources\views\store_owner\products\create.blade.php',
        r'c:\xampp\htdocs\system\resources\views\store_owner\products\edit.blade.php',
        r'c:\xampp\htdocs\system\resources\views\store_owner\purchases\create.blade.php'
    ]
    for f in files:
        localize_file(f)
