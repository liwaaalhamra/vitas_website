<?php

namespace App\Http\Controllers\Exports;

use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionExportController extends Controller
{
    public function export()
    {
        return Excel::download(new TransactionExport(), 'transactions.xlsx');
    }


}
