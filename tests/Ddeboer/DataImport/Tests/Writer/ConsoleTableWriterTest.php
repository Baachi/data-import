<?php

namespace Ddeboer\DataImport\Tests\Writer;

use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Helper\Table;

use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Reader\ArrayReader;
use Ddeboer\DataImport\Writer\ConsoleTableWriter;

/**
 *  @author Igor Mukhin <igor.mukhin@gmail.com>
 */
class ConsoleTableWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testRightColumnsHeadersNamesAfterItemConverter()
    {
        $data = array(
            array(
                'first'  => 'John',
                'lastname' => 'Doe'
            ),
            array(
                'first'  => 'Ivan',
                'lastname' => 'Sidorov'
            )
        );
        $reader = new ArrayReader($data);

        $output = new BufferedOutput();
        $table = new Table($output);
        $table
            ->setStyle('compact')
        ;

        $workflow = new Workflow($reader);
        $workflow
            ->addStep(new \Ddeboer\DataImport\Step\MappingStep(array('[first]'=>'[firstname]')))
            ->addWriter(new ConsoleTableWriter($output, $table))
            ->process()
        ;

        $this->assertRegExp('/\s+lastname\s+firstname\s+Doe\s+John\s+Sidorov\s+Ivan\s+/', $output->fetch());
    }
}
