<?php

namespace ZhiShiQ\Flarum\Queue\Console;

use Flarum\Foundation\Application;
use Illuminate\Queue\Listener;
use Illuminate\Queue\ListenerOptions;
use Symfony\Component\Console\Input\InputOption;

class ListenCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('queue:listen')
            ->addArgument('connection')
            ->addOption('delay', null, InputOption::VALUE_OPTIONAL, 'The number of seconds to delay failed jobs', 0)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the worker to run even in maintenance mode')
            ->addOption('memory', null, InputOption::VALUE_OPTIONAL, 'The memory limit in megabytes', 128)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'The number of seconds a child process can run', 60)
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed}', 0)
            ->addOption('env', null, InputOption::VALUE_OPTIONAL)
            ->addOption('queue', null, InputOption::VALUE_REQUIRED)
            ->setDescription('Listen to a given queue');
    }

    /**
     * The queue listener instance.
     *
     * @var \Illuminate\Queue\Listener
     */
    protected $listener;

    public function __construct(Listener $listener, Application $app, $name = null)
    {
        parent::__construct($app, $name);

        $this->setOutputHandler($this->listener = $listener);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // We need to get the right queue for the connection which is set in the queue
        // configuration file for the application. We will pull it based on the set
        // connection being run for the queue operation currently being executed.
        $queue = $this->getQueue(
            $connection = $this->input->getArgument('connection')
        );

        $this->listener->listen(
            $connection, $queue, $this->gatherOptions()
        );
    }

    /**
     * Get the name of the queue connection to listen on.
     *
     * @param  string $connection
     * @return string
     */
    protected function getQueue($connection)
    {
        $connection = $connection ?: $this->app['config']['queue.default'];

        return $this->input->getOption('queue') ?: $this->app['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );
    }

    /**
     * Get the listener options for the command.
     *
     * @return \Illuminate\Queue\ListenerOptions
     */
    protected function gatherOptions()
    {
        return new ListenerOptions(
            $this->option('env'), $this->option('delay'),
            $this->option('memory'), $this->option('timeout'),
            $this->option('sleep'), $this->option('tries'),
            $this->option('force')
        );
    }

    /**
     * Set the options on the queue listener.
     *
     * @param  \Illuminate\Queue\Listener $listener
     * @return void
     */
    protected function setOutputHandler(Listener $listener)
    {
        $listener->setOutputHandler(function ($type, $line) {
            $this->output->write($line);
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->handle();
    }
}
