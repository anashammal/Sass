<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            // العالم العربي (Arab World)
            ['name_ar' => 'ريال سعودي', 'name_en' => 'Saudi Riyal', 'code' => 'SAR', 'symbol' => 'SR'],
            ['name_ar' => 'درهم إماراتي', 'name_en' => 'UAE Dirham', 'code' => 'AED', 'symbol' => 'د.إ'],
            ['name_ar' => 'دينار كويتي', 'name_en' => 'Kuwaiti Dinar', 'code' => 'KWD', 'symbol' => 'KD'],
            ['name_ar' => 'ريال قطري', 'name_en' => 'Qatari Riyal', 'code' => 'QAR', 'symbol' => 'QR'],
            ['name_ar' => 'ريال عماني', 'name_en' => 'Omani Rial', 'code' => 'OMR', 'symbol' => 'OR'],
            ['name_ar' => 'دينار بحريني', 'name_en' => 'Bahraini Dinar', 'code' => 'BHD', 'symbol' => 'BD'],
            ['name_ar' => 'دينار أردني', 'name_en' => 'Jordanian Dinar', 'code' => 'JOD', 'symbol' => 'JD'],
            ['name_ar' => 'جنيه مصري', 'name_en' => 'Egyptian Pound', 'code' => 'EGP', 'symbol' => 'E£'],
            ['name_ar' => 'دينار عراقي', 'name_en' => 'Iraqi Dinar', 'code' => 'IQD', 'symbol' => 'ع.د'],
            ['name_ar' => 'ليرة لبنانية', 'name_en' => 'Lebanese Pound', 'code' => 'LBP', 'symbol' => 'LL'],
            ['name_ar' => 'ليرة سورية', 'name_en' => 'Syrian Pound', 'code' => 'SYP', 'symbol' => 'LS'],
            ['name_ar' => 'ريال يمني', 'name_en' => 'Yemeni Rial', 'code' => 'YER', 'symbol' => '﷼'],
            ['name_ar' => 'دينار جزائري', 'name_en' => 'Algerian Dinar', 'code' => 'DZD', 'symbol' => 'DA'],
            ['name_ar' => 'درهم مغربي', 'name_en' => 'Moroccan Dirham', 'code' => 'MAD', 'symbol' => 'DH'],
            ['name_ar' => 'دينار تونسي', 'name_en' => 'Tunisian Dinar', 'code' => 'TND', 'symbol' => 'DT'],
            ['name_ar' => 'دينار ليبي', 'name_en' => 'Libyan Dinar', 'code' => 'LYD', 'symbol' => 'LD'],
            ['name_ar' => 'جنيه سوداني', 'name_en' => 'Sudanese Pound', 'code' => 'SDG', 'symbol' => 'SDG'],
            ['name_ar' => 'أوقية موريتانية', 'name_en' => 'Mauritanian Ouguiya', 'code' => 'MRU', 'symbol' => 'UM'],
            ['name_ar' => 'شلن صومالي', 'name_en' => 'Somali Shilling', 'code' => 'SOS', 'symbol' => 'S'],
            ['name_ar' => 'فرنك جيبوتي', 'name_en' => 'Djiboutian Franc', 'code' => 'DJF', 'symbol' => 'Fdj'],

            // عملات عالمية رئيسية (Major Global Currencies)
            ['name_ar' => 'دولار أمريكي', 'name_en' => 'US Dollar', 'code' => 'USD', 'symbol' => '$'],
            ['name_ar' => 'يورو', 'name_en' => 'Euro', 'code' => 'EUR', 'symbol' => '€'],
            ['name_ar' => 'جنيه إسترليني', 'name_en' => 'British Pound', 'code' => 'GBP', 'symbol' => '£'],
            ['name_ar' => 'ليرة تركية', 'name_en' => 'Turkish Lira', 'code' => 'TRY', 'symbol' => '₺'],
            ['name_ar' => 'ين ياباني', 'name_en' => 'Japanese Yen', 'code' => 'JPY', 'symbol' => '¥'],
            ['name_ar' => 'يوان صيني', 'name_en' => 'Chinese Yuan', 'code' => 'CNY', 'symbol' => '¥'],
            ['name_ar' => 'فرنك سويسري', 'name_en' => 'Swiss Franc', 'code' => 'CHF', 'symbol' => 'CHF'],
            ['name_ar' => 'دولار كندي', 'name_en' => 'Canadian Dollar', 'code' => 'CAD', 'symbol' => 'C$'],
            ['name_ar' => 'دولار أسترالي', 'name_en' => 'Australian Dollar', 'code' => 'AUD', 'symbol' => 'A$'],
        ];

        foreach ($currencies as $currency) {
            \App\Models\Currency::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
