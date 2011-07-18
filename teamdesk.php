<?php

/**
 * teamdesk class
 *
 * This source file can be used to communicate with Team desk (http://www.teamdesk.net)
 *
 * The class is documented in the file itself. If you find any bugs help me out and report them. Reporting can be done by sending an email to php-teamdesk-bugs[at]verkoyen[dot]eu.
 * If you report a bug, make sure you give me enough information (include your code).
 *
 * License
 * Copyright (c), Tijs Verkoyen. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.
 *
 * This software is provided by the author "as is" and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed. In no event shall the author be liable for any direct, indirect, incidental, special, exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or services; loss of use, data, or profits; or business interruption) however caused and on any theory of liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of this software, even if advised of the possibility of such damage.
 *
 * @author			Tijs Verkoyen <php-teamdesk@verkoyen.eu>
 * @version			1.0.0
 *
 * @copyright		Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license			BSD License
 */
class Teamdesk
{
	// internal constant to enable/disable debugging
	const DEBUG = true;

	// current version
	const VERSION = '1.0.0';


	/**
	 * The login that will be used for authenticating
	 *
	 * @var	string
	 */
	private $login;


	/**
	 * The password that will be used for authenticating
	 *
	 * @var	string
	 */
	private $password;


	/**
	 * The server to use
	 *
	 * @var	string
	 */
	private $server = null;


	private $sessionId;


	/**
	 * The SOAP-client
	 *
	 * @var	SoapClient
	 */
	private $soapClient;


	/**
	 * The timeout
	 *
	 * @var	int
	 */
	private $timeOut = 60;


	/**
	 * The user agent
	 *
	 * @var	string
	 */
	private $userAgent;


// class methods
	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	string $login		Login provided for API access.
	 * @param	string $password	The password.
	 * @param	string $server		The server to use. See Setup - Integration API.
	 */
	public function __construct($login, $password, $server)
	{
		$this->setLogin($login);
		$this->setPassword($password);
		$this->setServer($server);
	}


	/**
	 * Destructor
	 *
	 * @return	void
	 */
	public function __destruct()
	{
		// is the connection open?
		if($this->soapClient !== null)
		{
			// reset vars
			$this->soapClient = null;
		}
	}


	/**
	 * Make the call
	 *
	 * @return	mixed
	 * @param	string $method					The method to be called.
	 * @param	array[optional] $parameters		The parameters.
	 */
	private function doCall($method, array $parameters = array())
	{
		// redefine
		$method = (string) $method;
		$parameters = (array) $parameters;

		// open connection if needed
		if($this->soapClient === null)
		{
			// build options
			$options = array('soap_version' => SOAP_1_1,
							 'trace' => self::DEBUG,
							 'exceptions' => false,
							 'connection_timeout' => $this->getTimeOut(),
							 'user_agent' => $this->getUserAgent()
						);

			// create client
			$this->soapClient = new SoapClient($this->getServer() . '?wsdl', $options);
		}

		// no session id
		if($this->sessionId === null)
		{
			// method isn't login
			if($method !== 'Login')
			{
				// build parameters
				$loginParameters = array();
				$loginParameters['email'] = $this->getLogin();
				$loginParameters['password'] = $this->getPassword();

				// make the call
				$response = $this->soapClient->__soapCall('Login', array($loginParameters));

				// store session id
				if(isset($response->LoginResult->SessionId)) $this->sessionId = (string) $response->LoginResult->SessionId;

				// build header
				$header = new SoapHeader('urn:soap.teamdesk.net', 'SessionHeader', array('sessionId' => $this->sessionId));

				// set the headers
				$this->soapClient->__setSoapHeaders($header);
			}
		}

		// loop parameters
		foreach($parameters as $key => $value)
		{
			// strings should be UTF8
			if(gettype($value) == 'string') $parameters[$key] = utf8_encode($value);
		}

		// make the call
		$response = $this->soapClient->__soapCall($method, array($parameters));

		// validate
		if(is_soap_fault($response))
		{
			// init var
			$message = $response->getMessage();

			// internal debugging enabled
			if(self::DEBUG)
			{
				echo '<pre>';
				echo 'last request<br />';
				var_dump($this->soapClient->__getLastRequest());
				echo 'response<br />';
				var_dump($response);
				echo '</pre>';
			}

			// throw exception
			throw new TeamdeskException($message);
		}

		// validate response
		if(!isset($response->{ucfirst($method) . 'Result'}))
		{
			// empty object, means the call was successfull
			if($response == new stdClass()) return true;

			// invalid response
			else throw new TeamdeskException('Invalid response');
		}

		// return
		return $response->{ucfirst($method) . 'Result'};
	}


	/**
	 * Get the login
	 *
	 * @return	string
	 */
	private function getLogin()
	{
		return (string) $this->login;
	}


	/**
	 * Get the password
	 *
	 * @return	string
	 */
	private function getPassword()
	{
		return $this->password;
	}


	/**
	 * Get the server
	 *
	 * @return	string
	 */
	private function getServer()
	{
		return $this->server;
	}


