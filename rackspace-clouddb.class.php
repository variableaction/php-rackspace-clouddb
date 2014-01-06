<?php
/**
 * RackspaceCloudDB Class for connecting with Rackspace's Cloud Database Service
 *
 * Based on documentation provided at
 * http://docs.rackspace.com/cdb/api/v1.0/cdb-devguide/content/API_Operations-d1e2264.html
 *
 * Note: When attempting to verify operations via the Rackspace Control Panel, you may
 * need to refresh the page to see it reflect changes made via calls to the API.
 *
 * LICENSE: see README.md
 *
 * @author      Andy Fleming <afleming@variableaction.com>
 * @author      Iain Collins <me@iaincollins.com>
 * @version     1.1
 * @link        https://github.com/variableaction/php-rackspace-clouddb
 */

class RackspaceCloudDB {

    private $account;
    private $authToken;
    
    /**
     * @param   string  $username   Your Rackspace username
     * @param   string  $apiKey     Your Rackspace API Key
     * @param   string  $accountId  Your Rackspace Account ID
     * @param   string  $datacenter Your datacenter e.g.'ORD' (Chicago - Default), 'DFW' (Dallas/Ft. Worth), 'LON' (London)
     * @param   string  $authGeo    Your authentication region e.g. 'US' (Default), 'UK'
     * @param   bool    $automaticallyCreateToken   Default true
     */
    public function __construct($username,$apiKey,$accountId,$datacenter='ORD',$authGeo='US',$automaticallyCreateToken=true) {
    
        // Set Account Details
        $this->account->username    = $username;
        $this->account->apiKey      = $apiKey;
        $this->account->id          = $accountId;

        // Set/generate API endpoints
        $this->setEndpoints($datacenter, $authGeo);

        // Automatically create token (if $automaticallyCreateToken is true)
        if ($automaticallyCreateToken)
            $this->createAuthToken();
    }

    /**
     * Set endpoints for requests (where they need to go) based on region and datacenter.
     *
     * @param   string  $datacenter Your datacenter e.g.'ORD' (Chicago - Default), 'LON' (London)
     * @param   string  $authGeo    Your authentication region e.g. 'US' (Default), 'UK'
     */
    private function setEndpoints($datacenter,$authGeo) {
    
        // Set auth endpoint based on region
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

        // Set clouddb endpoint based on specific datacenter
        switch ($datacenter) {
            
            case 'LON': // London
                $this->api->endpoints->clouddb = 'https://lon.databases.api.rackspacecloud.com/v1.0/';
                break;
            case 'DFW': // Dallas/Ft. Worth
                $this->api->endpoints->clouddb = 'https://dfw.databases.api.rackspacecloud.com/v1.0/';
                break;
            case 'ORD': // Chicago
            default:
                $this->api->endpoints->clouddb = 'https://ord.databases.api.rackspacecloud.com/v1.0/';
                break;
        }

        // Append account number for endpoint
        $this->api->endpoints->clouddb = "{$this->api->endpoints->clouddb}{$this->account->id}/";

    }

