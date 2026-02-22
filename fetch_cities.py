import urllib.request
import json
import os

print('Fetching countries and cities...')
req = urllib.request.Request('https://countriesnow.space/api/v0.1/countries', headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as res:
        data = json.loads(res.read())['data']
        output_path = 'public/js/countries_cities.json'
        os.makedirs(os.path.dirname(output_path), exist_ok=True)
        with open(output_path, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False)
        print(f'Successfully saved {len(data)} countries to {output_path}')
except Exception as e:
    print(f'Error: {e}')
