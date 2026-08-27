<?php

namespace App\Exports;

use App\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaction::select([
            'id',
            'user_id',
            'installment_number',
            'amount_iqd',
            'status',
            'phone_number',
            'loan_number',
            'branch_code',
            'amount_usd',
            'created_at'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'User Id',
            'Installment Number',
            'Amount IQD',
            'Status',
            'Phone Number',
            'Loan Number',
            'Branch Code',
            'Amount USD',
            'Created At'
        ];
    }
}
