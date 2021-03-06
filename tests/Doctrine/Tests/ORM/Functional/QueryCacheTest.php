<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\Tests\Models\CMS\CmsUser;
use Doctrine\ORM\Cache\ArrayCache;

require_once __DIR__ . '/../../TestInit.php';

/**
 * QueryCacheTest
 *
 * @author robo
 */
class QueryCacheTest extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp() {
        $this->useModelSet('cms');
        parent::setUp();
    }

    public function testQueryCache()
    {
        $user = new CmsUser;
        $user->name = 'Roman';
        $user->username = 'romanb';
        $user->status = 'dev';
        $this->_em->save($user);
        $this->_em->flush();


        $query = $this->_em->createQuery('select ux from Doctrine\Tests\Models\CMS\CmsUser ux');
        $cache = new ArrayCache;
        $query->setQueryCacheDriver($cache);
		$this->assertEquals(0, $cache->count());
		
        $users = $query->getResultList();

       	$this->assertEquals(1, $cache->count());
       	$this->assertTrue($cache->contains(md5('select ux from Doctrine\Tests\Models\CMS\CmsUser uxDOCTRINE_QUERY_CACHE_SALT')));
        $this->assertEquals(1, count($users));
        $this->assertEquals('Roman', $users[0]->name);
        
        $this->_em->clear();
        
        $query2 = $this->_em->createQuery('select ux from Doctrine\Tests\Models\CMS\CmsUser ux');
        $query2->setQueryCacheDriver($cache);
        
        $users = $query2->getResultList();

       	$this->assertEquals(1, $cache->count());
       	$this->assertTrue($cache->contains(md5('select ux from Doctrine\Tests\Models\CMS\CmsUser uxDOCTRINE_QUERY_CACHE_SALT')));
        $this->assertEquals(1, count($users));
        $this->assertEquals('Roman', $users[0]->name);
    }
}

