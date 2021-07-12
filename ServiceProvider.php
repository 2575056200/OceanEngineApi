<?php

namespace didphp\OceanEngineApi

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->alias(QianChuanApi::class, 'QianChuanApi');
    }

    public function provides()
    {
        return [QianChuanApi::class, 'QianChuanApi'];
    }
}
