import json
import os

langs = {
    'ar': ':h ساعة و :m دقيقة',
    'en': ':h h :m m',
    'fr': ':h h :m m',
    'tr': ':h sa :m dk',
    'de': ':h Std :m Min',
    'es': ':h h :m m',
    'ru': ':h ч :m мин',
    'pt': ':h h :m m',
    'pt-BR': ':h h :m m',
    'hr': ':h h :m m',
    'ja': ':h 時間 :m 分',
    'zh': ':h 小时 :m 分钟'
}

for lang, val in langs.items():
    filepath = f'resources/lang/{lang}.json'
    if os.path.exists(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            data = json.load(f)
        data['shift_duration_format'] = val
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=4)
        print(f'Updated {lang}')
