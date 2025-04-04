<?php

namespace SMW\Tests\SPARQLStore;

use SMW\DIProperty;
use SMW\SPARQLStore\ReplicationDataTruncator;

/**
 * @covers \SMW\SPARQLStore\ReplicationDataTruncator
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 2.5
 *
 * @author mwjames
 */
class ReplicationDataTruncatorTest extends \PHPUnit\Framework\TestCase {

	private $semanticData;

	public function setUp(): void {
		$this->semanticData = $this->getMockBuilder( '\SMW\semanticData' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SMW\SPARQLStore\ReplicationDataTruncator',
			new ReplicationDataTruncator()
		);
	}

	public function testOnEmptyList() {
		$instance = new ReplicationDataTruncator();
		$semanticData = $instance->doTruncate( $this->semanticData );

		$this->assertSame(
			$this->semanticData,
			$semanticData
		);
	}

	public function testOnExemptedList() {
		$property = new DIProperty( 'Foo_bar' );

		$this->semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $property );

		$instance = new ReplicationDataTruncator();
		$instance->setPropertyExemptionList( [ 'Foo bar' ] );

		$instance->doTruncate( $this->semanticData );
	}

	public function testOnExemptedListWithPredefinedProperty() {
		$property = new DIProperty( '_ASK' );

		$this->semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $property );

		$instance = new ReplicationDataTruncator();
		$instance->setPropertyExemptionList( [ 'Has query' ] );

		$instance->doTruncate( $this->semanticData );
	}

}
