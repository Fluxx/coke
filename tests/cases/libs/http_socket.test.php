<?php
/* SVN FILE: $Id: http_socket.test.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 * @link				https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package			cake.tests
 * @subpackage		cake.tests.cases.libs
 * @since			CakePHP(tm) v 1.2.0.4206
 * @version			$Revision: 6311 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2008-01-02 00:33:52 -0600 (Wed, 02 Jan 2008) $
 * @license			http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
uses('http_socket');
/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs
 */
class HttpSocketTest extends UnitTestCase {

	var $Socket = null;

	var $RequestSocket = null;
/**
 * This function sets up a TestHttpSocket instance we are going to use for testing
 *
 */
	function setUp() {
		if (!class_exists('TestHttpSocket')) {
			Mock::generatePartial('HttpSocket', 'TestHttpSocket', array('read', 'write', 'connect'));
			Mock::generatePartial('HttpSocket', 'TestHttpSocketRequests', array('read', 'write', 'connect', 'request'));
		}

		$this->Socket =& new TestHttpSocket();
		$this->RequestSocket =& new TestHttpSocketRequests();
	}
/**
 * We use this function to clean up after the test case was executed
 *
 */
	function tearDown() {
		unset($this->Socket, $this->RequestSocket);
	}
/**
 * Test that HttpSocket::__construct does what one would expect it to do
 *
 */
	function testConstruct() {
		$this->Socket->reset();
		$baseConfig = $this->Socket->config;
		$this->Socket->expectNever('connect');
		$this->Socket->__construct(array('host' => 'foo-bar'));
		$baseConfig['host']	 = 'foo-bar';
		$baseConfig['protocol'] = getprotobyname($baseConfig['protocol']);
		$this->assertIdentical($this->Socket->config, $baseConfig);
		$this->Socket->reset();
		$baseConfig = $this->Socket->config;
		$this->Socket->__construct('http://www.cakephp.org:23/');
		$baseConfig['host']	 = 'www.cakephp.org';
		$baseConfig['request']['uri']['host'] = 'www.cakephp.org';
		$baseConfig['port']	 = 23;
		$baseConfig['request']['uri']['port'] = 23;
		$baseConfig['protocol'] = getprotobyname($baseConfig['protocol']);
		$this->assertIdentical($this->Socket->config, $baseConfig);
	}

/**
 * Test that HttpSocket::configUri works properly with different types of arguments
 *
 */
	function testConfigUri() {
		$this->Socket->reset();
		$r = $this->Socket->configUri('https://bob:secret@www.cakephp.org:23/?query=foo');
		$expected = array(
			'persistent' => false,
			'host' 		 => 'www.cakephp.org',
			'protocol'   => 'tcp',
			'port' 		 => 23,
			'timeout' 	 =>	30,
			'request' => array(
				'uri' => array(
					'scheme' => 'https'
					, 'host' => 'www.cakephp.org'
					, 'port' => 23
				),
				'auth' => array(
					'method' => 'basic'
					, 'user' => 'bob'
					, 'pass' => 'secret'
				),
				'cookies' => array(),
			)
		);
		$this->assertIdentical($this->Socket->config, $expected);
		$this->assertIdentical($r, $expected);
		$r = $this->Socket->configUri(array('host' => 'www.foo-bar.org'));
		$expected['host'] = 'www.foo-bar.org';
		$expected['request']['uri']['host'] = 'www.foo-bar.org';
		$this->assertIdentical($this->Socket->config, $expected);
		$this->assertIdentical($r, $expected);

		$r = $this->Socket->configUri('http://www.foo.com');
		$expected = array(
			'persistent' => false,
			'host' 		 => 'www.foo.com',
			'protocol'   => 'tcp',
			'port' 		 => 80,
			'timeout' 	 =>	30,
			'request' => array(
				'uri' => array(
					'scheme' => 'http'
					, 'host' => 'www.foo.com'
					, 'port' => 80
				),
				'auth' => array(
					'method' => 'basic'
					, 'user' => null
					, 'pass' => null
				),
				'cookies' => array()
			)
		);
		$this->assertIdentical($this->Socket->config, $expected);
		$this->assertIdentical($r, $expected);
		$r = $this->Socket->configUri('/this-is-fuck');
		$this->assertIdentical($this->Socket->config, $expected);
		$this->assertIdentical($r, false);
		$r = $this->Socket->configUri(false);
		$this->assertIdentical($this->Socket->config, $expected);
		$this->assertIdentical($r, false);
	}

/**
 * Tests that HttpSocket::request (the heart of the HttpSocket) is working properly.
 *
 */
	function testRequest() {
		$this->Socket->reset();

		$this->Socket->reset();
		$response = $this->Socket->request(true);
		$this->assertFalse($response);

		$tests = array(
			0 => array(
				'request' => 'http://www.cakephp.org/?foo=bar'
				, 'expectation' => array(
					'config' => array(
						'persistent' => false
						, 'host' => 'www.cakephp.org'
						, 'protocol' => 'tcp'
						, 'port' => 80
						, 'timeout' => 30
						, 'request' => array(
							'uri' => array (
								'scheme' => 'http'
								, 'host' => 'www.cakephp.org'
								, 'port' => 80,
							)
							, 'auth' => array(
								'method' => 'basic'
								,'user' => null
								,'pass' => null
							),
							'cookies' => array(),
						),
					)
					, 'request' => array(
						'method' => 'GET'
						, 'uri' => array(
							'scheme' => 'http'
							, 'host' => 'www.cakephp.org'
							, 'port' => 80
							, 'user' => null
							, 'pass' => null
							, 'path' => '/'
							, 'query' => array('foo' => 'bar')
							, 'fragment' => null
						)
						, 'auth' => array(
							'method' => 'basic'
							, 'user' => null
							, 'pass' => null
						)
						, 'version' => '1.1'
						, 'body' => ''
						, 'line' => "GET /?foo=bar HTTP/1.1\r\n"
						, 'header' => "Host: www.cakephp.org\r\nConnection: close\r\nUser-Agent: CakePHP\r\n"
						, 'raw' => ""
						, 'cookies' => array(),
					)
				)
			)
			, 1 => array(
				'request' => array(
					'uri' => array(
						'host' => 'www.cakephp.org'
						, 'query' => '?foo=bar'
					)
				)
			)
			, 2 => array(
				'request' => 'www.cakephp.org/?foo=bar'
			)
			, 3 => array(
				'request' => array('host' => '192.168.0.1', 'uri' => 'http://www.cakephp.org/?foo=bar')
				, 'expectation' => array(
					'request' => array(
						'uri' => array('host' => 'www.cakephp.org')
					)
					, 'config' => array(
						'request' => array(
							'uri' => array('host' => 'www.cakephp.org')
						)
						, 'host' => '192.168.0.1'
					)
				)
			)
			, 'reset4' => array(
				'request.uri.query' => array()
			)
			, 4 => array(
				'request' => array('header' => array('Foo@woo' => 'bar-value'))
				, 'expectation' => array(
					'request' => array(
						'header' => "Host: www.cakephp.org\r\nConnection: close\r\nUser-Agent: CakePHP\r\nFoo\"@\"woo: bar-value\r\n"
						, 'line' => "GET / HTTP/1.1\r\n"
					)
				)
			)
			, 5 => array(
				'request' => array('header' => array('Foo@woo' => 'bar-value', 'host' => 'foo.com'), 'uri' => 'http://www.cakephp.org/')
				, 'expectation' => array(
					'request' => array(
						'header' => "Host: foo.com\r\nConnection: close\r\nUser-Agent: CakePHP\r\nFoo\"@\"woo: bar-value\r\n"
					)
					, 'config' => array(
						'host' => 'www.cakephp.org'
					)
				)
			)
			, 6 => array(
				'request' => array('header' => "Foo: bar\r\n")
				, 'expectation' => array(
					'request' => array(
						'header' => "Foo: bar\r\n"
					)
				)
			)
			, 7 => array(
				'request' => array('header' => "Foo: bar\r\n", 'uri' => 'http://www.cakephp.org/search?q=http_socket#ignore-me')
				, 'expectation' => array(
					'request' => array(
						'uri' => array(
							'path' => '/search'
							, 'query' => array('q' => 'http_socket')
							, 'fragment' => 'ignore-me'
						)
						, 'line' => "GET /search?q=http_socket HTTP/1.1\r\n"
					)
				)
			)
			, 'reset8' => array(
				'request.uri.query' => array()
			)
			, 8 => array(
				'request' => array('method' => 'POST', 'uri' => 'http://www.cakephp.org/posts/add', 'body' => array('name' => 'HttpSocket-is-released', 'date' => 'today'))
				, 'expectation' => array(
					'request' => array(
						'method' => 'POST'
						, 'uri' => array(
							'path' => '/posts/add'
							, 'fragment' => null
						)
						, 'body' => "name=HttpSocket-is-released&date=today"
						, 'line' => "POST /posts/add HTTP/1.1\r\n"
						, 'header' => "Host: www.cakephp.org\r\nConnection: close\r\nUser-Agent: CakePHP\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 38\r\n"
						, 'raw' => "name=HttpSocket-is-released&date=today"
					)
				)
			)
			, 9 => array(
				'request' => array('method' => 'POST', 'uri' => 'https://www.cakephp.org/posts/add', 'body' => array('name' => 'HttpSocket-is-released', 'date' => 'today'))
				, 'expectation' => array(
					'config' => array(
						'port' => 443
						, 'request' => array(
							'uri' => array(
								'scheme' => 'https'
								, 'port' => 443
							)
						)
					)
					, 'request' => array(
						'uri' => array(
							'scheme' => 'https'
							, 'port' => 443
						)
					)
				)
			)
			, 10 => array(
				'request' => array(
						'method' => 'POST',
						'uri' => 'https://www.cakephp.org/posts/add',
						'body' => array('name' => 'HttpSocket-is-released', 'date' => 'today'),
						'cookies' => array('foo' => array('value' => 'bar'))
				)
				, 'expectation' => array(
					'request' => array(
						'header' => "Host: www.cakephp.org\r\nConnection: close\r\nUser-Agent: CakePHP\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 38\r\nCookie: foo=bar\r\n",
						'cookies' => array(
							'foo' => array('value' => 'bar'),
						)
					)
				)
			)
		);

		$expectation = array();
		foreach ($tests as $i => $test) {
			if (strpos($i, 'reset') === 0) {
				foreach ($test as $path => $val) {
					$expectation = Set::insert($expectation, $path, $val);
				}
				continue;
			}

			if (isset($test['expectation'])) {
				$expectation = Set::merge($expectation, $test['expectation']);
			}
			$this->Socket->request($test['request']);

			$raw = $expectation['request']['raw'];
			$expectation['request']['raw'] = $expectation['request']['line'].$expectation['request']['header']."\r\n".$raw;

			$r = array('config' => $this->Socket->config, 'request' => $this->Socket->request);
			$v = $this->assertIdentical($r, $expectation, '%s in test #'.$i.' ');
			if (!$v) {
				debug('Result:');
				debug($r);
				debug('Expected:');
				debug($expectation);
			}
			$expectation['request']['raw'] = $raw;
		}

		$this->Socket->reset();
		$request = array('method' => 'POST', 'uri' => 'http://www.cakephp.org/posts/add', 'body' => array('name' => 'HttpSocket-is-released', 'date' => 'today'));
		$response = $this->Socket->request($request);
		$this->assertIdentical($this->Socket->request['body'], "name=HttpSocket-is-released&date=today");

		$request = array('uri' => '*', 'method' => 'GET');
		$this->expectError(new PatternExpectation('/activate quirks mode/i'));
		$response = $this->Socket->request($request);
		$this->assertFalse($response);
		$this->assertFalse($this->Socket->response);

		$this->Socket->reset();
		$request = array('uri' => 'htpp://www.cakephp.org/');
		$this->Socket->setReturnValue('connect', true);
		$this->Socket->setReturnValue('read', false);
		$this->Socket->_mock->_call_counts['read'] = 0;
		$number = rand(0, 9999999);
		$serverResponse = "HTTP/1.x 200 OK\r\nDate: Mon, 16 Apr 2007 04:14:16 GMT\r\nServer: CakeHttp Server\r\nContent-Type: text/html\r\n\r\n<h1>Hello, your lucky number is ".$number."</h1>";
		$this->Socket->setReturnValueAt(0, 'read', $serverResponse);
		$this->Socket->expect('write', array("GET / HTTP/1.1\r\nHost: www.cakephp.org\r\nConnection: close\r\nUser-Agent: CakePHP\r\n\r\n"));
		$this->Socket->expectCallCount('read', 2);
		$response = $this->Socket->request($request);
		$this->assertIdentical($response, "<h1>Hello, your lucky number is ".$number."</h1>");

		$this->Socket->reset();
		$serverResponse = "HTTP/1.x 200 OK\r\nSet-Cookie: foo=bar\r\nDate: Mon, 16 Apr 2007 04:14:16 GMT\r\nServer: CakeHttp Server\r\nContent-Type: text/html\r\n\r\n<h1>This is a cookie test!</h1>";
		unset($this->Socket->_mock->_return_sequence['read']);
		$this->Socket->_mock->_call_counts['read'] = 0;
		$this->Socket->setReturnValueAt(0, 'read', $serverResponse);
		$this->Socket->connected = true;
		$this->Socket->request($request);
		$result = $this->Socket->response['cookies'];
		$expect = array(
			'foo' => array(
				'value' => 'bar'
			)
		);
		$this->assertEqual($result, $expect);
		$this->assertEqual($this->Socket->config['request']['cookies'], $expect);
		$this->assertFalse($this->Socket->connected);
	}

