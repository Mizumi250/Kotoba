<?php

use App\Providers\AppServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;

return [
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    AppServiceProvider::class,    App\Providers\ModuleRouteServiceProvider::class,

];
