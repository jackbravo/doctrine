<?php
/* 
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.com>.
 */
/**
 * 
 * Doctrine_Manager is the base component of all doctrine based projects. 
 * It opens and keeps track of all connections (database connections).
 *
 * @package     Doctrine
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @category    Object Relational Mapping
 * @link        www.phpdoctrine.com
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Manager extends Doctrine_Configurable implements Countable, IteratorAggregate {
    /**
     * @var array $connections      an array containing all the opened connections
     */
    private $connections   = array();
    /**
     * @var array $bound            an array containing all components that have a bound connection
     */
    private $bound          = array();
    /**
     * @var integer $index          the incremented index
     */
    private $index      = 0;
    /**
     * @var integer $currIndex      the current connection index
     */
    private $currIndex  = 0;
    /**
     * @var string $root            root directory
     */
    private $root;
    /**
     * @var Doctrine_Null $null     Doctrine_Null object, used for extremely fast null value checking
     */
    private $null;

    /**
     * constructor
     *
     * this is private constructor (use getInstance to get an instance of this class)
     */
    private function __construct() {
        $this->root = dirname(__FILE__);
        $this->null = new Doctrine_Null;

        Doctrine_Record::initNullObject($this->null);
        Doctrine_Collection::initNullObject($this->null);
        Doctrine_Record_Iterator::initNullObject($this->null);
        Doctrine_Validator::initNullObject($this->null);
    }
    /**
     * @return Doctrine_Null
     */
    final public function getNullObject() {
        return $this->null;
    }
    /**
     * setDefaultAttributes
     * sets default attributes
     *
     * @return boolean
     */
    final public function setDefaultAttributes() {
        static $init = false;
        if( ! $init) {
            $init = true;
            $attributes = array(
                        Doctrine::ATTR_FETCHMODE        => Doctrine::FETCH_IMMEDIATE,
                        Doctrine::ATTR_BATCH_SIZE       => 5,
                        Doctrine::ATTR_COLL_LIMIT       => 5,
                        Doctrine::ATTR_LISTENER         => new Doctrine_EventListener(),
                        Doctrine::ATTR_LOCKMODE         => 1,
                        Doctrine::ATTR_VLD              => false,
                        Doctrine::ATTR_AUTO_LENGTH_VLD  => true,
                        Doctrine::ATTR_AUTO_TYPE_VLD    => true,
                        Doctrine::ATTR_CREATE_TABLES    => true,
                        Doctrine::ATTR_QUERY_LIMIT      => Doctrine::LIMIT_RECORDS,
                        Doctrine::ATTR_IDXNAME_FORMAT   => "%s_idx",
                        Doctrine::ATTR_SEQNAME_FORMAT   => "%s_seq",
                        Doctrine::ATTR_QUOTE_IDENTIFIER => false,
                        Doctrine::ATTR_SEQCOL_NAME      => 'id',
                        Doctrine::ATTR_PORTABILITY      => Doctrine::PORTABILITY_ALL,
                        );
            foreach($attributes as $attribute => $value) {
                $old = $this->getAttribute($attribute);
                if($old === null)
                    $this->setAttribute($attribute,$value);
            }
            return true;
        }
        return false;
    }
    /**
     * returns the root directory of Doctrine
     *
     * @return string
     */
    final public function getRoot() {
        return $this->root;
    }
    /**
     * getInstance                  
     * returns an instance of this class
     * (this class uses the singleton pattern)
     *
     * @return Doctrine_Manager
     */
    public static function getInstance() {
        static $instance;
        if( ! isset($instance))
            $instance = new self();

        return $instance;
    }
    /**
     * connection
     * a short cut for Doctrine_Manager::getInstance()->openConnection($dbh);
     *
     * @param PDO|Doctrine_Adapter_Interface $adapter   database driver
     * @param string $name                              name of the connection, if empty numeric key is used
     * @throws Doctrine_Manager_Exception               if trying to bind a connection with an existing name
     * @return Doctrine_Connection
     */
    public static function connection($adapter, $name = null) {
        return Doctrine_Manager::getInstance()->openConnection($dbh);
    }
    /**
     * openConnection
     * opens a new connection and saves it to Doctrine_Manager->connections
     *
     * @param PDO|Doctrine_Adapter_Interface $adapter   database driver
     * @param string $name                              name of the connection, if empty numeric key is used
     * @throws Doctrine_Manager_Exception               if trying to bind a connection with an existing name
     * @return Doctrine_Connection
     */
    public function openConnection($adapter, $name = null) {
        if( ! ($adapter instanceof PDO) && ! in_array('Doctrine_Adapter_Interface', class_implements($adapter)))
            throw new Doctrine_Manager_Exception("First argument should be an instance of PDO or implement Doctrine_Adapter_Interface");


        // initialize the default attributes
        $this->setDefaultAttributes();

        if($name !== null) {
            $name = (string) $name;
            if(isset($this->connections[$name]))
                throw new Doctrine_Manager_Exception("Connection with $name already exists!");

        } else {
            $name = $this->index;
            $this->index++;
        }
        switch($adapter->getAttribute(PDO::ATTR_DRIVER_NAME)):
            case "mysql":
                $this->connections[$name] = new Doctrine_Connection_Mysql($this, $adapter);
            break;
            case "sqlite":
                $this->connections[$name] = new Doctrine_Connection_Sqlite($this, $adapter);
            break;
            case "pgsql":
                $this->connections[$name] = new Doctrine_Connection_Pgsql($this, $adapter);
            break;
            case "oci":
                $this->connections[$name] = new Doctrine_Connection_Oracle($this, $adapter);
            break;
            case "mssql":
                $this->connections[$name] = new Doctrine_Connection_Mssql($this, $adapter);
            break;
            case "firebird":
                $this->connections[$name] = new Doctrine_Connection_Firebird($this, $adapter);
            break;
            case "informix":
                $this->connections[$name] = new Doctrine_Connection_Informix($this, $adapter);
            break;
            default:
                throw new Doctrine_Manager_Exception('Unknown connection driver '. $adapter->getAttribute(PDO::ATTR_DRIVER_NAME));
        endswitch;


        $this->currIndex = $name;
        return $this->connections[$name];
    }
    public function openSession(PDO $pdo, $name = null) {
        return $this->openConnection($pdo, $name);
    }
    /**
     * getConnection
     * @param integer $index
     * @return object Doctrine_Connection
     * @throws Doctrine_Manager_Exception   if trying to get a non-existent connection
     */
    public function getConnection($name) {   
        if( ! isset($this->connections[$name]))
            throw new Doctrine_Manager_Exception('Unknown connection: ' . $name);

        $this->currIndex = $name;
        return $this->connections[$name];
    }
    /**
     * bindComponent
     * binds given component to given connection
     * this means that when ever the given component uses a connection
     * it will be using the bound connection instead of the current connection
     *
     * @param string $componentName
     * @param string $connectionName
     * @return boolean
     */
    public function bindComponent($componentName, $connectionName) {
        $this->bound[$componentName] = $connectionName;
    }
    /**
     * getConnectionForComponent
     *
     * @param string $componentName
     * @return Doctrine_Connection
     */
    public function getConnectionForComponent($componentName = null) {
        if(isset($this->bound[$componentName]))
            return $this->getConnection($this->bound[$componentName]);

        return $this->getCurrentConnection();
    }  
    /**
     * closes the connection
     *
     * @param Doctrine_Connection $connection
     * @return void
     */
    public function closeConnection(Doctrine_Connection $connection) {
        $connection->close();
        unset($connection);
    }
    /**
     * getConnections
     * returns all opened connections
     *
     * @return array
     */
    public function getConnections() {
        return $this->connections;
    }
    /**
     * setCurrentConnection
     * sets the current connection to $key
     *
     * @param mixed $key                        the connection key
     * @throws InvalidKeyException
     * @return void
     */
    public function setCurrentConnection($key) {
        $key = (string) $key;
        if( ! isset($this->connections[$key]))
            throw new InvalidKeyException();
        
        $this->currIndex = $key;
    }
    /**
     * count
     * returns the number of opened connections
     *
     * @return integer
     */
    public function count() {
        return count($this->connections);
    }
    /**
     * getIterator
     * returns an ArrayIterator that iterates through all connections
     *
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator($this->connections);
    }
    /**
     * getCurrentConnection
     * returns the current connection
     *
     * @throws Doctrine_Connection_Exception       if there are no open connections
     * @return Doctrine_Connection
     */
    public function getCurrentConnection() {
        $i = $this->currIndex;
        if( ! isset($this->connections[$i]))
            throw new Doctrine_Connection_Exception();

        return $this->connections[$i];
    }
    /**
     * __toString
     * returns a string representation of this object
     *
     * @return string
     */
    public function __toString() {
        $r[] = "<pre>";
        $r[] = "Doctrine_Manager";
        $r[] = "Connections : ".count($this->connections);
        $r[] = "</pre>";
        return implode("\n",$r);
    }
}

