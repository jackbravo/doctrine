<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');

$conn->export->createTable('test', array('name' => array('type' => 'string')));
$conn->execute('INSERT INTO test (name) VALUES (?)', array('jwage'));