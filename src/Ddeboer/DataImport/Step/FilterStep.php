<?php

namespace Ddeboer\DataImport\Step;
use Ddeboer\DataImport\Filter\FilterInterface;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FilterStep implements StepInterface
{
    /**
     * @var \SplPriorityQueue
     */
    protected $filters;

    public function __construct()
    {
        $this->filters = new \SplPriorityQueue();
    }

    public function add(FilterInterface $filter, $priority = null)
    {
        $priority = $priority !== null ? $priority : $filter->getPriority();

        $this->filters->insert($filter, $priority);

        return $this;
    }

    public function process(&$item)
    {
        foreach (clone $this->filters as $filter) {
            if (false === $filter->filter($item)) {
                return false;
            }
        }

        return true;
    }
} 