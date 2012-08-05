<?php
###########################################################################################################
/**
 * RackspaceCloudDB Class for connecting with Rackspace's Cloud Database Service
 *
 * LICENSE: see README.md
 *
 * @author     Andy Fleming <afleming@variableaction.com>
 * @link       https://github.com/variableaction/php-rackspace-clouddb
*/
###########################################################################################################

	class RackspaceCloudDB {
		
		private $account;
		private $authToken;
				
	# -----------------------------------------------------------------------------------
	#	__construct()
	#		stores account details, generates endpoints, and generates token
	# -----------------------------------------------------------------------------------
	
		public function __construct($username,$apiKey,$accountID,$datacenter='ORD',$authGeo='US',$automaticallyCreateToken=true) {
			
			// Set Account Details
			$this->account->username	= $username;
			$this->account->apiKey		= $apiKey;
			$this->account->id			= $accountID;
			
			// Set/generate API endpoints
			$this->setEndpoints($authGeo,$datacenter);
						
			// Automatically create token (if $automaticallyCreateToken is true)
			if ($automaticallyCreateToken) { $this->createAuthToken(); }
			
		}
	
	# ---------------------------------------------------------------------------------
	#	setEndpoints()
	#		defaults to US and Chicago Datacenter (ORD)
	# ---------------------------------------------------------------------------------
	
		private function setEndpoints($authGeo,$datacenter) {
		
		// ----------------------------------------------------------------------
		//	Set auth endpoint
		// ----------------------------------------------------------------------
			
			switch ($authGeo) {
				
				case 'UK':
					$this->api->endpoints->auth = 'https://lon.identity.api.rackspacecloud.com/v1.1/auth';
					//$this->api->endpoints->auth = 'https://lon.identity.api.rackspacecloud.com/v2.0/';
					break;
				
				case 'US':
				default:
					$this->api->endpoints->auth = 'https://identity.api.rackspacecloud.com/v1.1/auth';
					//$this->api->endpoints->auth = 'https://identity.api.rackspacecloud.com/v2.0/';
					break;
				
			}
			
		// ----------------------------------------------------------------------
		//	Set datacenter endpoint
		// ----------------------------------------------------------------------

			switch ($datacenter) {
				
				// London
				case 'LON':
					$this->api->endpoints->clouddb = 'https://lon.databases.api.rackspacecloud.com/v1.0/';
					break;
				
				// Dallas/Ft. Worth
				case 'DFW':
					$this->api->endpoints->clouddb = 'https://dfw.databases.api.rackspacecloud.com/v1.0/';
					break;
				
				// Chicago
				case 'ORD':
				default:
					$this->api->endpoints->clouddb = 'https://ord.databases.api.rackspacecloud.com/v1.0/';
					break;
				
			}
						
			
			// Append account number for endpoint
			$this->api->endpoints->clouddb = "{$this->api->endpoints->clouddb}{$this->account->id}/";
			
		}
		
	# -----------------------------------------------------------------------------------
	#	basicCURL()
	# -----------------------------------------------------------------------------------
	
		private function basicCURL($url,$headers=array(),$data=null,$additionalOptions=null) {
			
			// setup handler
			$ch = curl_init($url);
			
			// Set options
			curl_setopt_array($ch,array(
				CURLOPT_HTTPHEADER		=> $headers,
				CURLOPT_RETURNTRANSFER	=> 1
			));
			
			// if there is data, attach it to the request
			if ($data) {
				curl_setopt_array($ch,array(
					CURLOPT_POST			=> 1,
					CURLOPT_POSTFIELDS		=> $data,
				));
			}
			
			// add additional options if sent
			if ($additionalOptions) {
				curl_setopt_array($ch,$additionalOptions);
			}
			
			// execute request
			$response = curl_exec($ch);       
			
			// close connection
			curl_close($ch);
			
			// return reponse
			return $response;
			
		}
	
	# -----------------------------------------------------------------------------------
	#	createAuthToken()
	# -----------------------------------------------------------------------------------
	
		public function createAuthToken() {
			
			$this->api->endpoints->auth;
			
			$requestObject->credentials->username		= $this->account->username;
			$requestObject->credentials->key			= $this->account->apiKey;
			
			$jsonString = json_encode($requestObject);
			
			$response = $this->basicCURL(
				$this->api->endpoints->auth,
				array('Content-Type: application/json'),
				$jsonString
			);
						
			$objectFromResponse = json_decode($response);
			
			// Set auth token
			$this->authToken = $objectFromResponse->auth->token->id;
			
		}
		
	# -----------------------------------------------------------------------------------
	#	listFlavors()
	# -----------------------------------------------------------------------------------
	
		public function listFlavors() {
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/flavors/detail',
				array('X-Auth-Token: '.$this->authToken,'Accept: application/json','Accept: application/json')
			);
			return json_decode($response);
		}
	
			
	# -----------------------------------------------------------------------------------
	#	listInstances()
	# -----------------------------------------------------------------------------------
		
		public function listInstances() {
						
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/instances',
				array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json')
				
			);
			
			//$objectFromResponse = json_decode($response);
			
			return json_decode($response);
			
		}
		
	# -----------------------------------------------------------------------------------
	#	createInstance()
	# -----------------------------------------------------------------------------------
	
		public function createInstance($instanceName,$flavor='1',$volumeSize='2') {
       
			$requestObject->instance->flavorRef		= $this->api->endpoints->clouddb.'/flavors/'.$flavor;
			$requestObject->instance->name			= $instanceName;
			$requestObject->instance->volume		= (object) array('size' => $volumeSize);
       
			$jsonString = json_encode($requestObject);
			
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/instances',
				array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
				$jsonString
			);
			
			return json_decode($response);
			
		}
	
	
	# -----------------------------------------------------------------------------------
	#	deleteInstance() -- tested; works
	# -----------------------------------------------------------------------------------
	
		public function deleteInstance($instanceID) {
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/instances/'.$instanceID,
				array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
				null,
				array(CURLOPT_CUSTOMREQUEST => "DELETE")
			);
			return json_decode($response);		
		}
	
	# -----------------------------------------------------------------------------------
	#	createDatabase()
	# -----------------------------------------------------------------------------------
	
		public function createDatabase($instanceID,$dbName) {
			
			$requestObject->databases[] = (object) array("name" => $dbName);
			       
			$jsonString = json_encode($requestObject);
			
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/instances/'.$instanceID.'/databases',
				array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
				$jsonString
			);
			
			return json_decode($response);
			
		}
	
	
	# -----------------------------------------------------------------------------------
	#	listDatabaseInstanceDatabases()
	# -----------------------------------------------------------------------------------
		
		public function listDatabaseInstanceDatabases($instanceID) {
			
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/instances/'.$instanceID.'/databases',
				array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json')
				
			);
			
			return json_decode($response);
			
			
		}
		
	# -----------------------------------------------------------------------------------
	#	listDatabaseInstanceUsers()
	# -----------------------------------------------------------------------------------
		
		public function listDatabaseInstanceUsers($instanceID) {
			
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/instances/'.$instanceID.'/users',
				array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json')
				
			);
			
			return json_decode($response);
			
			
		}
		
	# -----------------------------------------------------------------------------------
	#	createUser() -- creates with access to a specific database
	#		looks like the API response to this is just a 202 header
	# -----------------------------------------------------------------------------------
	
		public function createUser($instanceID,$dbName,$username,$password) {	
			
			$requestObject->users[] = (object) array(
				"databases" => array((object) array("name" => $dbName)),
				"name"		=> $username,
				"password"	=> $password
			);
			
			       
			$jsonString = json_encode($requestObject);
			
			$response = $this->basicCURL(
				$this->api->endpoints->clouddb.'/instances/'.$instanceID.'/users',
				array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
				$jsonString
			);
			
			return json_decode($response);
		}
	
	
	
	
	}
	
?>