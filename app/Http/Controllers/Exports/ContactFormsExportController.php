<?php

namespace App\Http\Controllers\Exports;

use App\Exports\ContactFormsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ContactFormsExportController extends Controller
{

    public function export()
    {
        return Excel::download(new ContactFormsExport(), 'contact_forms.xlsx');
    }

}