	function testUrl() {
		$this->Socket->reset(true);

		$this->assertIdentical($this->Socket->url(true), false);

		$url = $this->Socket->url('www.cakephp.org');
		$this->assertIdentical($url, 'http://www.cakephp.org/');

		$url = $this->Socket->url('https://www.cakephp.org/posts/add');
		$this->assertIdentical($url, 'https://www.cakephp.org/posts/add');
		$url = $this->Socket->url('http://www.cakephp/search?q=socket', '/%path?%query');
		$this->assertIdentical($url, '/search?q=socket');

		$this->Socket->config['request']['uri']['host'] = 'bakery.cakephp.org';
		$url = $this->Socket->url();
		$this->assertIdentical($url, 'http://bakery.cakephp.org/');

		$this->Socket->configUri('http://www.cakephp.org');
		$url = $this->Socket->url('/search?q=bar');
		$this->assertIdentical($url, 'http://www.cakephp.org/search?q=bar');

		$url = $this->Socket->url(array('host' => 'www.foobar.org', 'query' => array('q' => 'bar')));
		$this->assertIdentical($url, 'http://www.foobar.org/?q=bar');

		$url = $this->Socket->url(array('path' => '/supersearch', 'query' => array('q' => 'bar')));
		$this->assertIdentical($url, 'http://www.cakephp.org/supersearch?q=bar');

		$this->Socket->configUri('http://www.google.com');
		$url = $this->Socket->url('/search?q=socket');
		$this->assertIdentical($url, 'http://www.google.com/search?q=socket');

		$url = $this->Socket->url();
		$this->assertIdentical($url, 'http://www.google.com/');

		$this->Socket->configUri('https://www.google.com');
		$url = $this->Socket->url('/search?q=socket');
		$this->assertIdentical($url, 'https://www.google.com/search?q=socket');

		$this->Socket->reset();
		$this->Socket->configUri('www.google.com:443');
		$url = $this->Socket->url('/search?q=socket');
		$this->assertIdentical($url, 'https://www.google.com/search?q=socket');

		$this->Socket->reset();
		$this->Socket->configUri('www.google.com:8080');
		$url = $this->Socket->url('/search?q=socket');
		$this->assertIdentical($url, 'http://www.google.com:8080/search?q=socket');
	}

