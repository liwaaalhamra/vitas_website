<?php

namespace App\Exports;

use App\Vacancy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VacancyExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Vacancy::select([
            'id',
            'first_name',
            'last_name',
            'date_of_birth',
            'gender',
            'nationality',
            'place_of_residence',
            'country',
            'mobile_number',
            'email_address',
            'position',
            'level_of_education',
            'major',
            'last_job_title',
            'years_of_experience',
            'field_of_experience',
            'message',
            'open_vacancy',
            'cv',
            'created_at',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'First Name',
            'Last Name',
            'Date Of Birth',
            'Gender',
            'Nationality',
            'Place Of Residence',
            'Country',
            'Mobile Number',
            'Email Address',
            'Position',
            'Level Of Education',
            'Major',
            'Last Job Title',
            'Years Of Experience',
            'Field Of Experience',
            'Message',
            'Open Vacancy',
            'CV',
            'Created At',
        ];
    }
}
