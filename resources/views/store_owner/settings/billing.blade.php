@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-primary fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i> {{ __('المالية والفوترة') }}</h3>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm border-0">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('store.settings.update-billing', $store->id) }}">
        @csrf
        @method('PUT')

        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white text-danger fw-bold border-bottom">
                        <i class="fas fa-file-signature me-2"></i> {{ __('نظام الفوترة والضرائب') }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('الدولة التشغيلية') }}</label>
                            <select name="country_code" class="form-control">
                                <option value="SA" {{ $store->country_code == 'SA' ? 'selected' : '' }}>{{ __('السعودية 🇸🇦') }}</option>
                                <option value="TR" {{ $store->country_code == 'TR' ? 'selected' : '' }}>{{ __('تركيا 🇹🇷') }}</option>
                                <option value="EG" {{ $store->country_code == 'EG' ? 'selected' : '' }}>{{ __('مصر 🇪🇬') }}</option>
                                <option value="OTHER" {{ $store->country_code == 'OTHER' ? 'selected' : '' }}>{{ __('أخرى') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('نظام الفوترة') }}</label>
                            <select name="invoice_mode" class="form-control bg-light">
                                <option value="zatca_phase_1" {{ $store->invoice_mode == 'zatca_phase_1' ? 'selected' : '' }}>ZATCA (السعودية)</option>
                                <option value="turkey_kdv" {{ $store->invoice_mode == 'turkey_kdv' ? 'selected' : '' }}>KDV (تركيا)</option>
                                <option value="simple" {{ $store->invoice_mode == 'simple' ? 'selected' : '' }}>{{ __('ضريبة مبسطة') }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('نسب الضرائب المتاحة') }}</label>
                            <input type="text" name="tax_rates" class="form-control" 
                                   value="{{ old('tax_rates', $store->tax_rates ?? '0,15') }}" 
                                   placeholder="0,5,15">
                            <small class="text-muted d-block mt-1">{{ __('افصل بين النسب بفاصلة (,). مثال: 0,15') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white text-primary fw-bold border-bottom">
                        <i class="fas fa-university me-2"></i> {{ __('بيانات الحساب البنكي') }}
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('دولة البنك') }}</label>
                            <select name="bank_country" id="bank_country_select" class="form-select"></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('اسم البنك') }}</label>
                            <select name="iban_bank_name" id="bank_name_select" class="form-select">
                                <option value="">{{ __('اختر دولة البنك أولاً...') }}</option>
                            </select>
                            <input type="text" name="bank_name_manual" id="bank_name_manual" class="form-control d-none mt-2" placeholder="{{ __('اكتب اسم البنك يدوياً') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('اسم صاحب الحساب') }}</label>
                            <input type="text" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $store->bank_account_holder) }}" placeholder="{{ __('الاسم كما يظهر في البنك') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('رقم الآيبان (IBAN)') }}</label>
                            <div class="input-group" dir="ltr">
                                <span class="input-group-text fw-bold" id="iban_prefix" style="min-width: 50px; justify-content: center; background: #e9ecef; font-family: monospace; font-size: 16px;">--</span>
                                <input type="text" name="iban" id="iban_input" class="form-control fw-bold" 
                                       value="{{ old('iban', $store->iban) }}"
                                       placeholder="Enter IBAN number"
                                       dir="ltr"
                                       style="letter-spacing: 2px; font-size: 15px; font-family: monospace;">
                            </div>
                            <div class="form-text" id="iban_hint">{{ __('اختر دولة البنك لمعرفة صيغة الآيبان الصحيحة') }}</div>
                        </div>
                        <div class="mt-2">
                            <div class="alert alert-info py-2 px-3 small border-0 shadow-none mb-0">
                                <i class="fa fa-info-circle me-1"></i> {{ __('هذه البيانات تظهر للعملاء في الفواتير لتسهيل التحويل البنكي اليدوي.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow rounded-pill">
                    <i class="fa fa-save me-2"></i> {{ __('حفظ الإعدادات المالية') }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const COUNTRIES_GLOBAL = [
        { code: "af", ar: "أفغانستان", en: "Afghanistan" }, { code: "al", ar: "ألبانيا", en: "Albania" },
        { code: "dz", ar: "الجزائر", en: "Algeria" }, { code: "ad", ar: "أندورا", en: "Andorra" },
        { code: "ao", ar: "أنغولا", en: "Angola" }, { code: "ar", ar: "الأرجنتين", en: "Argentina" },
        { code: "am", ar: "أرمينيا", en: "Armenia" }, { code: "au", ar: "أستراليا", en: "Australia" },
        { code: "at", ar: "النمسا", en: "Austria" }, { code: "az", ar: "أذربيجان", en: "Azerbaijan" },
        { code: "bh", ar: "البحرين", en: "Bahrain" }, { code: "bd", ar: "بنغلاديش", en: "Bangladesh" },
        { code: "by", ar: "بيلاروسيا", en: "Belarus" }, { code: "be", ar: "بلجيكا", en: "Belgium" },
        { code: "bj", ar: "بنين", en: "Benin" }, { code: "bo", ar: "بوليفيا", en: "Bolivia" },
        { code: "ba", ar: "البوسنة والهرسك", en: "Bosnia And Herzegovina" }, { code: "bw", ar: "بوتسوانا", en: "Botswana" },
        { code: "br", ar: "البرازيل", en: "Brazil" }, { code: "bn", ar: "بروناي", en: "Brunei" },
        { code: "bg", ar: "بلغاريا", en: "Bulgaria" }, { code: "kh", ar: "كمبوديا", en: "Cambodia" },
        { code: "cm", ar: "الكاميرون", en: "Cameroon" }, { code: "ca", ar: "كندا", en: "Canada" },
        { code: "cl", ar: "تشيلي", en: "Chile" }, { code: "cn", ar: "الصين", en: "China" },
        { code: "co", ar: "كولومبيا", en: "Colombia" }, { code: "cr", ar: "كوستاريكا", en: "Costa Rica" },
        { code: "hr", ar: "كرواتيا", en: "Croatia" }, { code: "cu", ar: "كوبا", en: "Cuba" },
        { code: "cy", ar: "قبرص", en: "Cyprus" }, { code: "cz", ar: "التشيك", en: "Czech Republic" },
        { code: "dk", ar: "الدانمرك", en: "Denmark" }, { code: "dj", ar: "جيبوتي", en: "Djibouti" },
        { code: "do", ar: "جمهورية الدومينيكان", en: "Dominican Republic" }, { code: "ec", ar: "الإكوادور", en: "Ecuador" },
        { code: "eg", ar: "مصر", en: "Egypt" }, { code: "sv", ar: "السلفادور", en: "El Salvador" },
        { code: "ee", ar: "إستونيا", en: "Estonia" }, { code: "et", ar: "إثيوبيا", en: "Ethiopia" },
        { code: "fj", ar: "فيجي", en: "Fiji" }, { code: "fi", ar: "فنلندا", en: "Finland" },
        { code: "fr", ar: "فرنسا", en: "France" }, { code: "ge", ar: "جورجيا", en: "Georgia" },
        { code: "de", ar: "ألمانيا", en: "Germany" }, { code: "gh", ar: "غانا", en: "Ghana" },
        { code: "gr", ar: "اليونان", en: "Greece" }, { code: "gt", ar: "غواتيمالا", en: "Guatemala" },
        { code: "hn", ar: "هندوراس", en: "Honduras" }, { code: "hk", ar: "هونغ كونغ", en: "Hong Kong" },
        { code: "hu", ar: "المجر", en: "Hungary" }, { code: "is", ar: "آيسلندا", en: "Iceland" },
        { code: "in", ar: "الهند", en: "India" }, { code: "id", ar: "إندونيسيا", en: "Indonesia" },
        { code: "ir", ar: "إيران", en: "Iran" }, { code: "iq", ar: "العراق", en: "Iraq" },
        { code: "ie", ar: "أيرلندا", en: "Ireland" }, { code: "il", ar: "إسرائيل", en: "Israel" },
        { code: "it", ar: "إيطاليا", en: "Italy" }, { code: "jm", ar: "جامايكا", en: "Jamaica" },
        { code: "jp", ar: "اليابان", en: "Japan" }, { code: "jo", ar: "الأردن", en: "Jordan" },
        { code: "kz", ar: "كازاخستان", en: "Kazakhstan" }, { code: "ke", ar: "كينيا", en: "Kenya" },
        { code: "kw", ar: "الكويت", en: "Kuwait" }, { code: "kg", ar: "قيرغيزستان", en: "Kyrgyzstan" },
        { code: "la", ar: "لاوس", en: "Laos" }, { code: "lv", ar: "لاتفيا", en: "Latvia" },
        { code: "lb", ar: "لبنان", en: "Lebanon" }, { code: "ly", ar: "ليبيا", en: "Libya" },
        { code: "lt", ar: "ليتوانيا", en: "Lithuania" }, { code: "lu", ar: "لوكسمبورغ", en: "Luxembourg" },
        { code: "my", ar: "ماليزيا", en: "Malaysia" }, { code: "mv", ar: "المالديف", en: "Maldives" },
        { code: "mt", ar: "مالطا", en: "Malta" }, { code: "mx", ar: "المكسيك", en: "Mexico" },
        { code: "md", ar: "مولدوفا", en: "Moldova" }, { code: "mc", ar: "موناكو", en: "Monaco" },
        { code: "mn", ar: "منغوليا", en: "Mongolia" }, { code: "me", ar: "الجبل الأسود", en: "Montenegro" },
        { code: "ma", ar: "المغرب", en: "Morocco" }, { code: "mm", ar: "ميانمار", en: "Myanmar" },
        { code: "np", ar: "نيبال", en: "Nepal" }, { code: "nl", ar: "هولندا", en: "Netherlands" },
        { code: "nz", ar: "نيوزيلندا", en: "New Zealand" }, { code: "ni", ar: "نيكاراغوا", en: "Nicaragua" },
        { code: "ng", ar: "نيجيريا", en: "Nigeria" }, { code: "no", ar: "النرويج", en: "Norway" },
        { code: "om", ar: "عُمان", en: "Oman" }, { code: "pk", ar: "باكستان", en: "Pakistan" },
        { code: "ps", ar: "فلسطين", en: "Palestine" }, { code: "pa", ar: "بنما", en: "Panama" },
        { code: "py", ar: "باراغواي", en: "Paraguay" }, { code: "pe", ar: "بيرو", en: "Peru" },
        { code: "ph", ar: "الفلبين", en: "Philippines" }, { code: "pl", ar: "بولندا", en: "Poland" },
        { code: "pt", ar: "البرتغال", en: "Portugal" }, { code: "qa", ar: "قطر", en: "Qatar" },
        { code: "ro", ar: "رومانيا", en: "Romania" }, { code: "ru", ar: "روسيا", en: "Russia" },
        { code: "rw", ar: "رواندا", en: "Rwanda" }, { code: "sa", ar: "السعودية", en: "Saudi Arabia" },
        { code: "sn", ar: "السنغال", en: "Senegal" }, { code: "rs", ar: "صربيا", en: "Serbia" },
        { code: "sg", ar: "سنغافورة", en: "Singapore" }, { code: "sk", ar: "سلوفاكيا", en: "Slovakia" },
        { code: "si", ar: "سلوفينيا", en: "Slovenia" }, { code: "so", ar: "الصومال", en: "Somalia" },
        { code: "za", ar: "جنوب أفريقيا", en: "South Africa" }, { code: "kr", ar: "كوريا الجنوبية", en: "South Korea" },
        { code: "ss", ar: "جنوب السودان", en: "South Sudan" }, { code: "es", ar: "إسبانيا", en: "Spain" },
        { code: "lk", ar: "سريلانكا", en: "Sri Lanka" }, { code: "sd", ar: "السودان", en: "Sudan" },
        { code: "se", ar: "السويد", en: "Sweden" }, { code: "ch", ar: "سويسرا", en: "Switzerland" },
        { code: "sy", ar: "سوريا", en: "Syria" }, { code: "tw", ar: "تايوان", en: "Taiwan" },
        { code: "tj", ar: "طاجيكستان", en: "Tajikistan" }, { code: "tz", ar: "تنزانيا", en: "Tanzania" },
        { code: "th", ar: "تايلاند", en: "Thailand" }, { code: "tn", ar: "تونس", en: "Tunisia" },
        { code: "tr", ar: "تركيا", en: "Turkey" }, { code: "tm", ar: "تركمانستان", en: "Turkmenistan" },
        { code: "ug", ar: "أوغندا", en: "Uganda" }, { code: "ua", ar: "أوكرانيا", en: "Ukraine" },
        { code: "ae", ar: "الإمارات", en: "United Arab Emirates" }, { code: "gb", ar: "المملكة المتحدة", en: "United Kingdom" },
        { code: "us", ar: "الولايات المتحدة", en: "United States" }, { code: "uy", ar: "أوروغواي", en: "Uruguay" },
        { code: "uz", ar: "أوزبكستان", en: "Uzbekistan" }, { code: "ve", ar: "فنزويلا", en: "Venezuela" },
        { code: "vn", ar: "فيتنام", en: "Vietnam" }, { code: "ye", ar: "اليمن", en: "Yemen" },
        { code: "zm", ar: "زامبيا", en: "Zambia" }, { code: "zw", ar: "زيمبابوي", en: "Zimbabwe" }
    ];

    const BANK_DATA_GLOBAL = {
        'SA': { banks: ['البنك الأهلي السعودي (SNB)', 'مصرف الراجحي', 'بنك الرياض', 'البنك الأول (SABB)', 'بنك البلاد', 'بنك الجزيرة', 'مصرف الإنماء', 'البنك العربي الوطني (ANB)', 'البنك السعودي الفرنسي', 'STC Pay', 'Urpay'] },
        'AE': { banks: ['بنك أبوظبي الأول (FAB)', 'بنك الإمارات دبي الوطني (ENBD)', 'بنك دبي الإسلامي (DIB)', 'مصرف أبوظبي الإسلامي (ADIB)', 'بنك المشرق', 'بنك رأس الخيمة الوطني (RAKBANK)', 'بنك دبي التجاري (CBD)'] },
        'TR': { banks: ['Ziraat Bankası', 'Türkiye İş Bankası', 'Garanti BBVA', 'Yapı Kredi', 'Akbank', 'Halkbank', 'VakıfBank', 'QNB Finansbank', 'DenizBank', 'TEB', 'Kuveyt Türk', 'Albaraka Türk', 'Papara', 'Enpara'] },
        'EG': { banks: ['البنك الأهلي المصري (NBE)', 'بنك مصر (BM)', 'البنك التجاري الدولي (CIB)', 'بنك القاهرة', 'بنك الإسكندرية', 'البنك العربي الأفريقي الدولي', 'بنك QNB الأهلي', 'مصرف أبوظبي الإسلامي - مصر'] },
        'JO': { banks: ['البنك العربي', 'بنك الإسكان', 'البنك الإسلامي الأردني', 'البنك الأهلي الأردني', 'بنك الأردن', 'كابيتال بنك', 'بنك الاتحاد'] },
        'KW': { banks: ['بنك الكويت الوطني (NBK)', 'بيت التمويل الكويتي (KFH)', 'بنك بوبيان', 'بنك الخليج', 'البنك التجاري الكويتي', 'بنك برقان'] },
        'IQ': { banks: ['مصرف الرافدين', 'مصرف الرشيد', 'المصرف العراقي للتجارة (TBI)', 'مصرف بغداد', 'المركزي العراقي', 'مصرف التنمية الدولي'] },
        'QA': { banks: ['بنك قطر الوطني (QNB)', 'مصرف قطر الإسلامي (QIB)', 'البنك التجاري (CBQ)', 'مصرف الريان', 'بنك الدوحة', 'بنك بروة'] },
        'BH': { banks: ['بنك البحرين الوطني (NBB)', 'بنك البحرين والكويت (BBK)', 'البنك الأهلي المتحد', 'بنك السلام', 'بنك البركة', 'بيت التمويل الكويتي - البحرين'] },
        'OM': { banks: ['بنك مسقط', 'بنك ظفار', 'البنك الوطني العماني (NBO)', 'بنك نزوى', 'بنك صحار الدولي', 'بنك عمان العربي'] },
        'MA': { banks: ['التجاري وفا بنك (Attijariwafa)', 'البنك الشعبي (Banque Populaire)', 'بنك إفريقيا (BMCE)', 'القرض الفلاحي للمغرب', 'الشركة العامة المغرب', 'البنك المغربي للتجارة والصناعة (BMCI)'] },
        'DZ': { banks: ['البنك الخارجي الجزائري (BEA)', 'البنك الوطني الجزائري (BNA)', 'القرض الشعبي الجزائري (CPA)', 'بنك الفلاحة والتنمية الريفية (BADR)', 'CNEP Banque'] },
        'TN': { banks: ['بنك تونس العربي الدولي (BIAT)', 'البنك الوطني الفلاحي (BNA)', 'الشركة التونسية للبنك (STB)', 'بنك الإسكان (BH)', 'التجاري بنك', 'أمنية بنك'] },
        'LY': { banks: ['مصرف الجمهورية', 'المصرف التجاري الوطني', 'مصرف الوحدة', 'مصرف الصحارى', 'مصرف الأمان', 'مصرف التجارة والتنمية'] },
        'SD': { banks: ['بنك الخرطوم', 'بنك فيصل الإسلامي', 'بنك أمدرمان الوطني', 'البنك السوداني الفرنسي', 'البنك الزراعي السوداني'] },
        'SY': { banks: ['المصرف التجاري السوري', 'المصرف العقاري', 'بنك بيمو السعودي الفرنسي', 'بنك سورية الدولي الإسلامي', 'بنك البركة سورية'] },
        'LB': { banks: ['بنك عوده (Bank Audi)', 'بلوم بنك (BLOM Bank)', 'فرنسبنك', 'بنك بيبلوس', 'بنك بيروت'] },
        'PS': { banks: ['بنك فلسطين', 'البنك الإسلامي الفلسطيني', 'البنك الوطني', 'البنك الإسلامي العربي', 'بنك القدس'] },
        'YE': { banks: ['بنك التضامن', 'بنك الكريمي الإسلامي', 'بنك اليمن والكويت', 'البنك اليمني للإنشاء والتعمير', 'بنك القطيبي'] },
        'SO': { banks: ['Dahabshil Bank', 'Premier Bank', 'Salam Somali Bank', 'IBS Bank'] },
        
        // أمريكا الشمالية
        'US': { banks: ['JPMorgan Chase', 'Bank of America', 'Wells Fargo', 'Citibank', 'U.S. Bank', 'PNC Bank', 'Truist', 'Goldman Sachs', 'Capital One'] },
        'CA': { banks: ['Royal Bank of Canada (RBC)', 'TD Bank', 'Scotiabank', 'BMO', 'CIBC', 'National Bank of Canada'] },
        
        // أوروبا
        'GB': { banks: ['HSBC', 'Barclays', 'Lloyds Bank', 'NatWest', 'Standard Chartered', 'Revolut', 'Monzo', 'Santander UK'] },
        'DE': { banks: ['Deutsche Bank', 'Commerzbank', 'KfW', 'DZ Bank', 'N26', 'Sparkasse'] },
        'FR': { banks: ['BNP Paribas', 'Crédit Agricole', 'Société Générale', 'Groupe BPCE', 'Crédit Mutuel'] },
        'IT': { banks: ['Intesa Sanpaolo', 'UniCredit', 'Cassa Depositi e Prestiti', 'Banco BPM'] },
        'ES': { banks: ['Banco Santander', 'BBVA', 'CaixaBank', 'Banco Sabadell'] },
        'CH': { banks: ['UBS', 'Credit Suisse', 'Zurich Cantonal Bank', 'Julius Baer'] },
        'NL': { banks: ['ING Group', 'Rabobank', 'ABN AMRO', 'bunq'] },
        'IE': { banks: ['Allied Irish Banks (AIB)', 'Bank of Ireland', 'Permanent TSB'] },
        'BE': { banks: ['BNP Paribas Fortis', 'KBC Bank', 'Belfius', 'ING Belgium'] },
        'SE': { banks: ['Handelsbanken', 'SEB', 'Swedbank', 'Nordea'] },
        'NO': { banks: ['DNB', 'SpareBank 1', 'Nordea Norway'] },
        'FI': { banks: ['Nordea', 'OP Financial Group', 'Danske Bank'] },
        'DK': { banks: ['Danske Bank', 'Nykredit', 'Jyske Bank'] },
        'PT': { banks: ['Caixa Geral de Depósitos', 'Millennium BCP', 'Novo Banco'] },
        'GR': { banks: ['National Bank of Greece', 'Piraeus Bank', 'Alpha Bank', 'Eurobank'] },
        'RU': { banks: ['Sberbank', 'VTB Bank', 'Gazprombank', 'Alfa-Bank', 'Tinkoff'] },
        'UA': { banks: ['PrivatBank', 'Oschadbank', 'Ukreximbank', 'Monobank'] },

        // آسيا والمحيط الهادئ
        'CN': { banks: ['ICBC', 'Construction Bank (CCB)', 'Agricultural Bank (ABC)', 'Bank of China', 'Merchants Bank'] },
        'JP': { banks: ['MUFG Bank', 'SMBC', 'Mizuho Bank', 'Japan Post Bank'] },
        'IN': { banks: ['State Bank of India (SBI)', 'HDFC Bank', 'ICICI Bank', 'Punjab National Bank', 'Axis Bank'] },
        'AU': { banks: ['Commonwealth Bank', 'Westpac', 'ANZ', 'NAB', 'Macquarie'] },
        'NZ': { banks: ['ANZ', 'ASB Bank', 'Bank of New Zealand', 'Westpac'] },
        'SG': { banks: ['DBS Bank', 'OCBC Bank', 'UOB'] },
        'MY': { banks: ['Maybank', 'CIMB', 'Public Bank Berhad', 'RHB Bank'] },
        'ID': { banks: ['Bank Mandiri', 'BRI', 'BCA', 'BNI'] },
        'TH': { banks: ['Bangkok Bank', 'Kasikornbank', 'Krung Thai Bank', 'SCB'] },
        'PH': { banks: ['BDO Unibank', 'Metrobank', 'BPI', 'Landbank'] },
        'VN': { banks: ['Agribank', 'BIDV', 'VietinBank', 'Vietcombank'] },
        'PK': { banks: ['Habib Bank (HBL)', 'National Bank of Pakistan', 'Meezan Bank', 'UBL', 'MCB'] },
        'BD': { banks: ['Sonali Bank', 'Islami Bank Bangladesh', 'Dutch-Bangla Bank', 'BRAC Bank'] },
        
        // أمريكا اللاتينية
        'BR': { banks: ['Itaú Unibanco', 'Banco do Brasil', 'Bradesco', 'Caixa Econômica', 'Nubank'] },
        'MX': { banks: ['BBVA México', 'Banorte', 'Citibanamex', 'Santander México'] },
        'AR': { banks: ['Banco Nación', 'Banco Galicia', 'Banco Santander Río', 'BBVA Argentina'] },
        'CO': { banks: ['Bancolombia', 'Banco de Bogotá', 'Davivienda'] },
        'CL': { banks: ['Banco de Chile', 'Banco Santander Chile', 'BancoEstado'] },
        
        // إفريقيا (غير العربية)
        'ZA': { banks: ['Standard Bank', 'FirstRand (FNB)', 'ABSA', 'Nedbank', 'Capitec Bank'] },
        'NG': { banks: ['Access Bank', 'Zenith Bank', 'First Bank of Nigeria', 'UBA', 'Guaranty Trust Bank'] },
        'KE': { banks: ['KCB Bank', 'Equity Bank', 'Co-operative Bank', 'NCBA', 'M-Pesa (Safaricom)'] },
        'GH': { banks: ['GCB Bank', 'Ecobank Ghana', 'Absa Bank Ghana'] },
        
        // دول أخرى متنوعة
        'CY': { banks: ['Bank of Cyprus', 'Hellenic Bank', 'Eurobank Cyprus'] },
        'MT': { banks: ['Bank of Valletta', 'HSBC Malta', 'APS Bank'] },
        'KR': { banks: ['KB Kookmin Bank', 'Shinhan Bank', 'Hana Bank', 'Woori Bank', 'KakaoBank'] }
    };

    const IBAN_LENGTHS_GLOBAL = {
        'AL':28,'AD':24,'AT':20,'AZ':28,'BH':22,'BY':28,'BE':16,'BA':20,'BR':29,'BG':22,
        'CR':22,'HR':21,'CY':28,'CZ':24,'DK':18,'DO':28,'TL':23,'EE':20,'FO':18,'FI':18,
        'FR':27,'GE':22,'DE':22,'GI':23,'GR':27,'GL':18,'GT':28,'HU':28,'IS':26,'IQ':23,
        'IE':22,'IL':23,'IT':27,'JO':30,'KZ':20,'XK':20,'KW':30,'LV':21,'LB':28,'LI':21,
        'LT':20,'LU':20,'MK':19,'MT':31,'MR':27,'MU':30,'MC':27,'MD':24,'ME':22,'NL':18,
        'NO':15,'PK':24,'PS':29,'PL':28,'PT':25,'QA':29,'RO':24,'LC':32,'SM':27,'ST':25,
        'SA':24,'RS':22,'SC':31,'SK':24,'SI':19,'ES':24,'SD':18,'SE':24,'CH':21,'TN':24,
        'TR':26,'UA':29,'AE':23,'GB':22,'VA':22,'VG':24,'EG':29,'OM':23,'SY':24
    };

    function getCode(name) {
        if (!name) return ""; name = name.trim().toLowerCase();
        const f = COUNTRIES_GLOBAL.find(c => c.code.toLowerCase() === name || c.ar.toLowerCase() === name || c.en.toLowerCase() === name);
        return f ? f.code.toUpperCase() : "";
    }

    $(document).ready(function() {
        const $bc = $('#bank_country_select');
        const $bn = $('#bank_name_select');
        const $ib = $('#iban_input');
        const $pr = $('#iban_prefix');
        const $hi = $('#iban_hint');

        // تعبئة الدول فوراً باستخدام جيكويري
        $bc.empty().append('<option value="">{{ __("اختر دولة البنك...") }}</option>');
        COUNTRIES_GLOBAL.forEach(c => {
            const countryText = (document.documentElement.dir === 'rtl') ? (c.ar + ' - ' + c.en) : (c.en + ' - ' + c.ar);
            $bc.append($('<option>', { value: c.code.toUpperCase(), text: countryText }).attr('data-ar', c.ar).attr('data-en', c.en));
        });

        // تفعيل Select2 إذا كان موجوداً
        if ($.fn.select2) {
            $bc.select2({ theme: 'bootstrap-5', dir: 'rtl', width: '100%' });
            $bn.select2({ theme: 'bootstrap-5', dir: 'rtl', width: '100%' });
        }

        $bc.on('change', function() {
            const v = $(this).val();
            const ar = $(this).find('option:selected').attr('data-ar');
            const len = IBAN_LENGTHS_GLOBAL[v] || 24;
            if (v) {
                $pr.text(v);
                $hi.html(`{{ __('صيغة الآيبان:') }} <code dir="ltr">${v} + ${len-2} {{ __('رقم/حرف') }}</code>`);
                $bn.empty();
                const d = BANK_DATA_GLOBAL[v];
                if (d && d.banks) {
                    $bn.append('<option value="">{{ __("اختر البنك...") }}</option>');
                    d.banks.forEach(b => $bn.append(new Option(b, b)));
                    $bn.append('<option value="__other__">{{ __("🏦 بنك آخر...") }}</option>');
                    $bn.prop('disabled', false);
                    $('#bank_name_manual').addClass('d-none');
                } else {
                    $bn.append('<option value="">{{ __("أدخل اسم البنك يدوياً") }}</option>').prop('disabled', true);
                    const countryName = (document.documentElement.dir === 'rtl') ? (ar || '{{ __("هذه الدولة") }}') : ($(this).find('option:selected').attr('data-en') || '{{ __("this country") }}');
                    $('#bank_name_manual').removeClass('d-none').attr('placeholder', '{{ __("اكتب اسم البنك في") }} ' + countryName);
                }
            }
        });

        $ib.on('input', function() {
            let v = $(this).val().replace(/[^0-9]/g, '');
            $(this).val(v);
            const c = $bc.val();
            const exp = IBAN_LENGTHS_GLOBAL[c] || 24;
            const fullSize = (c ? c.length : 0) + v.length;
            if (c) {
                if (fullSize < exp) $hi.html(`<span class="text-warning">{{ __('⚠️ قصير') }} (${fullSize}/${exp})</span>`);
                else if (fullSize > exp) $hi.html(`<span class="text-danger">{{ __('❌ طويل جداً') }} (${fullSize}/${exp})</span>`);
                else $hi.html(`<span class="text-success">{{ __('✅ الطول صحيح') }} (${exp})</span>`);
            }
        });

        if ($ib.val()) {
            $ib.trigger('input');
        }

        const savedC = "{{ $store->bank_country }}";
        if (savedC) {
            let cC = savedC.toUpperCase();
            if (cC.length > 2) cC = getCode(savedC);
            if (cC) {
                $bc.val(cC).trigger('change');
                setTimeout(() => {
                    const savedB = "{{ $store->iban_bank_name }}";
                    if (savedB && savedB !== "null") {
                        if ($bn.find(`option[value="${savedB}"]`).length) $bn.val(savedB).trigger('change');
                        else { $bn.val('__other__').trigger('change'); $('#bank_name_manual').val(savedB); }
                    }
                }, 800);
            }
        }
    });
</script>
@endsection