	function testGet() {
		$this->RequestSocket->reset();

		$this->RequestSocket->expect('request', a(array('method' => 'GET', 'uri' => 'http://www.google.com/')));
		$this->RequestSocket->get('http://www.google.com/');

		$this->RequestSocket->expect('request', a(array('method' => 'GET', 'uri' => 'http://www.google.com/?foo=bar')));
		$this->RequestSocket->get('http://www.google.com/', array('foo' => 'bar'));

		$this->RequestSocket->expect('request', a(array('method' => 'GET', 'uri' => 'http://www.google.com/?foo=bar')));
		$this->RequestSocket->get('http://www.google.com/', 'foo=bar');

		$this->RequestSocket->expect('request', a(array('method' => 'GET', 'uri' => 'http://www.google.com/?foo=23&foobar=42')));
		$this->RequestSocket->get('http://www.google.com/?foo=bar', array('foobar' => '42', 'foo' => '23'));

		$this->RequestSocket->expect('request', a(array('method' => 'GET', 'uri' => 'http://www.google.com/', 'auth' => array('user' => 'foo', 'pass' => 'bar'))));
		$this->RequestSocket->get('http://www.google.com/', null, array('auth' => array('user' => 'foo', 'pass' => 'bar')));
	}

	function testPostPutDelete() {
		$this->RequestSocket->reset();

		foreach (array('POST', 'PUT', 'DELETE') as $method) {
			$this->RequestSocket->expect('request', a(array('method' => $method, 'uri' => 'http://www.google.com/', 'body' => array())));
			$this->RequestSocket->{low($method)}('http://www.google.com/');

			$this->RequestSocket->expect('request', a(array('method' => $method, 'uri' => 'http://www.google.com/', 'body' => array('Foo' => 'bar'))));
			$this->RequestSocket->{low($method)}('http://www.google.com/', array('Foo' => 'bar'));

			$this->RequestSocket->expect('request', a(array('method' => $method, 'uri' => 'http://www.google.com/', 'body' => null, 'line' => 'Hey Server')));
			$this->RequestSocket->{low($method)}('http://www.google.com/', null, array('line' => 'Hey Server'));
		}
	}

/**
 * Enter description here...
 *
 */
	function testParseResponse() {
		$this->Socket->reset();

		$r = $this->Socket->parseResponse(array('foo' => 'bar'));
		$this->assertIdentical($r, array('foo' => 'bar'));

		$r = $this->Socket->parseResponse(true);
		$this->assertIdentical($r, false);

		$r = $this->Socket->parseResponse("HTTP Foo\r\nBar: La");
		$this->assertIdentical($r, false);

		$tests = array(
			'simple-request' => array(
				'response' => array(
					'status-line' => "HTTP/1.x 200 OK\r\n",
					'header' => "Date: Mon, 16 Apr 2007 04:14:16 GMT\r\nServer: CakeHttp Server\r\n",
					'body' => "<h1>Hello World</h1>\r\n<p>It's good to be html</p>"
				)
				, 'expectations' => array(
					'status.http-version' => 'HTTP/1.x',
					'status.code' => 200,
					'status.reason-phrase' => 'OK',
					'header' => $this->Socket->parseHeader("Date: Mon, 16 Apr 2007 04:14:16 GMT\r\nServer: CakeHttp Server\r\n"),
					'body' => "<h1>Hello World</h1>\r\n<p>It's good to be html</p>"
				)
			),
			'no-header' => array(
				'response' => array(
					'status-line' => "HTTP/1.x 404 OK\r\n",
					'header' => null,
				)
				, 'expectations' => array(
					'status.code' => 404,
					'header' => array()
				)
			),
			'chunked' => array(
				'response' => array(
					'header' => "Transfer-Encoding: chunked\r\n",
					'body' => "19\r\nThis is a chunked message\r\n0\r\n"
				),
				'expectations' => array(
					'body' => "This is a chunked message",
					'header' => $this->Socket->parseHeader("Transfer-Encoding: chunked\r\n")
				)
			),
			'enitity-header' => array(
				'response' => array(
					'body' => "19\r\nThis is a chunked message\r\n0\r\nFoo: Bar\r\n"
				),
				'expectations' => array(
					'header' => $this->Socket->parseHeader("Transfer-Encoding: chunked\r\nFoo: Bar\r\n")
				)
			),
			'enitity-header-combine' => array(
				'response' => array(
					'header' => "Transfer-Encoding: chunked\r\nFoo: Foobar\r\n"
				),
				'expectations' => array(
					'header' => $this->Socket->parseHeader("Transfer-Encoding: chunked\r\nFoo: Foobar\r\nFoo: Bar\r\n")
				)
			)
		);

		$testResponse = array();
		$expectations = array();

		foreach ($tests as $name => $test) {

			$testResponse = am($testResponse, $test['response']);
			$testResponse['response'] = $testResponse['status-line'].$testResponse['header']."\r\n".$testResponse['body'];
			$r = $this->Socket->parseResponse($testResponse['response']);
			$expectations = am($expectations, $test['expectations']);

			foreach ($expectations as $property => $expectedVal) {
				$val = Set::extract($r, $property);
				$this->assertIdentical($val, $expectedVal, 'Test "'.$name.'": response.'.$property.' - %s');
			}

			foreach (array('status-line', 'header', 'body', 'response') as $field) {
				$this->assertIdentical($r['raw'][$field], $testResponse[$field], 'Test response.raw.'.$field.': %s');
			}
		}
	}

/**
 * Enter description here...
 *
 */
	function testDecodeBody() {
		$this->Socket->reset();

		$r = $this->Socket->decodeBody(true);
		$this->assertIdentical($r, false);

		$r = $this->Socket->decodeBody('Foobar', false);
		$this->assertIdentical($r, array('body' => 'Foobar', 'header' => false));

		$encodings = array(
			'chunked' => array(
				'encoded' => "19\r\nThis is a chunked message\r\n0\r\n",
				'decoded' => array('body' => "This is a chunked message", 'header' => false)
			),
			'foo-coded' => array(
				'encoded' => '!Foobar!',
				'decoded' => array('body' => '!Foobar!', 'header' => false),
				'error' => new PatternExpectation('/unknown encoding: foo-coded/i')
			)
		);

		foreach ($encodings as $encoding => $sample) {
			if (isset($sample['error'])) {
				$this->expectError($sample['error']);
			}

			$r = $this->Socket->decodeBody($sample['encoded'], $encoding);
			$this->assertIdentical($r, $sample['decoded']);

			if (isset($sample['error'])) {
				$this->Socket->quirksMode = true;
				$r = $this->Socket->decodeBody($sample['encoded'], $encoding);
				$this->assertIdentical($r, $sample['decoded']);
				$this->Socket->quirksMode = false;
			}
		}
	}

/**
 * Enter description here...
 *
 */
	function testDecodeChunkedBody() {
		$this->Socket->reset();

		$r = $this->Socket->decodeChunkedBody(true);
		$this->assertIdentical($r, false);

		$encoded = "19\r\nThis is a chunked message\r\n0\r\n";
		$decoded = "This is a chunked message";
		$r = $this->Socket->decodeChunkedBody($encoded);
		$this->assertIdentical($r['body'], $decoded);
		$this->assertIdentical($r['header'], false);

		$encoded = "19\r\nThis is a chunked message\r\nE\r\n\nThat is cool\n\r\n0\r\n";
		$decoded = "This is a chunked message\nThat is cool\n";
		$r = $this->Socket->decodeChunkedBody($encoded);
		$this->assertIdentical($r['body'], $decoded);
		$this->assertIdentical($r['header'], false);

		$encoded = "19\r\nThis is a chunked message\r\nE;foo-chunk=5\r\n\nThat is cool\n\r\n0\r\n";
		$r = $this->Socket->decodeChunkedBody($encoded);
		$this->assertIdentical($r['body'], $decoded);
		$this->assertIdentical($r['header'], false);

		$encoded = "19\r\nThis is a chunked message\r\nE\r\n\nThat is cool\n\r\n0\r\nfoo-header: bar\r\ncake: PHP\r\n\r\n";
		$r = $this->Socket->decodeChunkedBody($encoded);
		$this->assertIdentical($r['body'], $decoded);
		$this->assertIdentical($r['header'], array('Foo-Header' => 'bar', 'Cake' => 'PHP'));

		$encoded = "19\r\nThis is a chunked message\r\nE\r\n\nThat is cool\n\r\n";
		$this->expectError(new PatternExpectation('/activate quirks mode/i'));
		$r = $this->Socket->decodeChunkedBody($encoded);
		$this->assertIdentical($r, false);

		$this->Socket->quirksMode = true;
		$r = $this->Socket->decodeChunkedBody($encoded);
		$this->assertIdentical($r['body'], $decoded);
		$this->assertIdentical($r['header'], false);

		$encoded = "19\r\nThis is a chunked message\r\nE\r\n\nThat is cool\n\r\nfoo-header: bar\r\ncake: PHP\r\n\r\n";
		$r = $this->Socket->decodeChunkedBody($encoded);
		$this->assertIdentical($r['body'], $decoded);
		$this->assertIdentical($r['header'], array('Foo-Header' => 'bar', 'Cake' => 'PHP'));
	}

