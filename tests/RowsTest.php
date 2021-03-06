<?php

require dirname(__FILE__) . '/test.inc.php';

class RowsTest extends PHPUnit_Framework_TestCase
{

    public function testRowsIteration()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $zipCodeTotal = 0;

        foreach ($dp->getRows() as $row) {
            $zipCodeTotal += (int) $row['zipcode'];
        }

        $this->assertEquals(148551,$zipCodeTotal);

    }

    public function testIterateTwice()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $zipCodeTotal = 0;

        $rows = $dp->getRows();

        foreach ($rows as $row) {
            $zipCodeTotal += (int) $row['zipcode'];
        }

        foreach ($rows as $row) {
            $zipCodeTotal += (int) $row['zipcode'];
        }

        $this->assertEquals(148551 * 2,$zipCodeTotal);

    }

    public function testIterateNTimes()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $zipCodeTotal = 0;

        $rows = $dp->getRows();

        for ($i = 0; $i < 100; $i++) {
            foreach ($rows as $row) {
                $zipCodeTotal += (int) $row['zipcode'];
            }

        }

        $this->assertEquals(148551 * 100,$zipCodeTotal);

    }

}
