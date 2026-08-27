<?php

namespace App\Http\Controllers\Exports;

use App\Exports\ReferFriendsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReferFriendsExportController extends Controller
{

    public function export()
    {
        return Excel::download(new ReferFriendsExport(), 'refer_friend_forms.xlsx');
    }

}
