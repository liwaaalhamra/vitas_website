<?php

namespace App\Exports;

use App\ApplyForALoan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApplyForALoanExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ApplyForALoan::select([
            'id',
            'loan_type',
            'business_owner',
            'full_name',
            'age',
            'phone_number',
            'email_address',
            'governorate',
            'address',
            'platform',
            'employee',
            'home_improvement',
            'created_at'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'Loan Type',
            'Business Owner',
            'Full Name',
            'Age',
            'Phone Number',
            'Email Address',
            'Governorate',
            'Address',
            'Platform',
            'Employee',
            'Home Improvement',
            'Created At'
        ];
    }
}
