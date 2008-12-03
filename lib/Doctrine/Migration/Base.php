<?php
/*
 *  $Id: Migration.php 1080 2007-02-10 18:17:08Z jwage $
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
 * <http://www.phpdoctrine.org>.
 */

/**
 * Base migration class. All migration classes must extend from this base class
 *
 * @package     Doctrine
 * @subpackage  Migration
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.1
 * @version     $Revision: 1080 $
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class Doctrine_Migration_Base
{
    protected $_changes = array('created_tables'      =>  array(),
                                'renamed_tables'      =>  array(),
                                'created_constraints' =>  array(),
                                'added_columns'       =>  array(),
                                'added_indexes'       =>  array(),
                                'dropped_fks'         =>  array(),
                                'created_fks'         =>  array(),
                                'dropped_constraints' =>  array(),
                                'removed_indexes'     =>  array(),
                                'dropped_tables'      =>  array(),
                                'renamed_columns'     =>  array(),
                                'changed_columns'     =>  array(),
                                'removed_columns'     =>  array());

    protected static $_opposites = array('created_tables'       => 'dropped_tables',
                                         'dropped_tables'       => 'created_tables',
                                         'created_constraints'  => 'dropped_constraints',
                                         'dropped_constraints'  => 'created_constraints',
                                         'created_fks'          => 'dropped_fks',
                                         'dropped_fks'          => 'created_fks',
                                         'added_columns'        => 'removed_columns',
                                         'removed_columns'      => 'added_columns',
                                         'added_indexes'        => 'removed_indexes',
                                         'removed_indexes'      => 'added_indexes',
                                         );

    /**
     * Get the changes that have been added on this migration class instance
     *
     * @return array $changes
     */
    public function getChanges()
    {
        return $this->_changes;
    }

    public function getNumChanges()
    {
        return (count($this->_changes, true) - count($this->_changes));
    }

    /**
     * Add a change to the stack of changes to execute
     *
     * @param string $type    The type of change
     * @param array  $change   The array of information for the change 
     * @return void
     */
    protected function _addChange($type, array $change = array())
    {
        if (isset($change['upDown']) && $change['upDown'] !== null && isset(self::$_opposites[$type])) {
            if ($change['upDown'] == 'down') {
                $opposite = self::$_opposites[$type];
                return $this->_changes[$opposite][] = $change;
            }
            unset($change['upDown']);
        }
        return $this->_changes[$type][] = $change;
    }

    /**
     * Add a create or drop table change.
     *
     * @param string $upDown     Whether to add the up(create) or down(drop) table change.
     * @param string $tableName  Name of the table
     * @param array  $fields     Array of fields for table
     * @param array  $options    Array of options for the table
     * @return void
     */
    public function table($upDown, $tableName, array $fields = array(), array $options = array())
    {
        $options = get_defined_vars();

        $this->_addChange('created_tables', $options);
    }

    /**
     * Add a create table change.
     *
     * @param string $tableName  Name of the table
     * @param array  $fields     Array of fields for table
     * @param array  $options    Array of options for the table
     * @return void
     */
    public function createTable($tableName, array $fields = array(), array $options = array())
    {
        $this->table('up', $tableName, $fields, $options);
    }

    /**
     * Add a drop table change.
     *
     * @param string $tableName  Name of the table
     * @return void
     */
    public function dropTable($tableName)
    {
        $this->table('down', $tableName);
    }

    /**
     * Add a rename table change
     *
     * @param string $oldTableName      Name of the table to change
     * @param string $newTableName      Name to change the table to
     * @return void
     */
    public function renameTable($oldTableName, $newTableName)
    {
        $options = get_defined_vars();
        
        $this->_addChange('renamed_tables', $options);
    }

    /**
     * Add a create or drop constraint change.
     *
     * @param string $upDown            Whether to add the up(create) or down(drop) create change.
     * @param string $tableName         Name of the table.
     * @param string $constraintname    Name of the constraint.
     * @param array  $definition        Array for the constraint definition.
     * @return void
     */
    public function constraint($upDown, $tableName, $constraintname, array $definition)
    {
        $options = get_defined_vars();
        
        $this->_addChange('created_constraints', $options);
    }

    /**
     * Add a create constraint change.
     *
     * @param string $tableName         Name of the table.
     * @param string $constraintname    Name of the constraint.
     * @param array  $definition        Array for the constraint definition.
     * @return void
     */
    public function createConstraint($tableName, $constraintName, array $definition)
    {
        $this->constraint('up', $constraintName, $definition);
    }

    /**
     * Add a drop constraint change.
     *
     * @param string $tableName         Name of the table.
     * @param string $constraintname    Name of the constraint.
     * @return void
     */
    public function dropConstraint($tableName, $constraintName)
    {
        $this->constraint('down', $constraintName);
    }

    /**
     * Add a create or drop foreign key change.
     *
     * @param string $upDown        Whether to add the up(create) or down(drop) foreign key change.
     * @param string $tableName     Name of the table.
     * @param string $name          Name of the foreign key.
     * @param array  $definition    Array for the foreign key definition
     * @return void
     */
    public function foreignKey($upDown, $tableName, $name, array $definition = array())
    {
        $definition['name'] = $name;
        $options = get_defined_vars();

        $this->_addChange('created_fks', $options);
    }

    /**
     * Add a create foreign key change.
     *
     * @param string $tableName     Name of the table.
     * @param string $name          Name of the foreign key.
     * @param array  $definition    Array for the foreign key definition
     * @return void
     */
    public function createForeignKey($tableName, $name, array $definition)
    {
        $this->foreignKey('up', $tableName, $name, $definition);
    }

    /**
     * Add a drop foreign key change.
     *
     * @param string $tableName     Name of the table.
     * @param string $name          Name of the foreign key.
     * @return void
     */
    public function dropForeignKey($tableName, $name)
    {
        $this->foreignKey('down', $tableName, $name);
    }

    /**
     * Add a add or remove column change.
     *
     * @param string $upDown        Whether to add the up(add) or down(remove) column change.
     * @param string $tableName     Name of the table
     * @param string $columnName    Name of the column
     * @param string $type          Type of the column
     * @param string $length        Length of the column
     * @param array  $options       Array of options for the column
     * @return void
     */
    public function column($upDown, $tableName, $columnName, $type = null, $length = null, array $options = array())
    {
        $options = get_defined_vars();
        $options['options']['length'] = $length;
        $options = array_merge($options, $options['options']);
        unset($options['options']);

        $this->_addChange('added_columns', $options);
    }

    /**
     * Add a add column change.
     *
     * @param string $tableName     Name of the table
     * @param string $columnName    Name of the column
     * @param string $type          Type of the column
     * @param string $length        Length of the column
     * @param array  $options       Array of options for the column
     * @return void
     */
    public function addColumn($tableName, $columnName, $type, $length = null, array $options = array())
    {
        $this->column('up', $tableName, $columnName, $type, $length, $options);
    }

    /**
     * Add a remove column change.
     *
     * @param string $tableName     Name of the table
     * @param string $columnName    Name of the column
     * @return void
     */
    public function removeColumn($tableName, $columnName)
    {
        $this->column('down', $tableName, $columnName);
    }

    /**
     * Add a rename column change
     *
     * @param string $tableName         Name of the table to rename the column on
     * @param string $oldColumnName     The old column name
     * @param string $newColumnName     The new column name
     * @return void
     */
    public function renameColumn($tableName, $oldColumnName, $newColumnName)
    {
        $options = get_defined_vars();
        
        $this->_addChange('renamed_columns', $options);
    }

    /**
     * Add a change column change
     *
     * @param string $tableName     Name of the table to change the column on
     * @param string $columnName    Name of the column to change
     * @param string $type          New type of column
     * @param array  $options       New options for the column
     * @return void
     */
    public function changeColumn($tableName, $columnName, $length = null, $type = null, array $options = array())
    {
        $options = get_defined_vars();
        $options['options']['length'] = $length;

        $this->_addChange('changed_columns', $options);
    }

    /**
     * Add a add or remove index change.
     *
     * @param string $upDown       Whether to add the up(add) or down(remove) index change.
     * @param string $tableName    Name of the table
     * @param string $indexName    Name of the index
     * @param array  $definition   Array for the index definition
     * @return void
     */
    public function index($upDown, $tableName, $indexName, array $definition = array())
    {
        $options = get_defined_vars();
        
        $this->_addChange('added_indexes', $options);
    }

    /**
     * Add a add index change.
     *
     * @param string $tableName    Name of the table
     * @param string $indexName    Name of the index
     * @param array  $definition   Array for the index definition
     * @return void
     */
    public function addIndex($tableName, $indexName, array $definition)
    {
        $this->index('up', $tableName, $indexName, $definition);
    }

    /**
     * Add a remove index change.
     *
     * @param string $tableName    Name of the table
     * @param string $indexName    Name of the index
     * @return void
     */
    public function removeIndex($tableName, $indexName)
    {
        $this->index('down', $tableName, $indexName);
    }

    public function preUp()
    {
    }

    public function postUp()
    {
    }

    public function preDown()
    {
    }

    public function postDown()
    {
    }
}