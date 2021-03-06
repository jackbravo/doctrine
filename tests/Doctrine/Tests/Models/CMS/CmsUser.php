<?php

namespace Doctrine\Tests\Models\CMS;

/**
 * @DoctrineEntity
 * @DoctrineTable(name="cms_users")
 */
class CmsUser
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
    public $status;
    /**
     * @DoctrineColumn(type="string", length=255)
     */
    public $username;
    /**
     * @DoctrineColumn(type="string", length=255)
     */
    public $name;
    /**
     * @DoctrineOneToMany(targetEntity="CmsPhonenumber", mappedBy="user", cascade={"save", "delete"})
     */
    public $phonenumbers;
    /**
     * @DoctrineOneToMany(targetEntity="CmsArticle", mappedBy="user")
     */
    public $articles;
    /**
     * @DoctrineOneToOne(targetEntity="CmsAddress", mappedBy="user", cascade={"save"})
     */
    public $address;
    /**
     * @DoctrineManyToMany(targetEntity="CmsGroup", cascade={"save"})
     * @DoctrineJoinTable(name="cms_users_groups",
            joinColumns={{"name"="user_id", "referencedColumnName"="id"}},
            inverseJoinColumns={{"name"="group_id", "referencedColumnName"="id"}})
     */
    public $groups;

    public function getId() {
        return $this->id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getName() {
        return $this->name;
    }

    /**
     * Adds a phonenumber to the user.
     *
     * @param CmsPhonenumber $phone
     */
    public function addPhonenumber(CmsPhonenumber $phone) {
        $this->phonenumbers[] = $phone;
        if ($phone->user !== $this) {
            $phone->user = $this;
        }
    }

    public function getPhonenumbers() {
        return $this->phonenumbers;
    }

    public function addArticle(CmsArticle $article) {
        $this->articles[] = $article;
        if ($article->user !== $this) {
            $article->user = $this;
        }
    }

    public function addGroup(CmsGroup $group) {
        $this->groups[] = $group;
        $group->addUser($this);
    }

    public function getGroups() {
        return $this->groups;
    }

    public function removePhonenumber($index) {
        if (isset($this->phonenumbers[$index])) {
            $ph = $this->phonenumbers[$index];
            unset($this->phonenumbers[$index]);
            $ph->user = null;
            return true;
        }
        return false;
    }
}
