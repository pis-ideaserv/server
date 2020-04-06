<?php

namespace App\Observers;

use App\Models\ProductMasterList;
use App\Models\Logs;
use App\Models\Category;
use Auth;

class ProductMasterListObserver
{
    /**
     * Handle the product master list "created" event.
     *
     * @param  \App\Models\ProductMasterList  $productMasterList
     * @return void
     */
    public function created(ProductMasterList $productMasterList)
    {
        $a = $productMasterList->getAttributes();
        $a['category'] = Category::where('id','=',$a['category'])->get()->first();

        $logs = new Logs();
        $logs->user = Auth::user()->id;
        $logs->action = "create";
        $logs->target = "ProductMasterList";
        $logs->update = json_encode($a);
        $logs->save();
    }

    /**
     * Handle the product master list "updated" event.
     *
     * @param  \App\Models\ProductMasterList  $productMasterList
     * @return void
     */
    public function updated(ProductMasterList $productMasterList)
    {
        $a = $productMasterList->getAttributes();
        $b = $productMasterList->getOriginal();

        $a['category'] = Category::where('id','=',$a['category'])->get()->first();
        $b['category'] = Category::where('id','=',$b['category'])->get()->first();

        $logs           = new Logs();
        $logs->user     = Auth::user()->id;
        $logs->action   = "update";
        $logs->target   = "ProductMasterList";
        $logs->previous = json_encode($b);
        $logs->update   = json_encode($a);
        $logs->save();
    }

    /**
     * Handle the product master list "deleted" event.
     *
     * @param  \App\Models\ProductMasterList  $productMasterList
     * @return void
     */
    public function deleted(ProductMasterList $productMasterList)
    {
        //
    }

    /**
     * Handle the product master list "restored" event.
     *
     * @param  \App\Models\ProductMasterList  $productMasterList
     * @return void
     */
    public function restored(ProductMasterList $productMasterList)
    {
        //
    }

    /**
     * Handle the product master list "force deleted" event.
     *
     * @param  \App\Models\ProductMasterList  $productMasterList
     * @return void
     */
    public function forceDeleted(ProductMasterList $productMasterList)
    {
        //
    }
}