	/**
	 * Get the timeout that will be used
	 *
	 * @return	int
	 */
	public function getTimeOut()
	{
		return (int) $this->timeOut;
	}


	/**
	 * Get the useragent that will be used. Our version will be prepended to yours.
	 * It will look like: "PHP Teamdesk/<version> <your-user-agent>"
	 *
	 * @return	string
	 */
	public function getUserAgent()
	{
		return (string) 'PHP Teamdesk/' . self::VERSION . ' ' . $this->userAgent;
	}


	/**
	 * Set the login that has to be used
	 *
	 * @return	void
	 * @param	string $login	The login to use.
	 */
	private function setLogin($login)
	{
		$this->login = (string) $login;
	}


	/**
	 * Set the password that has to be used
	 *
	 * @return	void
	 * @param	string $password	The password to use.
	 */
	private function setPassword($password)
	{
		$this->password = (string) $password;
	}


	/**
	 * Set the server that has to be used.
	 *
	 * @return	void
	 * @param	string $server	The server to use.
	 */
	private function setServer($server)
	{
		$this->server = (string) $server;
	}


	/**
	 * Set the timeout
	 * After this time the request will stop. You should handle any errors triggered by this.
	 *
	 * @return	void
	 * @param	int $seconds	The timeout in seconds.
	 */
	public function setTimeOut($seconds)
	{
		$this->timeOut = (int) $seconds;
	}


	/**
	 * Set the user-agent for you application
	 * It will be appended to ours, the result will look like: "PHP Teamdesk/<version> <your-user-agent>"
	 *
	 * @return	void
	 * @param	string $userAgent	Your user-agent, it should look like <app-name>/<app-version>.
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = (string) $userAgent;
	}


// record methods
	/**
	 * Creates new records
	 *
	 * @return	array
	 * @param	string $table	The table wherein the records should be created.
	 * @param	string $xml		The records in XML-format.
	 */
	public function create($table, $xml)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['data'] = (string) $xml;

		// make the call
		return $this->doCall('Create', $parameters);
	}


	/**
	 * Deletes one of more individual records from the table.
	 *
	 * @return	bool
	 * @param	string $table	The table wherefrom the records should be deleted.
	 * @param	array $ids		The ids of the records to delete.
	 */
	public function delete($table, array $ids)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['ids'] = (array) $ids;

		// make the call
		return $this->doCall('Delete', $parameters);
	}


	/**
	 * Retrieves the list of individual record ids that have been deleted within the given timespan for the specified table
	 *
	 * @return	array
	 * @param	string $table	The table where the record where.
	 * @param	int $startTime	The start of the timespan, as a UNIXTIME-stamp.
	 * @param	int $endTime	The end of the timespan, as a UNIXTIME-stamp.
	 */
	public function getDeleted($table, $startTime, $endTime)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['startTime'] = (string) date('c', (int) $startTime);
		$parameters['endTime'] = (string) date('c', (int) $endTime);

		// make the call
		return $this->doCall('GetDeleted', $parameters);
	}


	/**
	 * Retrieves the list of individual record IDs that have been updated (added or changed) within the given timespan for the specified table
	 *
	 * @return	array
	 * @param	string $table	The table where the record where.
	 * @param	int $startTime	The start of the timespan, as a UNIXTIME-stamp.
	 * @param	int $endTime	The end of the timespan, as a UNIXTIME-stamp.
	 */
	public function getUpdated($table, $startTime, $endTime)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['startTime'] = (string) date('c', (int) $startTime);
		$parameters['endTime'] = (string) date('c', (int) $endTime);

		// make the call
		return $this->doCall('GetUpdated', $parameters);
	}


// application methods
	/**
	 * Retrieves applocation and basic table information
	 *
	 * @return	object
	 */
	public function describeApp()
	{
		// make the call
		return $this->doCall('DescribeApp');
	}


