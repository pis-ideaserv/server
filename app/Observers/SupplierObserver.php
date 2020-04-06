<?php

namespace App\Observers;

use App\Models\Supplier;
use App\Models\Logs;
use Auth;

class SupplierObserver
{
    /**
     * Handle the supplier "created" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function created(Supplier $supplier)
    {
        
        $a = $supplier->getAttributes();

        $logs = new Logs();
        $logs->user = Auth::user()->id;
        $logs->action = "create";
        $logs->target = "Supplier";
        $logs->update = json_encode($a);
        $logs->save();
    }

    /**
     * Handle the supplier "updated" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function updated(Supplier $supplier)
    {

        $a = $supplier->getAttributes();
        $b = $supplier->getOriginal();

        $logs           = new Logs();
        $logs->user     = Auth::user()->id;
        $logs->action   = "update";
        $logs->target   = "Supplier";
        $logs->previous = json_encode($b);
        $logs->update   = json_encode($a);
        $logs->save();
    }

    /**
     * Handle the supplier "deleted" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function deleted(Supplier $supplier)
    {
        //
    }

    /**
     * Handle the supplier "restored" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function restored(Supplier $supplier)
    {
        //
    }

    /**
     * Handle the supplier "force deleted" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function forceDeleted(Supplier $supplier)
    {
        //
    }
}
