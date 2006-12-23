<?php
ob_start();


require_once('ConfigurableTestCase.php');
require_once('DriverTestCase.php');
require_once('ManagerTestCase.php');
require_once('ConnectionTestCase.php');
require_once('ConnectionTransactionTestCase.php');
require_once('TableTestCase.php');
require_once('EventListenerTestCase.php');
require_once('BatchIteratorTestCase.php');
require_once('CacheFileTestCase.php');

require_once('RecordTestCase.php');
require_once('RecordStateTestCase.php');
require_once('RecordFilterTestCase.php');

require_once('AccessTestCase.php');
require_once('ValidatorTestCase.php');
require_once('CollectionTestCase.php');
require_once('PessimisticLockingTestCase.php');
require_once('EventListenerChainTestCase.php');
require_once('CacheSqliteTestCase.php');
require_once('CollectionOffsetTestCase.php');

require_once('CacheQuerySqliteTestCase.php');
require_once('ViewTestCase.php');
require_once('RawSqlTestCase.php');
require_once('CustomPrimaryKeyTestCase.php');
require_once('FilterTestCase.php');
require_once('HookTestCase.php');

require_once('QueryTestCase.php');
require_once('QueryLimitTestCase.php');
require_once('QueryMultiJoinTestCase.php');
require_once('QueryReferenceModelTestCase.php');
require_once('QueryWhereTestCase.php');
require_once('QueryFromTestCase.php');
require_once('QueryConditionTestCase.php');
require_once('QueryComponentAliasTestCase.php');
require_once('QuerySubqueryTestCase.php');
require_once('QuerySelectTestCase.php');
require_once('QueryShortAliasesTestCase.php');
require_once('QueryDeleteTestCase.php');
require_once('QueryUpdateTestCase.php');
require_once('QueryIdentifierQuotingTestCase.php');
require_once('QueryAggregateValueTestCase.php');

require_once('UnitOfWorkTestCase.php');

require_once('RelationAccessTestCase.php');
require_once('RelationTestCase.php');
require_once('RelationManyToManyTestCase.php');


require_once('DBTestCase.php');
require_once('DbProfilerTestCase.php');

require_once('SchemaTestCase.php');
require_once('ImportTestCase.php');
require_once('BooleanTestCase.php');
require_once('EnumTestCase.php');

require_once('DataDictSqliteTestCase.php');


$drivers = array('Firebird',
                               'Informix',
                               'Mysql',
                               'Mssql', 
                               'Oracle', 
                               'Pgsql', 
                               'Sqlite'
                               );
                            
foreach($drivers as $driver) {
    require_once('DataDict/' . $driver . 'TestCase.php');
}
/**
class Doctrine_Tester {
    protected $drivers = array('Firebird',
                               'Informix',
                               'Mysql',
                               'Mssql',
                               'Oracle',
                               'Pgsql',
                               'Sqlite'
                               );

    public function loadModule($module) {
        foreach($drivers as $driver) {
            require_once($module . DIRECTORY_SEPARATOR . $driver . 'TestCase.php');
        }
    }
    public function run($module) {
        $this->loadModule($module);

        foreach($drivers as $driver) {

        }
    }
}
*/
require_once('ExportTestCase.php');
require_once('ExportReporterTestCase.php');
require_once('ExportMysqlTestCase.php');
require_once('ExportFirebirdTestCase.php');
require_once('ExportPgsqlTestCase.php');
require_once('ExportOracleTestCase.php');
require_once('ExportSqliteTestCase.php');

require_once('TransactionTestCase.php');
require_once('TransactionMysqlTestCase.php');
require_once('TransactionPgsqlTestCase.php');
require_once('TransactionOracleTestCase.php');
require_once('TransactionFirebirdTestCase.php');
require_once('TransactionMssqlTestCase.php');
require_once('TransactionSqliteTestCase.php');

require_once('Connection/MysqlTestCase.php');

require_once('CustomResultSetOrderTestCase.php');

error_reporting(E_ALL);
print '<pre>';

$test = new GroupTest('Doctrine Framework Unit Tests');

 /**
$test->addTestCase(new Doctrine_Export_Sqlite_TestCase());

foreach($drivers as $driver) {
    $class = 'Doctrine_DataDict_' . $driver . '_TestCase';

    $test->addTestCase(new $class());
}



$test->addTestCase(new Doctrine_Connection_Mysql_TestCase());

$test->addTestCase(new Doctrine_Export_Mysql_TestCase());

$test->addTestCase(new Doctrine_Export_Oracle_TestCase());

$test->addTestCase(new Doctrine_Export_Pgsql_TestCase());

$test->addTestCase(new Doctrine_Export_Firebird_TestCase());


$test->addTestCase(new Doctrine_Configurable_TestCase());



$test->addTestCase(new Doctrine_Export_Sqlite_TestCase());

           */

$test->addTestCase(new Doctrine_Export_Reporter_TestCase());

$test->addTestCase(new Doctrine_Transaction_TestCase());

$test->addTestCase(new Doctrine_Transaction_Mysql_TestCase());

$test->addTestCase(new Doctrine_Transaction_Pgsql_TestCase());

$test->addTestCase(new Doctrine_Transaction_Oracle_TestCase());

$test->addTestCase(new Doctrine_Transaction_Firebird_TestCase());

$test->addTestCase(new Doctrine_Transaction_Sqlite_TestCase());

$test->addTestCase(new Doctrine_Transaction_Mssql_TestCase());

$test->addTestCase(new Doctrine_Relation_ManyToMany_TestCase());

$test->addTestCase(new Doctrine_PessimisticLockingTestCase());

$test->addTestCase(new Doctrine_BooleanTestCase());

$test->addTestCase(new Doctrine_TableTestCase());

