<?php

namespace App\Exports;

use App\ContactForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactFormsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ContactForm::select([
            'id',
            'first_name',
            'last_name',
            'country',
            'place_of_residence',
            'email_address',
            'mobile_number',
            'message',
            'governorate',
            'existing_client',
            'business_owner',
            'created_at',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'First Name',
            'Last Name',
            'Country',
            'Place Of Residence',
            'Email Address',
            'Mobile Number',
            'Message',
            'Governorate',
            'Existing Client',
            'Business Owner',
            'Created At',
        ];
    }
}
