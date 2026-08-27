<?php

namespace App\Exports;

use App\RenewLoan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RenewLoansExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return RenewLoan::select([
            'id',
            'loan_type',
            'client_id',
            'full_name',
            'age',
            'phone_number',
            'governorate',
            'address',
            'created_at'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'Loan Type',
            'Client ID',
            'Full Name',
            'Age',
            'Phone Number',
            'Governorate',
            'Address',
            'Created At'
        ];
    }
}
