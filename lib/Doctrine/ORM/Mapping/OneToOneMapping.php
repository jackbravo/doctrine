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
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Mapping;

/**
 * A one-to-one mapping describes a uni-directional mapping from one entity 
 * to another entity.
 *
 * <b>IMPORTANT NOTE:</b>
 *
 * The fields of this class are only public for 2 reasons:
 * 1) To allow fast, internal READ access.
 * 2) To drastically reduce the size of a serialized instance (private/protected members
 *    get the whole class name, namespace inclusive, prepended to every property in
 *    the serialized representation).
 *
 * @since 2.0
 * @author Roman Borschel <roman@code-factory.org>
 */
class OneToOneMapping extends AssociationMapping
{
    /**
     * Maps the source foreign/primary key columns to the target primary/foreign key columns.
     * i.e. source.id (pk) => target.user_id (fk).
     * Reverse mapping of _targetToSourceKeyColumns.
     */
    public $sourceToTargetKeyColumns = array();

    /**
     * Maps the target primary/foreign key columns to the source foreign/primary key columns.
     * i.e. target.user_id (fk) => source.id (pk).
     * Reverse mapping of _sourceToTargetKeyColumns.
     */
    public $targetToSourceKeyColumns = array();
    
    /**
     * Whether to delete orphaned elements (when nulled out, i.e. $foo->other = null)
     * 
     * @var boolean
     */
    public $deleteOrphans = false;

    /**
     * The join column definitions.
     *
     * @var array
     */
    public $joinColumns = array();
    
    /**
     * Creates a new OneToOneMapping.
     *
     * @param array $mapping  The mapping info.
     */
    public function __construct(array $mapping)
    {
        parent::__construct($mapping);
    }
    
    /**
     * {@inheritdoc}
     *
     * @param array $mapping  The mapping to validate & complete.
     * @return array  The validated & completed mapping.
     * @override
     */
    protected function _validateAndCompleteMapping(array $mapping)
    {
        parent::_validateAndCompleteMapping($mapping);
        
        if ($this->isOwningSide()) {
            if ( ! isset($mapping['joinColumns'])) {
                throw MappingException::invalidMapping($this->_sourceFieldName);
            }
            $this->joinColumns = $mapping['joinColumns'];
            foreach ($mapping['joinColumns'] as $joinColumn) {
                $this->sourceToTargetKeyColumns[$joinColumn['name']] = $joinColumn['referencedColumnName'];
            }
            $this->targetToSourceKeyColumns = array_flip($this->sourceToTargetKeyColumns);
        }
        
        $this->deleteOrphans = isset($mapping['deleteOrphans']) ?
                (bool)$mapping['deleteOrphans'] : false;
        
        return $mapping;
    }

    /**
     * Gets the join column definitions for this mapping.
     *
     * @return array
     */
    public function getJoinColumns()
    {
        return $this->joinColumns;
    }
    
    /**
     * Gets the source-to-target key column mapping.
     *
     * @return array
     */
    public function getSourceToTargetKeyColumns()
    {
        return $this->sourceToTargetKeyColumns;
    }
    
    /**
     * Gets the target-to-source key column mapping.
     *
     * @return array
     */
    public function getTargetToSourceKeyColumns()
    {
        return $this->targetToSourceKeyColumns;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return boolean
     * @override
     */
    public function isOneToOne()
    {
        return true;
    }
    
    /**
     * {@inheritdoc}
     *
     * @param object $owningEntity
     * @param object $targetEntity
     * @param EntityManager $em
     */
    public function load($owningEntity, $targetEntity, $em)
    {
        $sourceClass = $em->getClassMetadata($this->sourceEntityName);
        $targetClass = $em->getClassMetadata($this->targetEntityName);
        
        $conditions = array();

        if ($this->isOwningSide) {
            foreach ($this->sourceToTargetKeyColumns as $sourceKeyColumn => $targetKeyColumn) {
                $conditions[$targetKeyColumn] = $sourceClass->getReflectionProperty(
                    $sourceClass->getFieldName($sourceKeyColumn))->getValue($owningEntity);
            }
            if ($targetClass->hasInverseAssociation($this->sourceFieldName)) {
                $targetClass->setFieldValue(
                        $targetEntity,
                        $targetClass->inverseMappings[$this->_sourceFieldName]->getSourceFieldName(),
                        $owningEntity);
            }
        } else {
            $owningAssoc = $em->getClassMetadata($this->targetEntityName)->getAssociationMapping($this->mappedByFieldName);
            foreach ($owningAssoc->getTargetToSourceKeyColumns() as $targetKeyColumn => $sourceKeyColumn) {
                $conditions[$sourceKeyColumn] = $sourceClass->getReflectionProperty(
                    $sourceClass->getFieldName($targetKeyColumn))->getValue($owningEntity);
            }
            $targetClass->setFieldValue($targetEntity, $this->mappedByFieldName, $owningEntity);
        }

        $em->getUnitOfWork()->getEntityPersister($this->targetEntityName)->load($conditions, $targetEntity);
    }
}