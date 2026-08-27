<?php

namespace App\Exports;

use App\CheckEligibleUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CheckEligibleUsersExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return CheckEligibleUser::select([
            'id',
            'full_name',
            'phone',
            'loan_type',
            'business_owner',
            'employee',
            'business_age',
            'own_home',
            'physical_disabiliy',
            'monthly_income',
            'monthly_expenses',
            'guarantors',
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
            'Phone',
            'Loan Type',
            'Business Owner',
            'Employee',
            'Business Age',
            'Own Home',
            'Physical Disabiliy',
            'Monthly Income',
            'Monthly Expenses',
            'Guarantors',
            'Governorate',
            'Created At',
            'Age'
        ];
    }
}
