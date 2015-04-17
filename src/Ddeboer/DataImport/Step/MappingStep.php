<?php

namespace Ddeboer\DataImport\Step;

use \Ddeboer\DataImport\Exception\MappingException;
use \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use \Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use \Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class MappingStep implements StepInterface
{
    protected $mappings;

    public function __construct(array $mappings = [], PropertyAccessor $accessor = null)
    {
        $this->mappings = $mappings;
        $this->accessor = $accessor ?: new PropertyAccessor();
    }

    public function map($from, $to)
    {
        $this->mappings[$from] = $to;

        return $this;
    }

    public function process(&$item)
    {
        try {
            foreach ($this->mappings as $from => $to) {
                $value = $this->accessor->getValue($item, $from);
                $this->accessor->setValue($item, $to, $value);
                $strFrom = str_replace(array('[',']'), array('',''), $from);
                if(isset($item[$strFrom])){
                    unset($item[$strFrom]);
                }
            }
        }catch(NoSuchPropertyException $exception){
            throw new MappingException('Unable to map item',null,$exception);
        }
        catch(UnexpectedTypeException $exception){
            throw new MappingException('Unable to map item',null,$exception);
        }
    }
} 