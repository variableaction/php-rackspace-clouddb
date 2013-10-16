#!/usr/bin/php -q
<?php
/**
 * Simple command line script to show how to use the Rackspace CloudDB class.
 */
require_once('rackspace-clouddb.class.php');

const RACKSPACE_USERNAME    = 'YOUR_USERNAME';
const RACKSPACE_API_KEY     = 'YOUR_API_KEY';
const RACKSPACE_ACCOUNT_ID  = 'YOUR_ACCOUNT_ID';
const RACKSPACE_INSTANCE_ID = 'AN_INSTANCE_ID';

$database = 'my-test-database';

// Connect to the Chicago datacenter
//$rcdb = new RackspaceCloudDB(RACKSPACE_USERNAME,RACKSPACE_API_KEY,RACKSPACE_ACCOUNT_ID,'ORD','US');
// Connect to the Dallas/Ft. Worth datacenter
//$rcdb = new RackspaceCloudDB(RACKSPACE_USERNAME,RACKSPACE_API_KEY,RACKSPACE_ACCOUNT_ID,'DFW','US');
// Connect to the London datacenter
$rcdb = new RackspaceCloudDB(RACKSPACE_USERNAME,RACKSPACE_API_KEY,RACKSPACE_ACCOUNT_ID,'LON','UK');

// List all instances
$responseObject = $rcdb->listInstances();

// List all database users in an instance
//$responseObject = $rcdb->listDatabaseInstanceUsers(RACKSPACE_INSTANCE_ID);

// List all flavors (hardware profiles)
//$responseObject = $rcdb->listFlavors();

// Create a new database
//$responseObject = $rcdb->createDatabase(RACKSPACE_INSTANCE_ID, $database);

// Create a new user
//$responseObject = $rcdb->createUser(RACKSPACE_INSTANCE_ID,$database,'inkrato-app','mkfp28mt');

// Grant a user access to a database
//$responseObject = $rcdb->grantUserAccess(RACKSPACE_INSTANCE_ID, $database, 'inkrato-app');

// Revoke a users access to a database
//$responseObject = $rcdb->revokeUserAccess(RACKSPACE_INSTANCE_ID, $database, 'inkrato-app');

// NB: Some operations (such as creating a database or a user) don't return a
// JSON response object unless there an error occurs.
echo '<pre>'.print_r($responseObject,true).'</pre>';

?>