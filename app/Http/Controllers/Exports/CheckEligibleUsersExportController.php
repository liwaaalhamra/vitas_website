<?php

namespace App\Http\Controllers\Exports;

use App\Exports\CheckEligibleUsersExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CheckEligibleUsersExportController extends Controller
{

    public function export()
    {
        return Excel::download(new CheckEligibleUsersExport(), 'check_eligible_users.xlsx');
    }

}
