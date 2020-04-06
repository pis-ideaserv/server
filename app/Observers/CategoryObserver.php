<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Logs;
use Auth;

class CategoryObserver
{
    /**
     * Handle the category "created" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function created(Category $category)
    {
        // $a = $category->getAttributes();

        // $logs = new Logs();
        // $logs->user = Auth::user()->id;
        // $logs->action = "create";
        // $logs->target = "Category";
        // $logs->update = json_encode($a);
        // $logs->save();
    }

    /**
     * Handle the category "updated" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function updated(Category $category)
    {
        // $a = $category->getAttributes();
        // $b = $category->getOriginal();

        // $logs           = new Logs();
        // $logs->user     = Auth::user()->id;
        // $logs->action   = "update";
        // $logs->target   = "Category";
        // $logs->previous = json_encode($b);
        // $logs->update   = json_encode($a);
        // $logs->save();
    }

    /**
     * Handle the category "deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function deleted(Category $category)
    {
        //
    }

    /**
     * Handle the category "restored" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function restored(Category $category)
    {
        //
    }

    /**
     * Handle the category "force deleted" event.
     *
     * @param  \App\Models\Category  $category
     * @return void
     */
    public function forceDeleted(Category $category)
    {
        //
    }
}
