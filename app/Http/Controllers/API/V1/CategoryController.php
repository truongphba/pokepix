<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function list(){
        $list =  Category::orderByRaw('ISNULL(position), position ASC')->orderBy('created_at', 'DESC')->get();
        $data =  [];
        foreach($list as $item){
            $data[] = [
                'id' => $item->id,
                'name' => $item->name,
                'type' => config('global.pic_type')[$item->type]
            ];
        }
        return response()->json(['data'  => $data], 200);
    }
}
