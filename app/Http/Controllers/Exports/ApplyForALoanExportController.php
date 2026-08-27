<?php

namespace App\Http\Controllers\Exports;

use App\Exports\ApplyForALoanExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ApplyForALoanExportController extends Controller
{

    public function export()
    {
        return Excel::download(new ApplyForALoanExport(), 'apply_for_a_loans.xlsx');
    }

}
