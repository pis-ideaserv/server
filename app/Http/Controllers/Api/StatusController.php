<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Supplier;

class StatusController extends Controller
{
    public function index(){
        

        $counter = [
            'total' => [
                'supplier' => Supplier::all()->count(),
                'product'  => Product::all()->count(),
                'user'     => User::all()->count()
            ],
            'status' => [
                'New' => Product::leftJoin('status','status.id','=','product.status')->where('status.name','=','New')->count(),
                'Replaced' => Product::leftJoin('status','status.id','=','product.status')->where('status.name','=','Replaced')->count(),
                'Returned' => Product::leftJoin('status','status.id','=','product.status')->where('status.name','=','Returned')->count(),
                'Repaired' => Product::leftJoin('status','status.id','=','product.status')->where('status.name','=','Repaired')->count()
            ]
        ];

        return response()->json($counter);
    }
}
