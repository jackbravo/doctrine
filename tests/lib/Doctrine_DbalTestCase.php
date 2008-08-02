<?php
/**
 * Base testcase class for all dbal testcases.
 */
class Doctrine_DbalTestCase extends Doctrine_TestCase
{
    protected $_conn;
    
    /**
     * setUp()
     *
     * Note: This setUp() and the one of OrmTestCase currently look identical. However,
     * please dont pull this method up. In the future with a separation of Dbal/Orm
     * this setUp() will take care of a DBAL connection and the ORM setUp() will take care
     * of an ORM connection/session/manager.
     */
    protected function setUp()
    {
        // Setup a db connection if there is none, yet. This makes it possible
        // to run tests that use a connection standalone.
        if (isset($this->sharedFixture['conn'])) {
            $this->_conn = $this->sharedFixture['conn'];
        } else {
            $this->sharedFixture['conn'] = Doctrine_TestUtil::getConnection();
            $this->_conn = $this->sharedFixture['conn'];
        }
    }
}