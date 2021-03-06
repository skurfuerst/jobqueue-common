<?php
namespace Flowpack\JobQueue\Common\Command;

/*
 * This file is part of the Flowpack.JobQueue.Common package.
 *
 * (c) Contributors to the package
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use Flowpack\JobQueue\Common\Exception as JobQueueException;
use Flowpack\JobQueue\Common\Job\JobManager;
use Flowpack\JobQueue\Common\Queue\QueueManager;

/**
 * Job command controller
 */
class JobCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var JobManager
     */
    protected $jobManager;

    /**
     * @Flow\Inject
     * @var QueueManager
     */
    protected $queueManager;

    /**
     * Work on a queue and execute jobs
     *
     * @param string $queueName The name of the queue
     * @return void
     */
    public function workCommand($queueName)
    {
        do {
            try {
                $this->jobManager->waitAndExecute($queueName);
            } catch (JobQueueException $exception) {
                $this->outputLine($exception->getMessage());
                if ($exception->getPrevious() instanceof \Exception) {
                    $this->outputLine($exception->getPrevious()->getMessage());
                }
            } catch (\Exception $exception) {
                $this->outputLine('Unexpected exception during job execution: %s', array($exception->getMessage()));
            }
        } while (true);
    }

    /**
     * List queued jobs
     *
     * @param string $queueName The name of the queue
     * @param integer $limit Number of jobs to list
     * @return void
     */
    public function listCommand($queueName, $limit = 1)
    {
        $jobs = $this->jobManager->peek($queueName, $limit);
        $totalCount = $this->queueManager->getQueue($queueName)->count();
        foreach ($jobs as $job) {
            $this->outputLine('<u>%s</u>', array($job->getLabel()));
        }

        if ($totalCount > count($jobs)) {
            $this->outputLine('(%d omitted) ...', array($totalCount - count($jobs)));
        }
        $this->outputLine('(<b>%d total</b>)', array($totalCount));
    }
}