// table methods
	/**
	 * Describes metadata (table an column properties) for specified table.
	 *
	 * @return	object
	 * @param	string $table	The name of the table (RecordName in return of describeApp).
	 */
	public function describeTable($table)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;

		// make the call
		return $this->doCall('DescribeTable', $parameters);
	}


	/**
	 * Array-based version of DescribeTable(), describes metadata (table and column properties) for specified tables.
	 *
	 * @return	object
	 * @param	array $tables	The name of the tables.
	 */
	public function describeTables(array $tables)
	{
		// build parameters
		$parameters = array();
		$parameters['tables'] = $tables;

		// make the call
		return $this->doCall('DescribeTables', $parameters);
	}


	/**
	 * Executes a query against the specified table and returns data that matches the specified criteria.
	 * SELECT [ TOP n ] <column-names> | * FROM <table> [ WHERE <condition> ] [ ORDER BY <column-names> ]
	 *
	 * @return	SimpleXMLElement
	 * @param	string $query		The query to execute.
	 */
	public function query($query)
	{
		// build parameters
		$parameters = array();
		$parameters['query'] = (string) $query;

		// make the call
		$response = $this->doCall('Query', $parameters);

		// valid response?
		if(!isset($response->any)) return null;

		// load as XML
		$xml = simplexml_load_string($response->any);

		// valid xml?
		if($xml === false) return null;

		// return
		return $xml->Data;
	}


	/**
	 * Retrieves one or more records based on the specified record IDs
	 *
	 * @return	SimpleXMLElement
	 * @param	string $table		The table wherefrom the records should be grabbed.
	 * @param	array $columns		The columns to retrieve.
	 * @param	array $ids			The ids to retrieve.
	 */
	public function retrieve($table, array $columns, array $ids)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['columns'] = (array) $columns;
		$parameters['ids'] = (array) $ids;

		// make the call
		$response = $this->doCall('Retrieve', $parameters);

		// valid response?
		if(!isset($response->any)) return null;

		// load as XML
		$xml = simplexml_load_string($response->any);

		// valid xml?
		if($xml === false) return null;

		// return
		return $xml->Data;
	}


	/**
	 * Updates existing records
	 *
	 * @return	bool
	 * @param	string $table	The table wherein the records should be updated.
	 * @param	string $xml		The data.
	 */
	public function update($table, $xml)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['xml'] = (string) $xml;

		// make the call
		return $this->doCall('Update', $parameters);
	}


	/**
	 * Creates or updates records
	 *
	 * @return	bool
	 * @param	string $table			The table wherein the records should be updated.
	 * @param	string $xml				The data.
	 * @param	string $matchColumn		The column that marks items as unique.
	 */
	public function upsert($table, $xml, $matchColumn)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['xml'] = (string) $xml;
		$parameters['matchColumn'] = (string) $matchColumn;

		// make the call
		return $this->doCall('Upsert', $parameters);
	}


// attachment methods
	/**
	 * Retrieves file information and data for the specified revision
	 *
	 * @return	object
	 * @param	string $table				The table wherein the attachment is stored.
	 * @param	string $column				The name of the column that contains the attachment.
	 * @param	int $id						The id of the record.
	 * @param	int[optional] $revision		The revision, use 0 for current.
	 */
	public function getAttachment($table, $column, $id, $revision = 0)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['column'] = (string) $column;
		$parameters['id'] = (int) $id;
		$parameters['revision'] = (int) $revision;

		// make the call
		return $this->doCall('GetAttachment', $parameters);
	}


	/**
	 * Retrieves the list of revisions and detailed information about each revision
	 *
	 * @return	object
	 * @param	string $table		The table wherein the attachment is stored.
	 * @param	string $column		The name of the column that contains the attachment.
	 * @param	int $id				The id of the record.
	 * @param	int $revisions		No idea.
	 */
	public function getAttachmentInfo($table, $column, $id, $revisions)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['column'] = (string) $column;
		$parameters['id'] = (int) $id;
		$parameters['revisions'] = (int) $revisions;

		// make the call
		return $this->doCall('GetAttachmentInfo', $parameters);
	}


	/**
	 * Sets file data for the specified record and column
	 *
	 * @return	bool
	 * @param	string $table			The table wherein the attachment will be stored.
	 * @param	string $column			The name of the column that contains the attachment.
	 * @param	int $id					The id of the record.
	 * @param	string $fileName		The name of the file.
	 * @param	string $mimeType		The mime-type of the file.
	 * @param	string $data			The data of the file, encodes as a base64 string.
	 */
	public function setAttachment($table, $column, $id, $fileName, $mimeType, $data)
	{
		// build parameters
		$parameters = array();
		$parameters['table'] = (string) $table;
		$parameters['column'] = (string) $column;
		$parameters['id'] = (int) $id;
		$parameters['fileName'] = (string) $fileName;
		$parameters['mimeType'] = (string) $mimeType;
		$parameters['data'] = (string) $data;

		// make the call
		return $this->doCall('SetAttachment', $parameters);
	}


// user methods
	/**
	 * Retrieves the information for the user currently logged in
	 *
	 * @return	object
	 */
	public function getUserInfo()
	{
		return $this->doCall('GetUserInfo');
	}


// mail methods
	/**
	 * Sends a mail via server.
	 *
	 * @return	bool
	 * @param	string $from				The emailaddress which will be used as from.
	 * @param	string $to					The emailaddress whereto the mail will be send.
	 * @param	string[optional] $cc		The emailaddress in cc.
	 * @param	string[optional] $bcc		The emailaddress in bcc.
	 * @param	string $subject				The subject of the mail.
	 * @param	string $format				The format of the mail.
	 * @param	string $body				The body of the mail.
	 */
	public function sendMail($from, $to, $cc = null, $bcc = null, $subject, $format, $body)
	{
		// build parameters
		$parameters = array();
		$parameters['from'] = (string) $from;
		$parameters['to'] = (string) $to;
		if($cc !== null) $parameters['cc'] = (string) $cc;
		if($bcc !== null) $parameters['bcc'] = (string) $bcc;
		$parameters['subject'] = (string) $from;
		$parameters['format'] = (string) $format;
		$parameters['body'] = (string) $body;

		// make the call
		return $this->doCall('SendMail', $parameters);
	}
}


/**
 * Teamdesk Exception class
 *
 * @author	Tijs Verkoyen <php-teamdesk@verkoyen.eu>
 */
class TeamdeskException extends Exception
{
}

?>