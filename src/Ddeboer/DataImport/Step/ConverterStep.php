<?php

namespace Ddeboer\DataImport\Step;
use Ddeboer\DataImport\Exception\UnexpectedTypeException;
use Ddeboer\DataImport\ItemConverter\ItemConverterInterface;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ConverterStep implements StepInterface
{
    private $converters;

    public function __construct(array $converters = [])
    {
        $this->converters = new \SplObjectStorage($converters);
    }

    public function add(ItemConverterInterface $converter)
    {
        $this->converters->attach($converter);
    }

    public function clear()
    {
        $this->converters = new \SplObjectStorage();
    }

    public function process(&$item)
    {
        foreach ($this->converters as $converter) {
            $converter->convert($item);
        }

        if ($item && !(is_array($item) || ($item instanceof \ArrayAccess && $item instanceof \Traversable))) {
            throw new UnexpectedTypeException($item, 'false or array');
        }

        return true;
    }
} 