import os

def hex_debug(file_path, search_str):
    if not os.path.exists(file_path):
        return "File not found"
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
        if search_str in content:
            print(f"Found '{search_str}' in {file_path}")
            # Get 10 chars before and after
            idx = content.find(search_str)
            snippet = content[max(0, idx-10):idx+len(search_str)+10]
            print(f"Snippet: {repr(snippet)}")
            print(f"Hex: {' '.join(hex(ord(c)) for c in search_str)}")
        else:
            print(f"NOT Found '{search_str}' in {file_path}")
            # Show first 100 bytes of file
            print(f"Start of file: {repr(content[:50])}")

search_term = "إدارة المنتجات"
print(f"Searching for: {search_term}")
print(f"Hex of search term: {' '.join(hex(ord(c)) for c in search_term)}")

hex_debug(r'c:\xampp\htdocs\system\resources\views\store_owner\products\index.blade.php', search_term)
hex_debug(r'c:\xampp\htdocs\system\resources\lang\fr.json', search_term)
