<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DivineOmega\CachetPHP\Objects\CachetInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cachet', function(){
            $config = json_decode(getcwd().'/config.json', true);
            return new CachetInstance($config['cachet_url'], $config['cachet_token']);
        });
    }
}
