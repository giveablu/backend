<?php

namespace App\Observers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class CrudNotifyObserver
{
    public function updated($model)
    {
        $message = 'Updated';
        $this->notify($model, $message);
    }

    public function created($model)
    {
        $message = 'Created';
        $this->notify($model, $message);
    }

    public function deleted($model)
    {
        $message = 'Deleted';
        $this->notify($model, $message);
    }

    public function notify($modelName, $msz){
        $table = Str::singular($modelName->getTable());
        // dd($table,$msz);

    }
}