    /**
     * Make requests using libcurl
     */     
    private function basicCURL($url,$headers=array(),$data=null,$additionalOptions=null) {

        // setup handler
        $ch = curl_init($url);

        // Set options
        curl_setopt_array($ch,array(
                CURLOPT_HTTPHEADER        => $headers,
                CURLOPT_RETURNTRANSFER    => 1
            ));

        // if there is data, attach it to the request
        if ($data) {
            curl_setopt_array($ch,array(
                    CURLOPT_POST            => 1,
                    CURLOPT_POSTFIELDS        => $data,
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

    /**
     * Create a new authtoken used for all subsequent requests
     */
    public function createAuthToken() {
    
        $this->api->endpoints->auth;

        $requestObject->credentials->username   = $this->account->username;
        $requestObject->credentials->key        = $this->account->apiKey;

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

    /**
     * List all available flavours.
     *
     * From the Rackspace documentation:
     * "A flavor is an available hardware configuration for a database instance. 
     * Each flavor has a unique combination of memory capacity and priority for CPU time."
     */
    public function listFlavors() {
        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/flavors',
            array('X-Auth-Token: '.$this->authToken,'Accept: application/json','Accept: application/json')
        );
        return json_decode($response);
    }

    /**
     * List all avalible clouddb instances on an account
     */
    public function listInstances() {
        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json')
        );
        return json_decode($response);
    }

    /**
     * Create a new clouddb instance.
     *
     * The $sizeInGigabytes must be between 1 and 150. It can be increased later, but not decreased.
     *
     * @param   string  $instanceName       A user firendly name for the new instance
     * @param   int     $flavour            An ID of the flavour (i.e. hardware config) for this instance (default '1')
     * @param   int     $sizeInGigabytes    The size of the volume in Gigabytes (Default 1 GB, can be increased later)
     */
    public function createInstance($instanceName,$flavor='1',$sizeInGigabytes='1') {

        $requestObject->instance->flavorRef = $this->api->endpoints->clouddb.'/flavors/'.$flavor;
        $requestObject->instance->name      = $instanceName;
        $requestObject->instance->volume    = (object) array('size' => $sizeInGigabytes);

        $jsonString = json_encode($requestObject);

        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
            $jsonString
        );

        return json_decode($response);
    }

    /**
     * Delete a database instance
     *
     * @param   string  $instanceId The ID of the instance to delete
     */
    public function deleteInstance($instanceId) {
        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId,
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
            null,
            array(CURLOPT_CUSTOMREQUEST => "DELETE")
        );
        return json_decode($response);
    }

    /**
     * Delete a database instance
     *
     * NB: Database names cannot contain periods (MySQL allows this but Rackspace do not).
     *
     * @param   string  $instanceId An instance ID
     * @param   string  $database     The name of the new database (can contain A-z, 0-9 - and _)
     */
    public function createDatabase($instanceId,$database) {

        $requestObject->databases[] = (object) array("name" => $database);

        $jsonString = json_encode($requestObject);

        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId.'/databases',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
            $jsonString
        );

        return json_decode($response);

    }

    /**
     * List all databases in a given instance.
     *
     * @param   string  $instanceId An instance ID
     */
    public function listDatabaseInstanceDatabases($instanceId) {
        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId.'/databases',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json')

        );
        return json_decode($response);
    }

    /**
     * List all users in a given instance.
     *
     * @param   string  $instanceId An instance ID
     */
    public function listDatabaseInstanceUsers($instanceId) {
        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId.'/users',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json')

        );
        return json_decode($response);
    }

    /**
     * Create a user in an instance, and grant them access to a specific database.
     *
     * Simply returns a status code of 200 on success.
     *
     * @param   string  $instanceId An instance ID
     * @param   string  $database   A database to grant them access to
     * @param   string  $username   A username for the new user
     * @param   string  $password   A password for the new user
     */
    public function createUser($instanceId,$database,$username,$password) {
        $requestObject->users[] = (object) array(
            "databases" => array((object) array("name" => $database)),
            "name"        => $username,
            "password"    => $password
        );
        
        $jsonString = json_encode($requestObject);

        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId.'/users',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
            $jsonString
        );

        return json_decode($response);
    }

    /**
     * Grant a user access to a specific database
     *
     * Simply returns a status code of 200 on success.
     *
     * @param   string  $instanceId The ID of the instance the database is in
     * @param   string  $database   The database to grant access to
     * @param   string  $username   The user to be granted access
     */
    public function grantUserAccess($instanceId,$database,$username) {

        $requestObject->databases[] = (object) array("name" => $database);

        $jsonString = json_encode($requestObject);

        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId.'/users/'.$username.'/databases',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
            $jsonString,
            array(CURLOPT_CUSTOMREQUEST => "PUT")
        );

        return json_decode($response);

    }

    /**
     * Revoke a users access to a specific database
     *
     * Simply returns a status code of 200 on success.
     *
     * @param   string  $instanceId The ID of the instance the database is in
     * @param   string  $database   The database to revoke access from
     * @param   string  $username   The user to revoke access from
     */
    public function revokeUserAccess($instanceId,$database,$username) {
        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId.'/users/'.$username.'/databases/'.$database,
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
            null,
            array(CURLOPT_CUSTOMREQUEST => "DELETE")
        );
        return json_decode($response);
    }

    /**
     * Restart an instance.
     *
     * @param   string  $instanceId The ID of the instance to restart
     */
    public function restartInstance($instanceId) {
        $response = $this->basicCURL(
            $this->api->endpoints->clouddb.'/instances/'.$instanceId.'/action',
            array('X-Auth-Token: '.$this->authToken,'Content-Type: application/json','Accept: application/json'),
            '{"restart": {}}'
        );
        return json_decode($response);
    }

}
?>