	function testBuildRequestLine() {
		$this->Socket->reset();

		$this->expectError(new PatternExpectation('/activate quirks mode/i'));
		$r = $this->Socket->buildRequestLine('Foo');
		$this->assertIdentical($r, false);

		$this->Socket->quirksMode = true;
		$r = $this->Socket->buildRequestLine('Foo');
		$this->assertIdentical($r, 'Foo');
		$this->Socket->quirksMode = false;

		$r = $this->Socket->buildRequestLine(true);
		$this->assertIdentical($r, false);

		$r = $this->Socket->buildRequestLine(array('foo' => 'bar', 'method' => 'foo'));
		$this->assertIdentical($r, false);

		$r = $this->Socket->buildRequestLine(array('method' => 'GET', 'uri' => 'http://www.cakephp.org/search?q=socket'));
		$this->assertIdentical($r, "GET /search?q=socket HTTP/1.1\r\n");

		$request = array(
			'method' => 'GET',
			'uri' => array(
				'path' => '/search',
				'query' => array('q' => 'socket')
			)
		);
		$r = $this->Socket->buildRequestLine($request);
		$this->assertIdentical($r, "GET /search?q=socket HTTP/1.1\r\n");

		unset($request['method']);
		$r = $this->Socket->buildRequestLine($request);
		$this->assertIdentical($r, "GET /search?q=socket HTTP/1.1\r\n");

		$r = $this->Socket->buildRequestLine($request, 'CAKE-HTTP/0.1');
		$this->assertIdentical($r, "GET /search?q=socket CAKE-HTTP/0.1\r\n");

		$request = array('method' => 'OPTIONS', 'uri' => '*');
		$r = $this->Socket->buildRequestLine($request);
		$this->assertIdentical($r, "OPTIONS * HTTP/1.1\r\n");

		$request['method'] = 'GET';
		$this->expectError(new PatternExpectation('/activate quirks mode/i'));
		$r = $this->Socket->buildRequestLine($request);
		$this->assertIdentical($r, false);

		$this->expectError(new PatternExpectation('/activate quirks mode/i'));
		$r = $this->Socket->buildRequestLine("GET * HTTP/1.1\r\n");
		$this->assertIdentical($r, false);

		$this->Socket->quirksMode = true;
		$r = $this->Socket->buildRequestLine($request);
		$this->assertIdentical($r,  "GET * HTTP/1.1\r\n");

		$r = $this->Socket->buildRequestLine("GET * HTTP/1.1\r\n");
		$this->assertIdentical($r, "GET * HTTP/1.1\r\n");
	}

/**
 * Asserts that HttpSocket::parseUri is working properly
 *
 */
	function testParseUri() {
		$this->Socket->reset();

		$uri = $this->Socket->parseUri(array('invalid' => 'uri-string'));
		$this->assertIdentical($uri, false);

		$uri = $this->Socket->parseUri(array('invalid' => 'uri-string'), array('host' => 'somehost'));
		$this->assertIdentical($uri, array('host' => 'somehost', 'invalid' => 'uri-string'));

		$uri = $this->Socket->parseUri(false);
		$this->assertIdentical($uri, false);

		$uri = $this->Socket->parseUri('/my-cool-path');
		$this->assertIdentical($uri, array('path' => '/my-cool-path'));

		$uri = $this->Socket->parseUri('http://bob:foo123@www.cakephp.org:40/search?q=dessert#results');
		$this->assertIdentical($uri, array(
			'scheme' => 'http',
			'host' => 'www.cakephp.org',
			'port' => 40,
			'user' => 'bob',
			'pass' => 'foo123',
			'path' => '/search',
			'query' => array('q' => 'dessert'),
			'fragment' => 'results'
		));

		$uri = $this->Socket->parseUri('http://www.cakephp.org/');
		$this->assertIdentical($uri, array(
			'scheme' => 'http',
			'host' => 'www.cakephp.org',
			'path' => '/',
		));

		$uri = $this->Socket->parseUri('http://www.cakephp.org', true);
		$this->assertIdentical($uri, array(
			'scheme' => 'http',
			'host' => 'www.cakephp.org',
			'port' => 80,
			'user' => null,
			'pass' => null,
			'path' => '/',
			'query' => array(),
			'fragment' => null
		));

		$uri = $this->Socket->parseUri('https://www.cakephp.org', true);
		$this->assertIdentical($uri, array(
			'scheme' => 'https',
			'host' => 'www.cakephp.org',
			'port' => 443,
			'user' => null,
			'pass' => null,
			'path' => '/',
			'query' => array(),
			'fragment' => null
		));

		$uri = $this->Socket->parseUri('www.cakephp.org:443/query?foo', true);
		$this->assertIdentical($uri, array(
			'scheme' => 'https',
			'host' => 'www.cakephp.org',
			'port' => 443,
			'user' => null,
			'pass' => null,
			'path' => '/query',
			'query' => array('foo' => null),
			'fragment' => null
		));

		$uri = $this->Socket->parseUri('http://www.cakephp.org', array('host' => 'piephp.org', 'user' => 'bob', 'fragment' => 'results'));
		$this->assertIdentical($uri, array(
			'host' => 'www.cakephp.org',
			'user' => 'bob',
			'fragment' => 'results',
			'scheme' => 'http'
		));

		$uri = $this->Socket->parseUri('https://www.cakephp.org', array('scheme' => 'http', 'port' => 23));
		$this->assertIdentical($uri, array(
			'scheme' => 'https',
			'port' => 23,
			'host' => 'www.cakephp.org'
		));

		$uri = $this->Socket->parseUri('www.cakephp.org:59', array('scheme' => array('http', 'https'), 'port' => 80));
		$this->assertIdentical($uri, array(
			'scheme' => 'http',
			'port' => 59,
			'host' => 'www.cakephp.org'
		));

		$uri = $this->Socket->parseUri(array('scheme' => 'http', 'host' => 'www.google.com', 'port' => 8080), array('scheme' => array('http', 'https'), 'host' => 'www.google.com', 'port' => array(80, 443)));
		$this->assertIdentical($uri, array(
			'scheme' => 'http',
			'host' => 'www.google.com',
			'port' => 8080,
		));
	}

/**
 * Tests that HttpSocket::buildUri can turn all kinds of uri arrays (and strings) into fully or partially qualified URI's
 *
 */
	function testBuildUri() {
		$this->Socket->reset();

		$r = $this->Socket->buildUri(true);
		$this->assertIdentical($r, false);

		$r = $this->Socket->buildUri('foo.com');
		$this->assertIdentical($r, 'http://foo.com/');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org'));
		$this->assertIdentical($r, 'http://www.cakephp.org/');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'scheme' => 'https'));
		$this->assertIdentical($r, 'https://www.cakephp.org/');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'port' => 23));
		$this->assertIdentical($r, 'http://www.cakephp.org:23/');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'scheme' => 'https', 'port' => 79));
		$this->assertIdentical($r, 'https://www.cakephp.org:79/');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'path' => 'foo'));
		$this->assertIdentical($r, 'http://www.cakephp.org/foo');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'path' => '/foo'));
		$this->assertIdentical($r, 'http://www.cakephp.org/foo');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'path' => '/search', 'query' => array('q' => 'HttpSocket')));
		$this->assertIdentical($r, 'http://www.cakephp.org/search?q=HttpSocket');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'fragment' => 'bar'));
		$this->assertIdentical($r, 'http://www.cakephp.org/#bar');

		$r = $this->Socket->buildUri(array(
			'scheme' => 'https',
			'host' => 'www.cakephp.org',
			'port' => 25,
			'user' => 'bob',
			'pass' => 'secret',
			'path' => '/cool',
			'query' => array('foo' => 'bar'),
			'fragment' => 'comment'
		));
		$this->assertIdentical($r, 'https://bob:secret@www.cakephp.org:25/cool?foo=bar#comment');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org', 'fragment' => 'bar'), '%fragment?%host');
		$this->assertIdentical($r, 'bar?www.cakephp.org');

		$r = $this->Socket->buildUri(array('host' => 'www.cakephp.org'), '%fragment???%host');
		$this->assertIdentical($r, '???www.cakephp.org');

		$r = $this->Socket->buildUri(array('path' => '*'), '/%path?%query');
		$this->assertIdentical($r, '*');

		$r = $this->Socket->buildUri(array('scheme' => 'foo', 'host' => 'www.cakephp.org'));
		$this->assertIdentical($r, 'foo://www.cakephp.org:80/');
	}

