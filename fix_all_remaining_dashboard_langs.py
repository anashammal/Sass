import json
import os

lang_dir = r'c:\xampp\htdocs\system\resources\lang'

# Remaining dashboard/modal keys
t = {
    "باقي:": { "en": "Remaining:", "tr": "Kalan:", "es": "Restante:", "ja": "残り:", "zh": "剩余：", "fr": "Reste :", "de": "Restlich:", "ru": "Остаток:", "pt": "Restante:", "hr": "Preostalo:" },
    "حد التنبيه:": { "en": "Alert Limit:", "tr": "Uyarı Limiti:", "es": "Límite de Alerta:", "ja": "アラート制限:", "zh": "警报限制：", "fr": "Limite d'alerte :", "de": "Warnlimit:", "ru": "Лимит оповещения:", "pt": "Limite de alerta:", "hr": "Granica upozorenja:" },
    "إجمالي المدفوعات": { "en": "Total Payments", "tr": "Toplam Ödemeler", "es": "Total de Pagos", "ja": "支払い合計", "zh": "总支付额", "fr": "Total des paiements", "de": "Gesamtzahlungen", "ru": "Всего платежей", "pt": "Total de pagamentos", "hr": "Ukupno plaćanja" },
    "كشف حساب مورد": { "en": "Supplier Statement", "tr": "Tedarikçi Ekstresi", "es": "Estado de Cuenta del Proveedor", "ja": "仕入先計算書", "zh": "供应商账目表", "fr": "Relevé fournisseur", "de": "Lieferantenkontoauszug", "ru": "Выписка поставщика", "pt": "Extrato de fornecedor", "hr": "Izvod dobavljača" },
    "باركود الهاتف": { "en": "Phone Barcode", "tr": "Telefon Barkodu", "es": "Código de Barras del Teléfono", "ja": "電話バーコード", "zh": "电话条形码", "fr": "Code-barres du téléphone", "de": "Telefon-Barcode", "ru": "Штрих-код телефона", "pt": "Código de barras do telefone", "hr": "Barkod telefona" },
    "له رصيد / دائن": { "en": "Has Balance / Creditor", "tr": "Bakiyesi Var / Alacaklı", "es": "Tiene Saldo / Acreedor", "ja": "残高あり / 債権者", "zh": "有余额 / 债权人", "fr": "A un solde / Créancier", "de": "Hat Guthaben / Gläubiger", "ru": "Имеет баланс / Кредитор", "pt": "Tem saldo / Credor", "hr": "Ima saldo / Vjerovnik" },
    "عليه دين / مدين": { "en": "Has Debt / Debtor", "tr": "Borcu Var / Borçlu", "es": "Tiene Deuda / Deudor", "ja": "負債あり / 債務者", "zh": "有欠款 / 债务人", "fr": "A une dette / Débiteur", "de": "Hat Schulden / Schuldner", "ru": "Имеет долг / Дебитор", "pt": "Tem dívida / Devedor", "hr": "Ima dug / Dužnik" },
    "إضافة مصروف": { "en": "Add Expense", "tr": "Gider Ekle", "es": "Añadir Gasto", "ja": "経費を追加", "zh": "添加费用", "fr": "Ajouter une dépense", "de": "Ausgabe hinzufügen", "ru": "Добавить расход", "pt": "Adicionar despesa", "hr": "Dodaj trošak" },
    "مصاريف": { "en": "Expenses", "tr": "Giderler", "es": "Gastos", "ja": "経費", "zh": "费用", "fr": "Dépenses", "de": "Ausgaben", "ru": "Расходы", "pt": "Despesas", "hr": "Troškovi" },
    "إسم المنتج": { "en": "Product Name", "tr": "Ürün Adı", "es": "Nombre del Producto", "ja": "製品名", "zh": "产品名称", "fr": "Nom du produit", "de": "Produktname", "ru": "Название продукта", "pt": "Nome do produto", "hr": "Naziv proizvoda" },
    "الربح (%)": { "en": "Profit (%)", "tr": "Kar (%)", "es": "Ganancia (%)", "ja": "利益 (%)", "zh": "利润 (%)", "fr": "Bénéfice (%)", "de": "Gewinn (%)", "ru": "Прибыль (%)", "pt": "Lucro (%)", "hr": "Dobit (%)" },
    "إجمالي قائمة الطعام (المنيو)": { "en": "Total Menu Items", "tr": "Toplam Menü Öğeleri", "es": "Total de Elementos del Menú", "ja": "メニュー項目合計", "zh": "菜单项总数", "fr": "Total des articles du menu", "de": "Menüpunkte insgesamt", "ru": "Всего пунктов меню", "pt": "Total de itens do menu", "hr": "Ukupno stavki jelovnika" },
    "عرض كافة المنتجات": { "en": "View All Products", "tr": "Tüm Ürünleri Görüntüle", "es": "Ver Todos los Productos", "ja": "すべての製品を表示", "zh": "查看所有产品", "fr": "Voir tous les produits", "de": "Alle Produkte anzeigen", "ru": "Посмотреть все продукты", "pt": "Ver todos os produtos", "hr": "Vidi sve proizvode" },
    "إجمالي المدفوع": { "en": "Total Paid", "tr": "Toplam Ödenen", "es": "Total Pagado", "ja": "支払い合計", "zh": "已付总额", "fr": "Total payé", "de": "Gesamt bezahlt", "ru": "Всего оплачено", "pt": "Total pago", "hr": "Ukupno plaćeno" },
    "المتبقي (الأجل)": { "en": "Remaining (Credit)", "tr": "Kalan (Vadeli)", "es": "Restante (Crédito)", "ja": "残り (クレジット)", "zh": "剩余 (信用)", "fr": "Reste (Crédit)", "de": "Restbetrag (Kredit)", "ru": "Остаток (Кредит)", "pt": "Restante (Crédito)", "hr": "Preostalo (Kredit)" }
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
