<?php

namespace SMW\Tests\Exception;

use SMW\Exception\ClassNotFoundException;

/**
 * @covers \SMW\Exception\ClassNotFoundException
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class ClassNotFoundExceptionTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$instance = new ClassNotFoundException( 'Foo' );

		$this->assertInstanceof(
			ClassNotFoundException::class,
			$instance
		);

		$this->assertInstanceof(
			'\RuntimeException',
			$instance
		);
	}

}
