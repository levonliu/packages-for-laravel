<?php

namespace Levonliu\Packages\Service;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publish();
    }

    public function publish()
    {
        //配置文件
        $configs = __DIR__ . '/../Config';
        $configPath = config_path();

        $this->publishes([
            $configs   => $configPath
        ]);
    }
}
