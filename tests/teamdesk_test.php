<?php

require_once 'config.php';
require_once '../teamdesk.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Teamdesk test case.
 */
class TeamdeskTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Teamdesk
	 */
	private $teamdesk;


	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->teamdesk = new Teamdesk(EMAIL, PASS, SERVER);
	}


	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		$this->teamdesk = null;

		parent::tearDown();
	}


	/**
	 * Constructs the test case.
	 */
	public function __construct()
	{
		// TODO Auto-generated constructor
	}


	/**
	 * Tests Teamdesk->getTimeOut()
	 */
	public function testGetTimeOut()
	{
		$this->teamdesk->setTimeOut(5);
		$this->assertEquals(5, $this->teamdesk->getTimeOut());
	}


	/**
	 * Tests Teamdesk->getUserAgent()
	 */
	public function testGetUserAgent()
	{
		$this->teamdesk->setUserAgent('testing/1.0.0');
		$this->assertEquals('PHP Teamdesk/' . Teamdesk::VERSION . ' testing/1.0.0', $this->teamdesk->getUserAgent());
	}


	/**
	 * Tests Teamdesk->create()
	 */
	public function testCreate()
	{
		$this->markTestIncomplete("create test not implemented");
	}


	/**
	 * Tests Teamdesk->delete()
	 */
	public function testDelete()
	{
		$this->markTestIncomplete("delete test not implemented");
	}


	/**
	 * Tests Teamdesk->getDeleted()
	 */
	public function testGetDeleted()
	{
		$var = $this->teamdesk->getDeleted('Website', mktime(00, 00, 00, 06, 20, 2011), time());

		$this->assertEquals(new stdClass(), $var);
	}


	/**
	 * Tests Teamdesk->getUpdated()
	 */
	public function testGetUpdated()
	{
		$var = $this->teamdesk->getUpdated('Website', mktime(00, 00, 00, 06, 20, 2011), time());

		$this->assertObjectHasAttribute('int', $var);
	}


	/**
	 * Tests Teamdesk->describeApp()
	 */
	public function testDescribeApp()
	{
		// TODO Auto-generated TeamdeskTest->testDescribeApp()
		$this->markTestIncomplete("describeApp test not implemented");

		$var = $this->teamdesk->describeApp();

		$this->assertObjectHasAttribute('Id', $var);
		$this->assertObjectHasAttribute('Name', $var);
		$this->assertObjectHasAttribute('Logo', $var);
		$this->assertObjectHasAttribute('Tabs', $var);
	}


	/**
	 * Tests Teamdesk->describeTable()
	 */
	public function testDescribeTable()
	{
		$var = $this->teamdesk->describeTable('Website');

		$this->assertObjectHasAttribute('Id', $var);
		$this->assertObjectHasAttribute('RecordName', $var);
		$this->assertObjectHasAttribute('RecordsName', $var);
		$this->assertObjectHasAttribute('Description', $var);
		$this->assertObjectHasAttribute('ShowTab', $var);
		$this->assertObjectHasAttribute('Color', $var);
	}


	/**
	 * Tests Teamdesk->describeTables()
	 */
	public function testDescribeTables()
	{
		$var = $this->teamdesk->describeTables(array('Website', 'People'));

		$this->assertObjectHasAttribute('DescribeTableResult', $var);
	}


	/**
	 * Tests Teamdesk->query()
	 */
	public function testQuery()
	{
		$var = $this->teamdesk->query('SELECT * FROM [Website]');

		$this->assertObjectHasAttribute('r', $var);
		$this->assertType('SimpleXMLElement', $var);
	}


	/**
	 * Tests Teamdesk->retrieve()
	 */
	public function testRetrieve()
	{
		$var = $this->teamdesk->retrieve('Website', array('Name', 'URL'), array(1, 2));

		$this->assertObjectHasAttribute('r', $var);
		$this->assertType('SimpleXMLElement', $var);
	}


	/**
	 * Tests Teamdesk->update()
	 */
	public function testUpdate()
	{
		$this->markTestIncomplete("update test not implemented");
	}


	/**
	 * Tests Teamdesk->upsert()
	 */
	public function testUpsert()
	{
		$this->markTestIncomplete("upsert test not implemented");
	}


	/**
	 * Tests Teamdesk->getAttachment()
	 */
	public function testGetAttachment()
	{
		$var = $this->teamdesk->getAttachment('Invoice', 'Tender', 2);

		$this->assertObjectHasAttribute('Revision', $var);
		$this->assertObjectHasAttribute('Name', $var);
		$this->assertObjectHasAttribute('User', $var);
		$this->assertObjectHasAttribute('Time', $var);
		$this->assertObjectHasAttribute('Size', $var);
		$this->assertObjectHasAttribute('Type', $var);
		$this->assertObjectHasAttribute('Data', $var);
	}


	/**
	 * Tests Teamdesk->getAttachmentInfo()
	 */
	public function testGetAttachmentInfo()
	{
		$var = $this->teamdesk->getAttachmentInfo('Invoice', 'Tender', 2, 10);

		$this->assertObjectHasAttribute('AttachmentInfo', $var);
	}


	/**
	 * Tests Teamdesk->setAttachment()
	 */
	public function testSetAttachment()
	{
		$this->markTestIncomplete("setAttachment test not implemented");
	}


	/**
	 * Tests Teamdesk->getUserInfo()
	 */
	public function testGetUserInfo()
	{
		$var = $this->teamdesk->getUserInfo();

		$this->assertObjectHasAttribute('Id', $var);
		$this->assertObjectHasAttribute('FirstName', $var);
		$this->assertObjectHasAttribute('LastName', $var);
		$this->assertObjectHasAttribute('Email', $var);
		$this->assertObjectHasAttribute('FullName', $var);
		$this->assertObjectHasAttribute('Locale', $var);
		$this->assertObjectHasAttribute('TimeZone', $var);
	}


	/**
	 * Tests Teamdesk->sendMail()
	 */
	public function testSendMail()
	{
		$this->assertTrue($this->teamdesk->sendMail('from@verkoyen.eu', 'to@verkoyen.eu', null, null, 'subject', 'format', 'body'));
	}
}

