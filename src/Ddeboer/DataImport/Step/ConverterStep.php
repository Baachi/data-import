<?php

namespace Ddeboer\DataImport\Step;
use Ddeboer\DataImport\Exception\UnexpectedTypeException;
use Ddeboer\DataImport\ItemConverter\ItemConverterInterface;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ConverterStep implements StepInterface
{
    protected $converters;

    public function __construct(array $converters = [])
    {
        $this->converters = new \SplObjectStorage();

        foreach ($converters as $converter) {
            $this->add($converter);
        }
    }

    public function add(ItemConverterInterface $converter)
    {
        $this->converters->attach($converter);

        return $this;
    }

    public function process(&$item)
    {
        foreach ($this->converters as $converter) {
            $item = $converter->convert($item);
        }

        return true;
    }
} 