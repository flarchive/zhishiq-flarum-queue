<?php
/**
 * Created by IntelliJ IDEA.
 * User: bill
 * Date: 2019-01-04
 * Time: 17:08
 */

namespace ZhiShiQ\Flarum\Queue\Console;

use Flarum\Foundation\Application;

abstract class AbstractCommand extends \Flarum\Console\AbstractCommand
{
    /**
     * @var \Illuminate\Queue\QueueManager
     */
    protected $app;

    public function __construct(Application $app, $name = null)
    {
        parent::__construct($name);
        $this->app = $app;
        $app['config']['cache.stores.redis'] = [
            'driver' => 'redis',
            'connection' => 'default',
        ];
        $app['config']['cache.default'] = 'redis';

        $app->register(\Illuminate\Cache\CacheServiceProvider::class);
    }

    /**
     * Determine if the given argument is present.
     *
     * @param  string|int  $name
     * @return bool
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string|null  $key
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all of the arguments passed to the command.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->argument();
    }

    /**
     * Determine if the given option is present.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string  $key
     * @return string|array
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all of the options passed to the command.
     *
     * @return array
     */
    public function options()
    {
        return $this->option();
    }
}
