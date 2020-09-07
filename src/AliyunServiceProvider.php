<?php

namespace Aliyun\Signatrue;

class AliyunServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * 服务提供者加是否延迟加载.
     *
     * @var bool
     */
    protected $defer = true; // 延迟加载服务

    public function register()
    {
        $configPath = __DIR__ . '/config/config.php';

        $this->mergeConfigFrom($configPath, 'aliyun');
        $this->publishes([
            $configPath => config_path('aliyun.php'),
        ], 'config');

        $this->app->singleton(AliyunService::class, function () {
            return new AliyunService(config('aliyun.key'), config('aliyun.secret'));
        });

        $this->app->alias(AliyunService::class, 'aliyun');
    }

    /**
     * @return array
     */
    public function provides()
    {
        // 因为延迟加载 所以要定义 provides 函数 具体参考laravel 文档
        return [AliyunService::class, 'aliyun'];
    }
}
