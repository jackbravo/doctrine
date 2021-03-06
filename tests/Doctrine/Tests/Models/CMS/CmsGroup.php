<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Doctrine\Tests\Models\CMS;

/**
 * Description of CmsGroup
 *
 * @author robo
 * @DoctrineEntity
 * @DoctrineTable(name="cms_groups")
 */
class CmsGroup
{
    /**
     * @DoctrineId
     * @DoctrineColumn(type="integer")
     * @DoctrineGeneratedValue(strategy="auto")
     */
    public $id;
    /**
     * @DoctrineColumn(type="string", length=50)
     */
    public $name;
    /**
     * @DoctrineManyToMany(targetEntity="CmsUser", mappedBy="groups")
     */
    public $users;

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function addUser(CmsUser $user) {
        $this->users[] = $user;
    }

    public function getUsers() {
        return $this->users;
    }
}

