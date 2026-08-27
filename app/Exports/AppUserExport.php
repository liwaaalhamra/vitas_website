<?php

namespace App\Exports;

use App\Models\Api\AppUsers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AppUserExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AppUsers::select([
            'id',
            'employee_id',
            'registration',
            'customer_name',
            'first_name',
            'english_name',
            'mobile_number',
            'email',
            'quarter',
            'town',
            'governorate',
            'gender',
            'total_points',
            'can_redeem',
            'created_at'
        ])->get()->makeVisible(['first_name', 'english_name', 'customer_name', 'mobile_number', 'email', 'gender', 'quarter', 'town', 'governorate', 'total_points', 'registration', 'created_at']);
    }

    public function headings(): array
    {
        return [
            'Id',
            'Employee ID',
            'Registration',
            'Customer Name',
            'First Name',
            'English Name',
            'Mobile Number',
            'Email',
            'Quarter',
            'Town',
            'Governorate',
            'Gender',
            'Total Points',
            'Can Redeem',
            'Created At'
        ];
    }
}
