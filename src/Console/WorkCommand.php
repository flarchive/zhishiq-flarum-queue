<?php

namespace ZhiShiQ\Flarum\Queue\Console;

use Flarum\Foundation\Application;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Input\InputOption;

class WorkCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->addArgument('connection')
            ->addOption('once', null, InputOption::VALUE_NONE, 'Only process the next job on the queue')
            ->addOption('delay', null, InputOption::VALUE_OPTIONAL, 'The number of seconds to delay failed jobs', 0)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the worker to run even in maintenance mode')
            ->addOption('daemon', null, InputOption::VALUE_NONE, 'Force the worker to run even in maintenance mode')
            ->addOption('memory', null, InputOption::VALUE_OPTIONAL, 'The memory limit in megabytes', 128)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'The number of seconds a child process can run', 60)
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed}', 0)
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL,'The names of the queues to work')
            ->setDescription('Start processing jobs on the queue as a daemon');
    }

    /**
     * The queue worker instance.
     *
     * @var \Illuminate\Queue\Worker
     */
    protected $worker;

    public function __construct(Worker $worker, Application $app, $name = null)
    {
        parent::__construct($app, $name);

        $this->worker = $worker;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->downForMaintenance() && $this->option('once')) {
            return $this->worker->sleep($this->option('sleep'));
        }

        // We'll listen to the processed and failed events so we can write information
        // to the console as jobs are processed, which will let the developer watch
        // which jobs are coming through a queue and be informed on its progress.
        $this->listenForEvents();

        $connection = $this->argument('connection')
            ?: $this->app['config']['queue.default'];

        // We need to get the right queue for the connection which is set in the queue
        // configuration file for the application. We will pull it based on the set
        // connection being run for the queue operation currently being executed.
        $queue = $this->getQueue($connection);

        $this->runWorker(
            $connection, $queue
        );
    }

    /**
     * Run the worker instance.
     *
     * @param  string $connection
     * @param  string $queue
     * @return array
     */
    protected function runWorker($connection, $queue)
    {
        $this->worker->setCache($this->app['cache']->driver());

        return $this->worker->{$this->option('once') ? 'runNextJob' : 'daemon'}(
            $connection, $queue, $this->gatherWorkerOptions()
        );
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Illuminate\Queue\WorkerOptions
     */
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $this->option('delay'), $this->option('memory'),
            $this->option('timeout'), $this->option('sleep'),
            $this->option('tries'), $this->option('force')
        );
    }

    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    protected function listenForEvents()
    {
        $this->app['events']->listen(JobProcessing::class, function ($event) {
            $this->writeOutput($event->job, 'starting');
        });

        $this->app['events']->listen(JobProcessed::class, function ($event) {
            $this->writeOutput($event->job, 'success');
        });

        $this->app['events']->listen(JobFailed::class, function ($event) {
            $this->writeOutput($event->job, 'failed');

            $this->logFailedJob($event);
        });
    }

    /**
     * Write the status output for the queue worker.
     *
     * @param  \Illuminate\Contracts\Queue\Job $job
     * @param  string $status
     * @return void
     */
    protected function writeOutput(Job $job, $status)
    {
        switch ($status) {
            case 'starting':
                return $this->writeStatus($job, 'Processing', 'comment');
            case 'success':
                return $this->writeStatus($job, 'Processed', 'info');
            case 'failed':
                return $this->writeStatus($job, 'Failed', 'error');
        }
    }

    /**
     * Format the status output for the queue worker.
     *
     * @param  \Illuminate\Contracts\Queue\Job $job
     * @param  string $status
     * @param  string $type
     * @return void
     */
    protected function writeStatus(Job $job, $status, $type)
    {
        $this->output->writeln(sprintf(
            "<{$type}>[%s] %s</{$type}> %s",
            Carbon::now()->format('Y-m-d H:i:s'),
            str_pad("{$status}:", 11), $job->resolveName()
        ));
    }

    /**
     * Store a failed job event.
     *
     * @param  \Illuminate\Queue\Events\JobFailed $event
     * @return void
     */
    protected function logFailedJob(JobFailed $event)
    {
        $this->app['queue.failer']->log(
            $event->connectionName, $event->job->getQueue(),
            $event->job->getRawBody(), $event->exception
        );
    }

    /**
     * Get the queue name for the worker.
     *
     * @param  string $connection
     * @return string
     */
    protected function getQueue($connection)
    {
        return $this->option('queue') ?: $this->app['config']->get(
            "queue.connections.{$connection}.queue", 'default'
        );
    }

    /**
     * Determine if the worker should run in maintenance mode.
     *
     * @return bool
     */
    protected function downForMaintenance()
    {
        return $this->option('force') ? false : $this->app->isDownForMaintenance();
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->handle();
    }
}