/**
 * Asserts that HttpSocket::parseQuery is working properly
 *
 */
	function testParseQuery() {
		$this->Socket->reset();

		$query = $this->Socket->parseQuery(array('framework' => 'cakephp'));
		$this->assertIdentical($query, array('framework' => 'cakephp'));

		$query = $this->Socket->parseQuery('');
		$this->assertIdentical($query, array());

		$query = $this->Socket->parseQuery('framework=cakephp');
		$this->assertIdentical($query, array('framework' => 'cakephp'));

		$query = $this->Socket->parseQuery('?framework=cakephp');
		$this->assertIdentical($query, array('framework' => 'cakephp'));

		$query = $this->Socket->parseQuery('a&b&c');
		$this->assertIdentical($query, array('a' => null, 'b' => null, 'c' => null));

		$query = $this->Socket->parseQuery('value=12345');
		$this->assertIdentical($query, array('value' => 12345));

		$query = $this->Socket->parseQuery('a[0]=foo&a[1]=bar&a[2]=cake');
		$this->assertIdentical($query, array('a' => array(0 => 'foo', 1 => 'bar', 2 => 'cake')));

		$query = $this->Socket->parseQuery('a[]=foo&a[]=bar&a[]=cake');
		$this->assertIdentical($query, array('a' => array(0 => 'foo', 1 => 'bar', 2 => 'cake')));

		$query = $this->Socket->parseQuery('a]][[=foo&[]=bar&]]][]=cake');
		$this->assertIdentical($query, array('a]][[' => 'foo', 0 => 'bar', ']]]' => array('cake')));

		$query = $this->Socket->parseQuery('a[][]=foo&a[][]=bar&a[][]=cake');
		$expectedQuery = array(
			'a' => array(
				0 => array(
					0 => 'foo'
				),
				1 => array(
					0 => 'bar'
				),
				array(
					0 => 'cake'
				)
			)
		);
		$this->assertIdentical($query, $expectedQuery);

		$query = $this->Socket->parseQuery('a[][]=foo&a[bar]=php&a[][]=bar&a[][]=cake');
		$expectedQuery = array(
			'a' => array(
				0 => array(
					0 => 'foo'
				),
				'bar' => 'php',
				1 => array(
					0 => 'bar'
				),
				array(
					0 => 'cake'
				)
			)
		);
		$this->assertIdentical($query, $expectedQuery);

		$query = $this->Socket->parseQuery('user[]=jim&user[3]=tom&user[]=bob');
		$expectedQuery = array(
			'user' => array(
				0 => 'jim',
				3 => 'tom',
				4 => 'bob'
			)
		);
		$this->assertIdentical($query, $expectedQuery);

		$queryStr = 'user[0]=foo&user[0][items][]=foo&user[0][items][]=bar&user[][name]=jim&user[1][items][personal][]=book&user[1][items][personal][]=pen&user[1][items][]=ball&user[count]=2&empty';
		$query = $this->Socket->parseQuery($queryStr);
		$expectedQuery = array(
			'user' => array(
				0 => array(
					'items' => array(
						'foo',
						'bar'
					)
				),
				1 => array(
					'name' => 'jim',
					'items' => array(
						'personal' => array(
							'book'
							, 'pen'
						),
						'ball'
					)
				),
				'count' => 2
			),
			'empty' => null
		);
		$this->assertIdentical($query, $expectedQuery);
	}

