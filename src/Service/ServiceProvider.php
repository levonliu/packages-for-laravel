<?php

namespace Levonliu\Packages\Service;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $configs = ['request'];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
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

    public function mergeConfig()
    {
        foreach ($this->configs as $key) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/' . $key . '.php', $key);
        }
    }
}
