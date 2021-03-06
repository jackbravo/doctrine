<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\Tests\Models\CMS\CmsPhonenumber;
use Doctrine\Tests\Models\CMS\CmsAddress;
use Doctrine\Tests\Models\CMS\CmsGroup;

require_once __DIR__ . '/../../TestInit.php';

class BasicFunctionalTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp()
    {
        $this->useModelSet('cms');
        parent::setUp();
    }

    public function testBasicUnitsOfWorkWithOneToManyAssociation()
    {
        // Create
        $user = new CmsUser;
        $user->name = 'Roman';
        $user->username = 'romanb';
        $user->status = 'developer';
        $this->_em->save($user);
        $this->assertTrue(is_numeric($user->id));
        $this->assertTrue($this->_em->contains($user));

        // Read
        $user2 = $this->_em->find('Doctrine\Tests\Models\CMS\CmsUser', $user->id);
        $this->assertTrue($user === $user2);

        // Add a phonenumber
        $ph = new CmsPhonenumber;
        $ph->phonenumber = "12345";
        $user->addPhonenumber($ph);
        $this->_em->flush();
        $this->assertTrue($this->_em->contains($ph));
        $this->assertTrue($this->_em->contains($user));
        //$this->assertTrue($user->phonenumbers instanceof \Doctrine\ORM\PersistentCollection);

        // Update name
        $user->name = 'guilherme';
        $this->_em->flush();
        $this->assertEquals('guilherme', $user->name);

        // Add another phonenumber
        $ph2 = new CmsPhonenumber;
        $ph2->phonenumber = "6789";
        $user->addPhonenumber($ph2);
        $this->_em->flush();
        $this->assertTrue($this->_em->contains($ph2));

        // Delete
        $this->_em->delete($user);
        $this->assertTrue($this->_em->getUnitOfWork()->isRegisteredRemoved($user));
        $this->assertTrue($this->_em->getUnitOfWork()->isRegisteredRemoved($ph));
        $this->assertTrue($this->_em->getUnitOfWork()->isRegisteredRemoved($ph2));
        $this->_em->flush();
        $this->assertFalse($this->_em->getUnitOfWork()->isRegisteredRemoved($user));
        $this->assertFalse($this->_em->getUnitOfWork()->isRegisteredRemoved($ph));
        $this->assertFalse($this->_em->getUnitOfWork()->isRegisteredRemoved($ph2));
    }

    public function testOneToManyAssociationModification()
    {
        $user = new CmsUser;
        $user->name = 'Roman';
        $user->username = 'romanb';
        $user->status = 'developer';

        $ph1 = new CmsPhonenumber;
        $ph1->phonenumber = "0301234";
        $ph2 = new CmsPhonenumber;
        $ph2->phonenumber = "987654321";

        $user->addPhonenumber($ph1);
        $user->addPhonenumber($ph2);

        $this->_em->save($user);
        $this->_em->flush();

        //$this->assertTrue($user->phonenumbers instanceof \Doctrine\ORM\PersistentCollection);

        // Remove the first element from the collection
        unset($user->phonenumbers[0]);
        $ph1->user = null; // owning side!

        $this->_em->flush();

        $this->assertEquals(1, count($user->phonenumbers));
        $this->assertNull($ph1->user);
    }

    public function testBasicOneToOne()
    {
        $user = new CmsUser;
        $user->name = 'Roman';
        $user->username = 'romanb';
        $user->status = 'developer';

        $address = new CmsAddress;
        $address->country = 'Germany';
        $address->city = 'Berlin';
        $address->zip = '12345';

        $user->address = $address; // inverse side
        $address->user = $user; // owning side!

        $this->_em->save($user);
        $this->_em->flush();

        // Check that the foreign key has been set
        $userId = $this->_em->getConnection()->execute("SELECT user_id FROM cms_addresses WHERE id=?",
                array($address->id))->fetchColumn();
        $this->assertTrue(is_numeric($userId));
    }

    public function testBasicManyToMany()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';

        $group = new CmsGroup;
        $group->name = 'Developers';

        $user->groups[] = $group;
        $group->users[] = $user;

        $this->_em->save($user);
        $this->_em->save($group);

        $this->_em->flush();

        unset($group->users[0]); // inverse side
        unset($user->groups[0]); // owning side!

        $this->_em->flush();

        // Check that the link in the association table has been deleted
        $count = $this->_em->getConnection()->execute("SELECT COUNT(*) FROM cms_users_groups",
                array())->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testManyToManyCollectionClearing()
    {
        echo PHP_EOL . "MANY-MANY" . PHP_EOL;

        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';

        for ($i=0; $i<10; ++$i) {
            $group = new CmsGroup;
            $group->name = 'Developers_' . $i;
            $user->groups[] = $group;
            $group->users[] = $user;
        }

        $this->_em->save($user); // Saves the user, 'cause of post-insert ID

        $this->_em->flush();

        // Check that there are indeed 10 links in the association table
        $count = $this->_em->getConnection()->execute("SELECT COUNT(*) FROM cms_users_groups",
                array())->fetchColumn();
        $this->assertEquals(10, $count);

        //$user->groups->clear();
        unset($user->groups);

        echo PHP_EOL . "FINAL FLUSH" . PHP_EOL;
        $this->_em->flush();

        // Check that the links in the association table have been deleted
        $count = $this->_em->getConnection()->execute("SELECT COUNT(*) FROM cms_users_groups",
                array())->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testBasicQuery()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';
        $this->_em->save($user);
        $this->_em->flush();

        $query = $this->_em->createQuery("select u from Doctrine\Tests\Models\CMS\CmsUser u");

        $users = $query->getResultList();

        $this->assertEquals(1, $users->count());
        $this->assertEquals('Guilherme', $users[0]->name);
        $this->assertEquals('gblanco', $users[0]->username);
        $this->assertEquals('developer', $users[0]->status);
        //$this->assertNull($users[0]->phonenumbers);
        //$this->assertNull($users[0]->articles);

        $usersArray = $query->getResultArray();

        $this->assertTrue(is_array($usersArray));
        $this->assertEquals(1, count($usersArray));
        $this->assertEquals('Guilherme', $usersArray[0]['name']);
        $this->assertEquals('gblanco', $usersArray[0]['username']);
        $this->assertEquals('developer', $usersArray[0]['status']);

        $usersScalar = $query->getScalarResult();

        $this->assertTrue(is_array($usersScalar));
        $this->assertEquals(1, count($usersScalar));
        $this->assertEquals('Guilherme', $usersScalar[0]['u_name']);
        $this->assertEquals('gblanco', $usersScalar[0]['u_username']);
        $this->assertEquals('developer', $usersScalar[0]['u_status']);
    }

    public function testBasicOneToManyInnerJoin()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';
        $this->_em->save($user);
        $this->_em->flush();

        $query = $this->_em->createQuery("select u from Doctrine\Tests\Models\CMS\CmsUser u join u.phonenumbers p");

        $users = $query->getResultList();

        $this->assertEquals(0, $users->count());
    }

    public function testBasicOneToManyLeftJoin()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';
        $this->_em->save($user);
        $this->_em->flush();

        $query = $this->_em->createQuery("select u,p from Doctrine\Tests\Models\CMS\CmsUser u left join u.phonenumbers p");

        $users = $query->getResultList();

        $this->assertEquals(1, $users->count());
        $this->assertEquals('Guilherme', $users[0]->name);
        $this->assertEquals('gblanco', $users[0]->username);
        $this->assertEquals('developer', $users[0]->status);
        $this->assertTrue($users[0]->phonenumbers instanceof \Doctrine\ORM\PersistentCollection);
        $this->assertEquals(0, $users[0]->phonenumbers->count());
        //$this->assertNull($users[0]->articles);
    }

    public function testBasicManyToManyJoin()
    {
        $user = new CmsUser;
        $user->name = 'Guilherme';
        $user->username = 'gblanco';
        $user->status = 'developer';

        $group1 = new CmsGroup;
        $group1->setName('Doctrine Developers');

        $user->addGroup($group1);

        $this->_em->save($user);
        $this->_em->save($group1);

        
        $this->_em->flush();
        $this->_em->clear();

        $this->assertEquals(0, $this->_em->getUnitOfWork()->size());

        $query = $this->_em->createQuery("select u, g from Doctrine\Tests\Models\CMS\CmsUser u join u.groups g");

        $result = $query->getResultList();
        
        $this->assertEquals(2, $this->_em->getUnitOfWork()->size());
        $this->assertTrue($result[0] instanceof CmsUser);
        $this->assertEquals('Guilherme', $result[0]->name);
        $this->assertEquals(1, $result[0]->getGroups()->count());
        $groups = $result[0]->getGroups();
        $this->assertEquals('Doctrine Developers', $groups[0]->getName());

        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_MANAGED, $this->_em->getUnitOfWork()->getEntityState($result[0]));
        $this->assertEquals(\Doctrine\ORM\UnitOfWork::STATE_MANAGED, $this->_em->getUnitOfWork()->getEntityState($groups[0]));

        $this->assertTrue($groups instanceof \Doctrine\ORM\PersistentCollection);
        $this->assertTrue($groups[0]->getUsers() instanceof \Doctrine\ORM\PersistentCollection);

        $groups[0]->getUsers()->clear();
        $groups->clear();

        $this->_em->flush();
        $this->_em->clear();

        $query = $this->_em->createQuery("select u, g from Doctrine\Tests\Models\CMS\CmsUser u inner join u.groups g");
        $this->assertEquals(0, count($query->getResultList()));

        /* RB: TEST */
        /*
        $address = new CmsAddress;
        $address->country = 'Germany';
        $address->zip = '103040';
        $address->city = 'Berlin';
        $address->user = $user;
        $this->_em->save($address);
        $this->_em->clear();
        
        $proxy = $this->_em->getProxyGenerator()->getAssociationProxy($user, $this->_em->getClassMetadata('Doctrine\Tests\Models\CMS\CmsUser')->getAssociationMapping('address'));

        var_dump($proxy->getId());
        //var_dump(get_class($proxy));
        var_dump(get_class($proxy->user));
        //var_dump($proxy);

        //$proxy = $this->_em->getProxyGenerator()->getReferenceProxy('Doctrine\Tests\Models\CMS\CmsUser', 1);

        //echo $proxy->getId();
        //var_dump(serialize($proxy));
        */
        
        
    }
}