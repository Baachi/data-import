<?php

namespace Ddeboer\DataImport\Step;
use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ValueConverterStep implements StepInterface
{
    protected $converters = [];

    public function add($property, ValueConverterInterface $converter)
    {
        if (!isset($this->converters[$property])) {
            $this->converters[$property] = new \SplObjectStorage();
        }

        $this->converters[$property]->attach($converter);

        return $this;
    }

    public function process(&$item)
    {
        $accessor = new PropertyAccessor();
        foreach ($this->converters as $property => $converters) {
            foreach ($converters as $converter) {
                if(isset($item[$property])){
                    $item[$property] = $converter->convert($item[$property]);
                }
                else
                {
                    $orgValue = $accessor->getValue($item, $property);
                    $value = $converter->convert($orgValue);
                    $accessor->setValue($item,$property,$value);
                }
            }
        }

        return true;
    }
} 
