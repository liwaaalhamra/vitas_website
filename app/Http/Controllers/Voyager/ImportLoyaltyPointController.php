<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use App\Models\Api\AppUsersPoint;

class ImportLoyaltyPointController extends VoyagerBreadController
{

	public function index()
	{
		return view('cms.import-loyalty-points');
	}

    public function import(Request $request)
    {
        $file = $request['file'];

        $customerArr = $this->csvToArray($file);

        for($i = 0; $i < count($customerArr); $i ++)
        {
            $row = new AppUsersPoint;
            $row->user_id = $customerArr[$i]['user'];
            $row->meta_name = $customerArr[$i]['meta_name'];
            $row->points = $customerArr[$i]['points'];
            $row->meta_value = $customerArr[$i]['meta_value'];
            $row->save();
        }

        return redirect()->back()->with('success', 'CSV file successfully imported');
    }

    function csvToArray($filename = '')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $flag = true;
        if (($handle = fopen($filename, 'r')) !== false){
            while (($row = fgetcsv($handle )) !== FALSE){
                if($flag) { $flag = false; continue; }
                $user = $row[0];
                $meta_name = $row[1];
                $points = $row[2];
                $meta_value = $row[3];

                $data[] = array(
                    'user' => $user,
                    'meta_name' => $meta_name,
                    'points' => $points,
                    'meta_value' => $meta_value
                );
            }
            fclose($handle);
        }

        return $data;
    }

}
