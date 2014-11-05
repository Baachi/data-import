<?php

namespace Ddeboer\DataImport;

use Ddeboer\DataImport\Exception\ExceptionInterface;
use Ddeboer\DataImport\Reader\ReaderInterface;
use DateTime;
use Ddeboer\DataImport\Step\PriorityStepInterface;
use Ddeboer\DataImport\Step\StepInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * A mediator between a reader and one or more writers and converters
 *
 * @author David de Boer <david@ddeboer.nl>
 */
final class Workflow implements WorkflowInterface
{
    /**
     * Reader
     *
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * Identifier for the Import/Export
     *
     * @var string|null
     */
    protected $name = null;

    /**
     * @var boolean
     */
    protected $skipItemOnFailure = false;

    protected $logger;

    /**
     * @var array
     */
    protected $steps = [];

    /**
     * Construct a workflow
     *
     * @param ReaderInterface $reader
     * @param string $name
     */
    public function __construct(ReaderInterface $reader, LoggerInterface $logger = null, $name = null)
    {
        $this->name = $name;
        $this->logger = $logger ?: new NullLogger();
        $this->reader = $reader;
    }

    public function addStep(StepInterface $step, $priority = null)
    {
        $priority = null === $priority && $step instanceof PriorityStepInterface ? $step->getPriority() : null;
        $priority = null === $priority ? 0 : $priority;

        if (!isset($this->steps[$priority])) {
            $this->steps[$priority] = new \SplObjectStorage();
        }

        $this->steps[$priority]->attach($step);
    }

    /**
     * Process the whole import workflow
     *
     * 1. Prepare the added writers.
     * 2. Ask the reader for one item at a time.
     * 3. Filter each item.
     * 4. If the filter succeeds, convert the item’s values using the added
     *    converters.
     * 5. Write the item to each of the writers.
     *
     * @throws ExceptionInterface
     * @return Result Object Containing Workflow Results
     */
    public function process()
    {
        $count      = 0;
        $exceptions = array();
        $startTime  = new DateTime;
        $steps      = $this->getOrderedSteps();

        // Read all items
        foreach ($this->reader as $rowIndex => $item) {
            try {
                foreach ($steps as $step) {
                    if (!$step->process($item)) {
                        continue;
                    }
                }
            } catch(ExceptionInterface $e) {
                if ($this->skipItemOnFailure) {
                    $exceptions[$rowIndex] = $e;
                    $this->logger->error($e->getMessage());
                } else {
                    throw $e;
                }
            }

            $count++;
        }

        return new Result($this->name, $startTime, new DateTime, $count, $exceptions);
    }

    /**
     * Set skipItemOnFailure.
     *
     * @param boolean $skipItemOnFailure then true skip current item on process exception and log the error
     *
     * @return $this
     */
    public function setSkipItemOnFailure($skipItemOnFailure)
    {
        $this->skipItemOnFailure = $skipItemOnFailure;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    private function getOrderedSteps()
    {
        $tmp = $this->steps;
        $steps = [];

        ksort($tmp);

        foreach ($tmp as $_steps) {
            foreach ($_steps as $step) {
                array_push($steps, $step);
            }
        }

        return $steps;
    }
}