/**
 * Tests that HttpSocket::buildHeader can turn a given $header array into a proper header string according to
 * HTTP 1.1 specs.
 *
 */
	function testBuildHeader() {
		$this->Socket->reset();

		$r = $this->Socket->buildHeader(true);
		$this->assertIdentical($r, false);

		$r = $this->Socket->buildHeader('My raw header');
		$this->assertIdentical($r, 'My raw header');

		$r = $this->Socket->buildHeader(array('Host' => 'www.cakephp.org'));
		$this->assertIdentical($r, "Host: www.cakephp.org\r\n");

		$r = $this->Socket->buildHeader(array('Host' => 'www.cakephp.org', 'Connection' => 'Close'));
		$this->assertIdentical($r, "Host: www.cakephp.org\r\nConnection: Close\r\n");

		$r = $this->Socket->buildHeader(array('People' => array('Bob', 'Jim', 'John')));
		$this->assertIdentical($r, "People: Bob,Jim,John\r\n");

		$r = $this->Socket->buildHeader(array('Multi-Line-Field' => "This is my\r\nMulti Line field"));
		$this->assertIdentical($r, "Multi-Line-Field: This is my\r\n Multi Line field\r\n");

		$r = $this->Socket->buildHeader(array('Multi-Line-Field' => "This is my\r\n Multi Line field"));
		$this->assertIdentical($r, "Multi-Line-Field: This is my\r\n Multi Line field\r\n");

		$r = $this->Socket->buildHeader(array('Multi-Line-Field' => "This is my\r\n\tMulti Line field"));
		$this->assertIdentical($r, "Multi-Line-Field: This is my\r\n\tMulti Line field\r\n");

		$r = $this->Socket->buildHeader(array('Test@Field' => "My value"));
		$this->assertIdentical($r, "Test\"@\"Field: My value\r\n");

	}

