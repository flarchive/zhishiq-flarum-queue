<?php

use Flarum\Console\Event\Configuring;
use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;
use ZhiShiQ\Flarum\Queue\Listener\Listener;

return [
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),
    function (\Flarum\Foundation\Application $container, Dispatcher $events) {
        $events->listen(Configuring::class, function (Configuring $configuring) {
            $configuring->addCommand($configuring->app->make(\ZhiShiQ\Flarum\Queue\Console\ListenCommand::class));
            $configuring->addCommand($configuring->app->make(\ZhiShiQ\Flarum\Queue\Console\WorkCommand::class));
        });

        $container['config']['queue'] = [
            'default' => 'redis',
            'connections' => [
                'redis' => [
                    'driver' => 'redis',
                    'connection' => 'default',
                    'queue' => 'default',
                    'retry_after' => 90,
                    'block_for' => null,
                ]
            ]
        ];

        $container->register(\Illuminate\Queue\QueueServiceProvider::class);

        $container->singleton(\Illuminate\Contracts\Queue\Factory::class, function ($container) {
            return $container['queue'];
        });
        $container->alias(\Illuminate\Contracts\Queue\Factory::class, \Illuminate\Queue\QueueManager::class);

        $container->singleton(\Illuminate\Queue\Listener::class, function () {
            return new Listener(__DIR__ . "/../../");
        });

        $container->bind(\Illuminate\Contracts\Debug\ExceptionHandler::class, \ZhiShiQ\Flarum\Queue\ExceptionHandler::class);
    },
    new Extend\Locales(__DIR__ . '/locale'),
];
