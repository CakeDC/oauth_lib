# Oauth library plugin for CakePHP #

Version 1.1 for cake 2.x

Oauth library is implementation of the [OAuth 1.0 Protocol](http://tools.ietf.org/html/rfc5849).

## Oauth Consumer implementation ##

Initialize consumer:

	$Consumer = new Consumer($options['oauth_consumer_key'], $options['oauth_consumer_secret'], $options);
	$Consumer->http = new HttpSocket($options['uri']);
	$Consumer->init($options['oauth_consumer_key'], $options['oauth_consumer_secret'], $options);

Retrieve request token:

	$RequestToken = $Consumer->getRequestToken(array('oauth_callback' => $options['oauth_callback']), 	array('scope' => @$options['scope']));

Retrieve access token:

	$AccessToken = $RequestToken->getAccessToken(array('oauth_verifier' => $oauthVerifier));

Perform request to protected URI:

	$response = $AccessToken->request('GET', $protectedUri);
	$response = $AccessToken->request('POST', $protectedUri);
	$response = $AccessToken->get($protectedUri);

## Brief description of oauth ##

OAuth introduces a third role to the traditional client-server authentication model: the resource owner.  In the OAuth model, the client (which is not the resource owner, but is acting on its behalf) requests access to resources controlled by the resource owner, but hosted by the server.  In addition, OAuth allows the server to verify not only the resource owner authorization, but also the identity of the client making the request.

OAuth provides a method for clients to access server resources on behalf of a resource owner (such as a different client or an end-user).  It also provides a process for end-users to authorize third-party access to their server resources without sharing their credentials (typically, a username and password pair), using UserAgent redirections.

For example, a web user (resource owner) can grant a printing service (client) access to her private photos stored at a photo sharing service (server), without sharing her username and password with the printing service. Instead, she authenticates directly with the photo sharing service which issues the printing service delegation-specific credentials.

  
## Oauth CakePHP testing shell ##
	
### Supported commands: ###

 * authorize - used for retrieve access token and secret by user
 * call      - Do call to oauth protected resource
 * debug     - Generate and print OAuth signature
 * sign      - Generate an OAuth signature

Usage: cake oauth [options] <command>

 * body                    - Use the request body for OAuth parameters.") do
 * consumer_key KEY        - Specifies the consumer key to use.") do |v|
 * consumer_secret SECRET  - Specifies the consumer secret to use.") do |v|
 * header                  - Use the Authorization header for OAuth parameters (default).
 * query_string            - Use the query string for OAuth parameters.

Options for signing and querying

 * method METHOD           - Specifies the method (e.g. GET) to use when signing.
 * nonce NONCE             - Specifies the none to use.
 * parameters PARAMETERS   - Specifies the parameters to use when signing.
 * signature-method METHOD - Specifies the signature method to use; defaults to HMAC-SHA1.
 * secret SECRET           - Specifies the token secret to use.
 * timestamp TIMESTAMP     - Specifies the timestamp to use.
 * token TOKEN             - Specifies the token to use.
 * realm REALM             - Specifies the realm to use.
 * uri URI                 - Specifies the URI to use when signing.
 * version VERSION         - Specifies the OAuth version to use.
 * no_version              - Omit oauth_version.
 * debug                   - Be verbose.
	
Options for authorization

 * access_token_url URL    - Specifies the access token URL.
 * authorize_url URL       - Specifies the authorization URL.
 * callback_url URL        - Specifies a callback URL.") do |v|
 * request_token_url URL   - Specifies the request token URL.
 * scope SCOPE             - Specifies the scope (Google-specific).
	

## Requirements ##

* PHP version: PHP 5.2+
* CakePHP version: Cakephp 1.3 Stable

## Support ##

For support and feature request, please visit the [OauthLib Plugin Support Site](http://cakedc.lighthouseapp.com/projects/60476-oauthlib-plugin/).

For more information about our Professional CakePHP Services please visit the [Cake Development Corporation website](http://cakedc.com).

## Branch strategy ##

The master branch holds the STABLE latest version of the plugin. 
Develop branch is UNSTABLE and used to test new features before releasing them. 

Previous maintenance versions are named after the CakePHP compatible version, for example, branch 1.3 is the maintenance version compatible with CakePHP 1.3.
All versions are updated with security patches.

## Contributing to this Plugin ##

Please feel free to contribute to the plugin with new issues, requests, unit tests and code fixes or new features. If you want to contribute some code, create a feature branch from develop, and send us your pull request. Unit tests for new features and issues detected are mandatory to keep quality high. 


## License ##

Copyright 2009-2010, [Cake Development Corporation](http://cakedc.com)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

## Copyright ###

Copyright 2009-2011<br/>
[Cake Development Corporation](http://cakedc.com)<br/>
1785 E. Sahara Avenue, Suite 490-423<br/>
Las Vegas, Nevada 89104<br/>
http://cakedc.com<br/>
