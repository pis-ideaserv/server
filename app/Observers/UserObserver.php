<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Logs;
use App\Models\Level;
use Auth;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        $a = $user->getAttributes();
        $a['level'] = Level::where('id','=',$a['level'])->get()->first();

        unset($a['password']); //remove password from the array

        $logs = new Logs();
        $logs->user = Auth::user()->id;
        $logs->action = "create";
        $logs->target = "User";
        $logs->update = json_encode($a);
        $logs->save();
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        $a = $user->getAttributes();
        $b = $user->getOriginal();

        $a['level'] = Level::where('id','=',$a['level'])->get()->first();
        $b['level'] = Level::where('id','=',$b['level'])->get()->first();


        unset($a['password']);   //remove password from the array 
        unset($b['password']);   //remove password from the array



        $logs           = new Logs();
        $logs->user     = Auth::user()->id;
        $logs->action   = "update";
        $logs->target   = "User";
        $logs->previous = json_encode($b);
        $logs->update   = json_encode($a);
        $logs->save();
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
