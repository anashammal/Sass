import json
import os

lang_dir = r'c:\xampp\htdocs\system\resources\lang'

# Targeted translations for dashboard elements
t = {
    "نواقص المخزون": {
        "en": "Stock Shortages", "tr": "Stok Eksikliği", "es": "Faltantes de Inventario",
        "ja": "在庫不足", "zh": "库存短缺", "fr": "Ruptures de stock",
        "de": "Lagerbestände knapp", "ru": "Дефицит запасов", "pt": "Faltas de Stock",
        "hr": "Manjak zaliha"
    },
    "خامات ومواد ناقصة": {
        "en": "Shortage of Materials", "tr": "Malzeme Eksikliği", "es": "Escasez de Materiales",
        "ja": "材料不足", "zh": "原材料短缺", "fr": "Pénurie de matières",
        "de": "Materialknappheit", "ru": "Нехватка материалов", "pt": "Escassez de materiais",
        "hr": "Nestašica materijala"
    },
    "منتجات منتهية/قريبة": {
        "en": "Expired/Near Expiry", "tr": "Süresi Dolan/Yakın Ürünler", "es": "Productos Caducados/Próximos",
        "ja": "期限切れ/期限間近の製品", "zh": "已过期/即将过期产品", "fr": "Produits expirés/proches",
        "de": "Abgelaufene/Nahe Produkte", "ru": "Истекшие/Близкие продукты", "pt": "Produtos expirados/próximos",
        "hr": "Istekli/Bliski proizvodi"
    },
    "تنبيهات الصلاحية (الخامات)": {
        "en": "Expiry Alerts (Materials)", "tr": "Son Kullanma Tarihi Uyarıları (Malzemeler)", "es": "Alertas de Vencimiento (Materiales)",
        "ja": "有効期限アラート (材料)", "zh": "到期警报 (原材料)", "fr": "Alertes d'expiration (Matières)",
        "de": "Verfallsalarme (Materialien)", "ru": "Оповещения о сроке (Материалы)", "pt": "Alertas de validade (Materiais)",
        "hr": "Upozorenja o roku trajanja (Materijali)"
    },
    "لوحة تحكم المتجر:": {
        "en": "Store Dashboard:", "tr": "Mağaza Paneli:", "es": "Panel de la Tienda:",
        "ja": "ストアダッシュボード:", "zh": "商店仪表板：", "fr": "Tableau de bord :",
        "de": "Shop-Dashboard:", "ru": "Панель управления магазином:", "pt": "Painel da loja:",
        "hr": "Nadzorna ploča trgovine:"
    },
    "أهلاً بك في متجرك!": {
        "en": "Welcome to your store!", "tr": "Mağazanınıza hoş geldiniz!", "es": "¡Bienvenido a tu tienda!",
        "ja": "あなたのストアへようこそ！", "zh": "欢迎来到您的商店！", "fr": "Bienvenue dans votre magasin !",
        "de": "Willkommen in Ihrem Shop!", "ru": "Добро пожаловать в ваш магазин!", "pt": "Bem-vindo à sua loja!",
        "hr": "Dobrodošli u svoju trgovinu!"
    },
    "هذه الصفحة خاصة بالدومين الفرعي:": {
        "en": "This page is specific to the subdomain:", "tr": "Bu sayfa alt alan adına özeldir:", "es": "Esta página es específica para el subdominio:",
        "ja": "このページはサブドメイン専用です:", "zh": "此页面特定于子域：", "fr": "Cette page est spécifique au sous-domaine :",
        "de": "Diese Seite ist spezifisch für die Subdomain:", "ru": "Эта страница предназначена для поддомена:", "pt": "Esta página é específica para o subdominio:",
        "hr": "Ova stranica je specifična za poddomenu:"
    },
    "نقطة البيع (POS)": {
        "en": "Point of Sale (POS)", "tr": "Satış Noktası (POS)", "es": "Punto de Venta (POS)",
        "ja": "販売時点管理 (POS)", "zh": "销售点 (POS)", "fr": "Point de vente (POS)",
        "de": "Verkaufsstelle (POS)", "ru": "Точка продажи (POS)", "pt": "Ponto de venda (POS)",
        "hr": "Prodajno mjesto (POS)"
    },
    "نقطة البيع (الكاشير)": {
        "en": "Point of Sale (Cashier)", "tr": "Satış Noktası (Kasiyer)", "es": "Punto de Venta (Cajero)",
        "ja": "販売時点管理 (レジ)", "zh": "销售点 (收银员)", "fr": "Point de vente (Caisse)",
        "de": "Verkaufsstelle (Kasse)", "ru": "Точка продажи (Касса)", "pt": "Ponto de venda (Caixa)",
        "hr": "Prodajno mjesto (Blagajna)"
    },
    "إدارة المنيو والوجبات": {
        "en": "Manage Menu & Meals", "tr": "Menü ve Yemek Yönetimi", "es": "Gestión de Menú y Comidas",
        "ja": "メニューと食事の管理", "zh": "菜单和餐饮管理", "fr": "Gérer le menu et les repas",
        "de": "Menü & Mahlzeiten verwalten", "ru": "Управление меню и блюдами", "pt": "Gerir menu e refeições",
        "hr": "Upravljanje jelovnikom i jelima"
    },
    "إدارة المنتجات": {
        "en": "Product Management", "tr": "Ürün Yönetimi", "es": "Gestión de Productos",
        "ja": "製品管理", "zh": "产品管理", "fr": "Gestion des produits",
        "de": "Produktmanagement", "ru": "Управление продуктами", "pt": "Gestão de produtos",
        "hr": "Upravljanje proizvodima"
    },
    "تنبيهات هامة للمخزون والصلاحية": {
        "en": "Important Stock & Expiry Alerts", "tr": "Önemli Stok ve Süre Uyarıları", "es": "Alertas Importantes de Stock y Expiración",
        "ja": "重要な在庫および有効期限アラート", "zh": "重要库存和到期警报", "fr": "Alertes de stock et d'expiration importantes",
        "de": "Wichtige Bestands- und Verfallsalarme", "ru": "Важные оповещения о запасах и сроке годности", "pt": "Alertas importantes de stock e validade",
        "hr": "Važna upozorenja o zalihama i roku trajanja"
    },
    "ممتاز! وضع المخزون سليم.": {
        "en": "Excellent! Stock status is healthy.", "tr": "Mükemmel! Stok durumu sağlıklı.", "es": "¡Excelente! El estado del stock es saludable.",
        "ja": "素晴らしい！在庫状況は良好です。", "zh": "优秀！库存状态良好。", "fr": "Excellent ! L'état des stocks est sain.",
        "de": "Hervorragend! Der Lagerstatus ist gesund.", "ru": "Отлично! Состояние запасов в норме.", "pt": "Excelente! O estado do stock está saudável.",
        "hr": "Odlično! Stanje zaliha je zdravo."
    },
    "فهمت، إغلاق": {
        "en": "I understand, Close", "tr": "Anladım, Kapat", "es": "Entiendo, Cerrar",
        "ja": "了解、閉じる", "zh": "明白，关闭", "fr": "Compris, fermer",
        "de": "Verstanden, Schließen", "ru": "Понятно, закрыть", "pt": "Entendido, fechar",
        "hr": "Razumijem, zatvori"
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
