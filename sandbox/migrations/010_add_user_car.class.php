<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddUserCar extends Doctrine_Migration
{
	public function up()
	{
		$this->createTable('user_car', array (
  'user_id' => 
  array (
    'primary' => true,
    'type' => 'integer',
    'length' => 11,
  ),
  'car_id' => 
  array (
    'primary' => true,
    'type' => 'integer',
    'length' => 11,
  ),
), array (
  'indexes' => 
  array (
  ),
  'primary' => 
  array (
    0 => 'user_id',
    1 => 'car_id',
  ),
));
	}

	public function down()
	{
		$this->dropTable('user_car');
	}
}