/**
 * Test that HttpSocket::parseHeader can take apart a given (and valid) $header string and turn it into an array.
 *
 */
	function testParseHeader() {
		$this->Socket->reset();

		$r = $this->Socket->parseHeader(array('foo' => 'Bar', 'fOO-bAr' => 'quux'));
		$this->assertIdentical($r, array('Foo' => 'Bar', 'Foo-Bar' => 'quux'));

		$r = $this->Socket->parseHeader(true);
		$this->assertIdentical($r, false);

		$header = "Host: cakephp.org\t\r\n";
		$r = $this->Socket->parseHeader($header);
		$expected = array(
			'Host' => 'cakephp.org'
		);
		$this->assertIdentical($r, $expected);

		$header = "Date:Sat, 07 Apr 2007 10:10:25 GMT\r\nX-Powered-By: PHP/5.1.2\r\n";
		$r = $this->Socket->parseHeader($header);
		$expected = array(
			'Date' => 'Sat, 07 Apr 2007 10:10:25 GMT'
			, 'X-Powered-By' =>  'PHP/5.1.2'
		);
		$this->assertIdentical($r, $expected);

		$header = "people: Jim,John\r\nfoo-LAND: Bar\r\ncAKe-PHP: rocks\r\n";
		$r = $this->Socket->parseHeader($header);
		$expected = array(
			'People' => 'Jim,John'
			, 'Foo-Land' => 'Bar'
			, 'Cake-Php' =>  'rocks'
		);
		$this->assertIdentical($r, $expected);

		$header = "People: Jim,John,Tim\r\nPeople: Lisa,Tina,Chelsea\r\n";
		$r = $this->Socket->parseHeader($header);
		$expected = array(
			'People' =>  array('Jim,John,Tim', 'Lisa,Tina,Chelsea')
		);
		$this->assertIdentical($r, $expected);

		$header = "Multi-Line: I am a \r\nmulti line\t\r\nfield value.\r\nSingle-Line: I am not\r\n";
		$r = $this->Socket->parseHeader($header);
		$expected = array(
			'Multi-Line' => "I am a\r\nmulti line\r\nfield value."
			, 'Single-Line' => 'I am not'
		);
		$this->assertIdentical($r, $expected);

		$header = "Esc\"@\"ped: value\r\n";
		$r = $this->Socket->parseHeader($header);
		$expected = array(
			'Esc@ped' => 'value'
		);
		$this->assertIdentical($r, $expected);
	}
