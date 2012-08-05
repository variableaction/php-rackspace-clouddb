#php-rackspace-clouddb - *work-in-progress*

PHP Class for connecting applications to [Rackspace's Cloud Databases Service](http://www.rackspace.com/cloud/cloud_hosting_products/databases/)



**It helps view and manage:**

- API Connection
- Instances
- Database
- Users
- Flavors



##Usage - Connecting

**Example One - Simple Connection**

	<?php

		$rcdb = new RackspaceCloudDB('example user','123abc123abc123abc123abc123abc12','123456');
	
	?>
	
**Example Two - Connection to Alternative Datacenter**

	<?php
	
		$rcdb = new RackspaceCloudDB('example user','123abc123abc123abc123abc123abc12','123456','DFW');
	
	?>
	
**Example Three - Connection to London Datacenter using London Authentication**

	<?php
	
		$rcdb = new RackspaceCloudDB('example user','123abc123abc123abc123abc123abc12','123456','LON','UK');
		
	?>

##Usage - Connecting

**Example One - Manual Token Generation**

	<?php
	
		// Set up connection (with automatic token generation off)
		$rcdb = new RackspaceCloudDB('example user','123abc123abc123abc123abc123abc12','123456','ORD','US',false);
		
		// Create Token
		$rcdb->createAuthToken();
			
	?>
	
**Example Two - List Instances**

	<?php
	
		// Set up connection
		$rcdb = new RackspaceCloudDB('example user','123abc123abc123abc123abc123abc12','123456','DFW');
	
		// execute request
		$responseObject = $rcdb->listInstances();
		
		// View Response
		echo '<pre>'.print_r($responseObject,true).'</pre>';
	
	?>
	
##Additional Resources



[Getting Started Guide](http://docs.rackspace.com/cdb/api/cdb-getting-started-latest/index.html)

[API Developer Guide](http://docs.rackspace.com/cdb/api/cdb-devguide-latest/index.html)

[How-to and Other Resources](http://www.rackspace.com/knowledge_center/content/cloud-databases-how-articles-other-resources)

[Code Samples and Bindings](http://www.rackspace.com/knowledge_center/content/cloud-databases-sample-code-bindings)

[Release Notes](http://docs.rackspace.com/cdb/api/cdb-releasenotes-latest/index.html)


##License

Unless otherwise noted, all files are released under the MIT license, exceptions contain licensing information in them.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.