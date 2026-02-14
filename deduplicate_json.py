import json

file_path = r'c:\xampp\htdocs\system\resources\lang\en.json'

with open(file_path, 'r', encoding='utf-8') as f:
    data = json.load(f)

# json.load naturally handles duplicate keys by taking the last one, 
# but writing it back will give us a clean version.
with open(file_path, 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False, indent=4)

print("De-duplicated successfully.")
