<?php

namespace App\Http\Controllers\Exports;

use App\Exports\VacancyExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VacanciesExportController extends Controller
{

    public function export()
    {
        return Excel::download(new VacancyExport(), 'vacancies.xlsx');
    }

}
