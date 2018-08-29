<?php

namespace App\Http\Controllers;

use App\Models\PreordainList;
use App\Models\PreordainOpen;
use Illuminate\Http\Request;
use Excel;

class FeatureController extends Controller
{
    //
    public function export($id){
        $preordainList = PreordainList::where('order_id',$id)->get();
        $preordainList->toArray();
        dd($preordainList);
    }
}
