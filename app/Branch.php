<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Branch extends Model
{
	protected $with = ['branch_category:id,marker,marker_small'];

	/**
     * Get the Branch Category
     */
    public function branch_category()
    {
        return $this->belongsTo('App\BranchesCategory', 'branches_categories_id');
    }

}
