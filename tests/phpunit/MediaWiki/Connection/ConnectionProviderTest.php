<?php

namespace SMW\Tests\MediaWiki\Connection;

use SMW\MediaWiki\Connection\ConnectionProvider;
use SMW\Tests\PHPUnitCompat;
use SMW\Tests\TestEnvironment;

/**
 * @covers \SMW\MediaWiki\Connection\ConnectionProvider
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 2.1
 *
 * @author mwjames
 */
class ConnectionProviderTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ConnectionProvider::class,
			new ConnectionProvider()
		);
	}

	public function testGetConnection() {
		$instance = new ConnectionProvider();
		$instance->setLogger(
			TestEnvironment::newSpyLogger()
		);

		$connection = $instance->getConnection();

		$this->assertInstanceOf(
			'\SMW\MediaWiki\Connection\Database',
			$connection
		);

		$this->assertSame(
			$connection,
			$instance->getConnection()
		);

		$instance->releaseConnection();

		$this->assertNotSame(
			$connection,
			$instance->getConnection()
		);
	}

	public function testGetConnectionOnFixedConfWithSameIndex() {
		$instance = new ConnectionProvider(
			'foo'
		);

		$instance->setLogger(
			TestEnvironment::newSpyLogger()
		);

		$conf = [
			'foo' => [
				'read' => 'Bar',
				'write' => 'Bar'
			]
		];

		$instance->setLocalConnectionConf( $conf );

		$connection = $instance->getConnection();

		$this->assertInstanceOf(
			'\SMW\MediaWiki\Connection\Database',
			$connection
		);

		$this->assertSame(
			$connection,
			$instance->getConnection()
		);

		$instance->releaseConnection();

		$this->assertNotSame(
			$connection,
			$instance->getConnection()
		);
	}

	public function testGetConnectionOnCallback() {
		$db = $this->getMockBuilder( '\SMW\MediaWiki\Connection\Database' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ConnectionProvider(
			'foo'
		);

		$conf = [
			'foo' => [
				'callback'  => static function () use( $db ) {
					return $db;
				}
			]
		];

		$instance->setLocalConnectionConf( $conf );

		$connection = $instance->getConnection();

		$this->assertSame(
			$db,
			$instance->getConnection()
		);

		$instance->releaseConnection();
	}

	public function testGetConnectionOnIncompleteConfThrowsException() {
		$instance = new ConnectionProvider(
			'foo'
		);

		$conf = [
			'foo' => [
				'read' => 'Foo'
			]
		];

		$instance->setLocalConnectionConf( $conf );

		$this->expectException( 'RuntimeException' );
		$instance->getConnection();
	}

}
