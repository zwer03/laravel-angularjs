<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\UserService;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Helpers\Interfaces\UserInterface', function(){
            return new UserService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }


    public function provides()
    {
        return ['App\Helpers\Interfaces\UserInterface'];
    }
}
