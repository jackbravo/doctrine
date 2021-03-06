<?php

namespace Doctrine\Tests\ORM;

use Doctrine\Tests\Mocks\ConnectionMock;
use Doctrine\Tests\Mocks\EntityManagerMock;
use Doctrine\Tests\Mocks\UnitOfWorkMock;
use Doctrine\Tests\Mocks\SequenceMock;

use Doctrine\Tests\Models\Forum\ForumUser;
use Doctrine\Tests\Models\Forum\ForumAvatar;

require_once __DIR__ . '/../TestInit.php';

class EntityPersisterTest extends \Doctrine\Tests\OrmTestCase
{
    private $_connMock;
    private $_emMock;
    private $_idGenMock;
    private $_uowMock;
    
    protected function setUp()
    {
        parent::setUp();
        $this->_connMock = new ConnectionMock(array(), new \Doctrine\Tests\Mocks\DriverMock());
        $this->_emMock = EntityManagerMock::create($this->_connMock);
        $this->_uowMock = new UnitOfWorkMock($this->_emMock);
        $this->_emMock->setUnitOfWork($this->_uowMock);
        $this->_idGenMock = new SequenceMock($this->_emMock, 'seq', 20);
        //$this->_emMock->setIdGenerator('Doctrine\Tests\Models\Forum\ForumUser', $this->_idGenMock);
    }
    
    public function testSimpleInsert()
    {
        $userPersister = new \Doctrine\ORM\Persisters\SingleTablePersister(
                $this->_emMock, $this->_emMock->getClassMetadata("Doctrine\Tests\Models\Forum\ForumUser"));
        $avatarPersister = new \Doctrine\ORM\Persisters\StandardEntityPersister(
                $this->_emMock, $this->_emMock->getClassMetadata("Doctrine\Tests\Models\Forum\ForumAvatar"));

        $user = new ForumUser();
        $user->username = "romanb";
        $user->avatar = new ForumAvatar();

        $this->_uowMock->setDataChangeSet($user, array(
                'username' => array('', 'romanb'),
                'avatar' => array('', $user->avatar)));

        //insert
        $avatarPersister->insert($user->avatar);
        $inserts = $this->_connMock->getInserts();
        //check
        $this->assertEquals(1, count($inserts));
        $this->assertTrue(isset($inserts['forum_avatars']));
        $this->assertEquals(1, count($inserts['forum_avatars']));
        $this->assertEquals(null, $user->avatar->id);
        $user->avatar->id = 0; // Fake that we got an id

        //insert
        $userPersister->insert($user);
        $inserts = $this->_connMock->getInserts();
        //check
        $this->assertEquals(2, count($inserts));
        $this->assertEquals(null, $user->id);
        $this->assertTrue(isset($inserts['forum_users']));
        $this->assertEquals(1, count($inserts['forum_users']));
        $this->assertEquals(3, count($inserts['forum_users'][0]));
        //username column
        $this->assertTrue(isset($inserts['forum_users'][0]['username']));
        $this->assertEquals('romanb', $inserts['forum_users'][0]['username']);
        //avatar_id join column
        $this->assertTrue(isset($inserts['forum_users'][0]['avatar_id']));
        $this->assertEquals(0, $inserts['forum_users'][0]['avatar_id']);
    }
}