$test->addTestCase(new Doctrine_ValidatorTestCase());

$test->addTestCase(new Doctrine_UnitOfWork_TestCase());

$test->addTestCase(new Doctrine_ConnectionTestCase());

$test->addTestCase(new Doctrine_Db_TestCase());

$test->addTestCase(new Doctrine_Db_Profiler_TestCase());


$test->addTestCase(new Doctrine_Record_TestCase());

$test->addTestCase(new Doctrine_Relation_TestCase());

$test->addTestCase(new Doctrine_Record_State_TestCase());

//$test->addTestCase(new Doctrine_Import_TestCase());

$test->addTestCase(new Doctrine_SchemaTestCase());

$test->addTestCase(new Doctrine_EventListenerTestCase());

$test->addTestCase(new Doctrine_Connection_Transaction_TestCase());

$test->addTestCase(new Doctrine_AccessTestCase());

$test->addTestCase(new Doctrine_ManagerTestCase());

$test->addTestCase(new Doctrine_BatchIteratorTestCase());

//$test->addTestCase(new Doctrine_Collection_Offset_TestCase());

$test->addTestCase(new Doctrine_ViewTestCase());

$test->addTestCase(new Doctrine_CustomPrimaryKeyTestCase());

$test->addTestCase(new Doctrine_Filter_TestCase());

$test->addTestCase(new Doctrine_RawSql_TestCase());

$test->addTestCase(new Doctrine_CollectionTestCase());


$test->addTestCase(new Doctrine_DataDict_Sqlite_TestCase());

$test->addTestCase(new Doctrine_EventListener_Chain_TestCase());

$test->addTestCase(new Doctrine_RelationAccessTestCase());

$test->addTestCase(new Doctrine_CustomResultSetOrderTestCase());

//$test->addTestCase(new Doctrine_Record_Filter_TestCase());

$test->addTestCase(new Doctrine_EnumTestCase());

$test->addTestCase(new Doctrine_Query_MultiJoin_TestCase());
$test->addTestCase(new Doctrine_Query_ReferenceModel_TestCase());
$test->addTestCase(new Doctrine_Query_Condition_TestCase());
$test->addTestCase(new Doctrine_Query_ComponentAlias_TestCase());
$test->addTestCase(new Doctrine_Query_Subquery_TestCase());
$test->addTestCase(new Doctrine_Query_TestCase());
$test->addTestCase(new Doctrine_Query_ShortAliases_TestCase());
$test->addTestCase(new Doctrine_Query_From_TestCase());
$test->addTestCase(new Doctrine_Query_Delete_TestCase());
$test->addTestCase(new Doctrine_Query_Where_TestCase());
$test->addTestCase(new Doctrine_Query_Limit_TestCase());
$test->addTestCase(new Doctrine_Query_IdentifierQuoting_TestCase());
$test->addTestCase(new Doctrine_Query_Update_TestCase());
$test->addTestCase(new Doctrine_Query_AggregateValue_TestCase());
$test->addTestCase(new Doctrine_Query_Select_TestCase());

$test->addTestCase(new Doctrine_Hook_TestCase());

//$test->addTestCase(new Doctrine_Cache_Query_SqliteTestCase());
//$test->addTestCase(new Doctrine_Cache_FileTestCase());
//$test->addTestCase(new Doctrine_Cache_SqliteTestCase());

class MyReporter extends HtmlReporter {
    public function paintHeader() {}
    public function paintFooter()
    {
        $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
        print "<div style=\"";
        print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
        print "\">";
        print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print " test cases complete:\n";
        print "<strong>" . $this->getPassCount() . "</strong> passes, ";
        print "<strong>" . $this->getFailCount() . "</strong> fails and ";
        print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
        print "</div>\n";
    }
}

if (TextReporter::inCli()) {
    if ($argc == 4)
    {
        $dsn = $argv[1];
        $username = $argv[2];
        $password = $argv[3];
    }
    exit ($test->run(new TextReporter()) ? 0 : 1);
} else {
    if (isset($_POST))
    {
        $dsn        = isset($_POST["dsn"])?$_POST["dsn"]:null;
        $username   = isset($_POST["username"])?$_POST["username"]:null;
        $password   = isset($_POST["password"])?$_POST["password"]:null;
    }
    $test->run(new MyReporter());
    $output = ob_get_clean();
}
/**
$cache = Doctrine_Manager::getInstance()->getCurrentConnection()->getCacheHandler();
if(isset($cache)) {
    $a     = $cache->getQueries();
    print "Executed cache queries: ".count($a)."\n";

    foreach($a as $query) {
        print $query."\n";
    }

}
*/
?>
<html>
<head>

  <title>Doctrine Unit Tests</title>
  <style>
.fail { color: red; } pre { background-color: lightgray; }
  </style>
</head>

<body>

<h1>Doctrine Unit Tests</h1>
<h3>DSN Settings</h3>
<form method="post">
<table>
<tr>
  <th>DSN</th>
  <td><input type="text" name="dsn" /></td>
</tr>
<tr>
  <th>Username</th>
  <td><input type="text" name="username" /></td>
</tr>
<tr>
  <th>Password</th>
  <td><input type="text" name="password" /></td>
</tr>
<tr>
  <td>&nbsp;</td>
  <td><input type="submit" name="submit" /></td>
</tr>
</table>
</form>
<h3>Tests</h3>
<pre>
<?php echo $output; ?>
</pre>
<h3>Queries</h3>
<pre>
<?php
$dbh = Doctrine_Manager::getInstance()->getCurrentConnection()->getDBH();
$a   = $dbh->getQueries();

print "Executed queries: ".count($a)."\n";

foreach($a as $query) {
    print $query."\n";
}
?>
</pre>
</body>
</html>

