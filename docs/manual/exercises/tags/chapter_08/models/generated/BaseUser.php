<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseUser extends Doctrine_Record
{
  public function setTableDefinition()
  {
    $this->setTableName('user');
    $this->hasColumn('is_active', 'integer', 1, array('type' => 'integer', 'default' => '1', 'length' => '1'));
    $this->hasColumn('is_super_admin', 'integer', 1, array('type' => 'integer', 'default' => '0', 'length' => '1'));
    $this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => '255'));
    $this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => '255'));
    $this->hasColumn('username', 'string', 255, array('type' => 'string', 'default' => 'default username', 'length' => '255'));
    $this->hasColumn('password', 'string', 255, array('type' => 'string', 'length' => '255'));
    $this->hasColumn('type', 'string', 255, array('type' => 'string', 'length' => '255'));
  }

  public function setUp()
  {
    $this->hasOne('Email', array('local' => 'id',
                                 'foreign' => 'user_id'));

    $this->hasMany('Phonenumber as Phonenumbers', array('local' => 'id',
                                                        'foreign' => 'user_id'));

    $this->hasMany('Group as Groups', array('refClass' => 'UserGroup',
                                            'local' => 'user_id',
                                            'foreign' => 'group_id'));

    $this->hasMany('User as Parents', array('refClass' => 'UserReference',
                                            'local' => 'parent_id',
                                            'foreign' => 'child_id'));

    $this->hasMany('User as Friends', array('refClass' => 'FriendReference',
                                            'local' => 'user1',
                                            'foreign' => 'user2',
                                            'equal' => true));

    $this->hasOne('Address as Addresses', array('local' => 'id',
                                                'foreign' => 'user_id',
                                                'cascade' => array(0 => 'delete')));

    $this->hasMany('User as Children', array('refClass' => 'UserReference',
                                             'local' => 'child_id',
                                             'foreign' => 'parent_id'));

    $this->hasMany('Forum_Thread as Threads', array('local' => 'id',
                                                    'foreign' => 'user_id'));

    $timestampable0 = new Doctrine_Template_Timestampable();
    $this->actAs($timestampable0);
  }
}