<?php

namespace App\Http\Controllers\Exports;

use App\Exports\AppUserExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AppUserExportController extends Controller
{
    public function export()
    {
        return Excel::download(new AppUserExport(), 'users_export.xlsx');
    }


}
