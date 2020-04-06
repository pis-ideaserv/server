<?php

namespace App\Observers;
use App\Models\Product;
use App\Models\Logs;
use App\Models\Supplier;
use App\Models\ProductMasterList;
use App\Models\Status;
use Auth;

class ProductObserver
{
    /**
     * Handle the product "created" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function created(Product $product)
    {
        $a = $product->getAttributes();
        
        

        // $supplier = $a['supplier'];
        // $product = $a['product'];

        $a['supplier'] = Supplier::where('id','=',$a['supplier'])->get()->first()->toArray();
        $a['product'] = ProductMasterList::where('id','=', $a['product'])->get()->first()->toArray();
        $a['status'] = Status::where('id','=',$a['status'])->get()->first()->toArray();


        $logs = new Logs();
        $logs->user = Auth::user()->id;
        $logs->action = "create";
        $logs->target = "Product";
        $logs->update = json_encode($a);
        $logs->save();

    }

    /**
     * Handle the product "updated" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function updated(Product $product)
    {
        $a = $product->getAttributes();
        $b = $product->getOriginal();

        // $supplier = $a['supplier'];
        // $product = $a['product'];

        $a['supplier'] = Supplier::where('id','=',$a['supplier'])->get()->first()->toArray();
        $a['product'] = ProductMasterList::where('id','=', $a['product'])->get()->first()->toArray();
        $a['status'] = Status::where('id','=',$a['status'])->get()->first()->toArray();



        $b['supplier'] = Supplier::where('id','=',$b['supplier'])->get()->first()->toArray();
        $b['product'] = ProductMasterList::where('id','=', $b['product'])->get()->first()->toArray();
        $b['status'] = Status::where('id','=',$b['status'])->get()->first()->toArray();



        $logs           = new Logs();
        $logs->user     = Auth::user()->id;
        $logs->action   = "update";
        $logs->target   = "Product";
        $logs->previous = json_encode($b);
        $logs->update   = json_encode($a);
        $logs->save();
    }

    /**
     * Handle the product "deleted" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function deleted(Product $product)
    {
        //
    }

    /**
     * Handle the product "restored" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function restored(Product $product)
    {
        //
    }

    /**
     * Handle the product "force deleted" event.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function forceDeleted(Product $product)
    {
        //
    }
}