/**
 * undocumented function
 *
 * @return void
 * @access public
 */
	function testParseCookies() {
		$header = array(
			'Set-Cookie' => array(
				'foo=bar',
				'people=jim,jack,johnny";";Path=/accounts'
			),
			'Transfer-Encoding' => 'chunked',
			'Date' => 'Sun, 18 Nov 2007 18:57:42 GMT',
		);
		$cookies = $this->Socket->parseCookies($header);
		$expected = array(
			'foo' => array(
				'value' => 'bar'
			),
			'people' => array(
				'value' => 'jim,jack,johnny";"',
				'path' => '/accounts'
			)
		);
		$this->assertEqual($cookies, $expected);

		$header['Set-Cookie'][] = 'cakephp=great; Secure';
		$expected['cakephp'] = array('value' => 'great', 'secure' => true);
		$cookies = $this->Socket->parseCookies($header);
		$this->assertEqual($cookies, $expected);

		$header['Set-Cookie'] = 'foo=bar';
		unset($expected['people'], $expected['cakephp']);
		$cookies = $this->Socket->parseCookies($header);
		$this->assertEqual($cookies, $expected);
	}
/**
 * undocumented function
 *
 * @return void
 * @access public
 * @todo Test more scenarios
 */
	function testBuildCookies() {
		$cookies = array(
			'foo' => array(
				'value' => 'bar'
			),
			'people' => array(
				'value' => 'jim,jack,johnny;',
				'path' => '/accounts'
			)
		);
		$expect = "Cookie: foo=bar\r\nCookie: people=jim,jack,johnny\";\"\r\n";
		$result = $this->Socket->buildCookies($cookies);
		$this->assertEqual($result, $expect);
	}
/**
 * Tests that HttpSocket::__tokenEscapeChars() returns the right characters.
 *
 */
	function testTokenEscapeChars() {
		$this->Socket->reset();

		$expected = array('\x22','\x28','\x29','\x3c','\x3e','\x40','\x2c','\x3b','\x3a','\x5c','\x2f','\x5b','\x5d','\x3f','\x3d','\x7b',
						  '\x7d','\x20','\x00','\x01','\x02','\x03','\x04','\x05','\x06','\x07','\x08','\x09','\x0a','\x0b','\x0c','\x0d',
						  '\x0e','\x0f','\x10','\x11','\x12','\x13','\x14','\x15','\x16','\x17','\x18','\x19','\x1a','\x1b','\x1c','\x1d',
						  '\x1e','\x1f','\x7f');
		$r = $this->Socket->__tokenEscapeChars();
		$this->assertEqual($r, $expected);

		foreach ($expected as $key => $char) {
			$expected[$key] = chr(hexdec(substr($char, 2)));
		}

		$r = $this->Socket->__tokenEscapeChars(false);
		$this->assertEqual($r, $expected);
	}

/**
 * Test that HttpSocket::escapeToken is escaping all characters as descriped in RFC 2616 (HTTP 1.1 specs)
 *
 */
	function testEscapeToken() {
		$this->Socket->reset();

		$this->assertIdentical($this->Socket->escapeToken('Foo'), 'Foo');

		$escape = $this->Socket->__tokenEscapeChars(false);
		foreach ($escape as $char) {
			$token = 'My-special-'.$char.'-Token';
			$escapedToken = $this->Socket->escapeToken($token);
			$expectedToken = 'My-special-"'.$char.'"-Token';

			$this->assertIdentical($escapedToken, $expectedToken, 'Test token escaping for ASCII '.ord($char));
		}

		$token = 'Extreme-:Token-	-"@-test';
		$escapedToken = $this->Socket->escapeToken($token);
		$expectedToken = 'Extreme-":"Token-"	"-""""@"-test';
		$this->assertIdentical($expectedToken, $escapedToken);
	}

/**
 * Test that escaped token strings are properly unescaped by HttpSocket::unescapeToken
 *
 */
	function testUnescapeToken() {
		$this->Socket->reset();

		$this->assertIdentical($this->Socket->unescapeToken('Foo'), 'Foo');

		$escape = $this->Socket->__tokenEscapeChars(false);
		foreach ($escape as $char) {
			$token = 'My-special-"'.$char.'"-Token';
			$unescapedToken = $this->Socket->unescapeToken($token);
			$expectedToken = 'My-special-'.$char.'-Token';

			$this->assertIdentical($unescapedToken, $expectedToken, 'Test token unescaping for ASCII '.ord($char));
		}

		$token = 'Extreme-":"Token-"	"-""""@"-test';
		$escapedToken = $this->Socket->unescapeToken($token);
		$expectedToken = 'Extreme-:Token-	-"@-test';
		$this->assertIdentical($expectedToken, $escapedToken);
	}

/**
 * This tests asserts HttpSocket::reset() resets a HttpSocket instance to it's initial state (before Object::__construct
 * got executed)
 *
 */
	function testReset() {
		$this->Socket->reset();

		$initialState = get_class_vars('HttpSocket');
		foreach ($initialState as $property => $value) {
			$this->Socket->{$property} = 'Overwritten';
		}

		$return = $this->Socket->reset();

		foreach ($initialState as $property => $value) {
			$this->assertIdentical($this->Socket->{$property}, $value);
		}

		$this->assertIdentical($return, true);
	}

/**
 * This tests asserts HttpSocket::reset(false) resets certain HttpSocket properties to their initial state (before
 * Object::__construct got executed).
 *
 */
	function testPartialReset() {
		$this->Socket->reset();

		$partialResetProperties = array('request', 'response');
		$initialState = get_class_vars('HttpSocket');

		foreach ($initialState as $property => $value) {
			$this->Socket->{$property} = 'Overwritten';
		}

		$return = $this->Socket->reset(false);

		foreach ($initialState as $property => $originalValue) {
			if (in_array($property, $partialResetProperties)) {
				$this->assertIdentical($this->Socket->{$property}, $originalValue);
			} else {
				$this->assertIdentical($this->Socket->{$property}, 'Overwritten');
			}
		}
		$this->assertIdentical($return, true);
	}

}
?>