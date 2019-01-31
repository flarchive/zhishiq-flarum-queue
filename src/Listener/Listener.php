<?php
/**
 * Created by IntelliJ IDEA.
 * User: bill
 * Date: 2019-01-04
 * Time: 17:03
 */

namespace ZhiShiQ\Flarum\Queue\Listener;

class Listener extends \Illuminate\Queue\Listener
{
    /**
     * Create a new queue listener.
     *
     * @param  string $commandPath
     * @return void
     */
    public function __construct($commandPath)
    {
        parent::__construct($commandPath);
    }

    /**
     * Get the Artisan binary.
     *
     * @return string
     */
    protected function artisanBinary()
    {
        return 'flarum';
    }
}
