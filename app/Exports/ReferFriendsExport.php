<?php

namespace App\Exports;

use App\ReferFriend;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReferFriendsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ReferFriend::select([
            'id',
            'full_name',
            'phone_number',
            'email_address',
            'address',
            'loan_type',
            'business_owner',
            'governorate',
            'created_at',
            'age'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'Full Name',
            'Phone Number',
            'Email Address',
            'Address',
            'Loan Type',
            'Business Owner',
            'Own Home',
            'Governorate',
            'Created At',
            'Age'
        ];
    }
}
