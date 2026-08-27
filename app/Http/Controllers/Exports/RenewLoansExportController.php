<?php

namespace App\Http\Controllers\Exports;

use App\Exports\RenewLoansExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RenewLoansExportController extends Controller
{

    public function export()
    {
        return Excel::download(new RenewLoansExport(), 'renew_loans.xlsx');
    }

}
