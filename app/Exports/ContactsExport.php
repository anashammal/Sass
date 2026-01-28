<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    protected $contacts;

    public function __construct($contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * جلب البيانات
     */
    public function collection()
    {
        return $this->contacts->map(function ($contact) {
            return [
                'الاسم' => $contact->contact_name,
                'الشركة' => $contact->company_name,
                'الهاتف' => $contact->phone,
                'البريد الالكتروني' => $contact->email,
                'الرقم الضريبي' => $contact->tax_number,
                'العنوان' => $contact->address,
                'النوع' => $contact->is_supplier ? ($contact->is_customer ? 'مورد/زبون' : 'مورد') : 'زبون',
                'الرصيد' => $contact->balance, // الرصيد الفعلي
            ];
        });
    }

    /**
     * رؤوس الأعمدة
     */
    public function headings(): array
    {
        return [
            'الاسم',
            'الشركة',
            'الهاتف',
            'البريد الالكتروني',
            'الرقم الضريبي',
            'العنوان',
            'النوع',
            'الرصيد',
        ];
    }
}
