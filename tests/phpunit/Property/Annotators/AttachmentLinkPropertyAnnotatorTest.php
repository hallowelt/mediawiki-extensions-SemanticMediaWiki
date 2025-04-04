<?php

namespace SMW\Tests\Property\Annotators;

use SMW\DataItemFactory;
use SMW\Localizer\Localizer;
use SMW\Property\Annotators\AttachmentLinkPropertyAnnotator;
use SMW\Property\Annotators\NullPropertyAnnotator;
use SMW\SemanticData;
use SMW\Tests\TestEnvironment;

/**
 * @covers \SMW\Property\Annotators\AttachmentLinkPropertyAnnotator
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.1
 *
 * @author mwjames
 */
class AttachmentLinkPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $semanticDataValidator;
	private $dataItemFactory;
	private $fileNS;

	protected function setUp(): void {
		parent::setUp();

		$this->semanticDataValidator = TestEnvironment::newValidatorFactory()->newSemanticDataValidator();
		$this->dataItemFactory = new DataItemFactory();
		$this->fileNS = Localizer::getInstance()->getNsText( NS_FILE );
	}

	public function testCanConstruct() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new AttachmentLinkPropertyAnnotator(
			new NullPropertyAnnotator( $semanticData ),
			[]
		);

		$this->assertInstanceOf(
			AttachmentLinkPropertyAnnotator::class,
			$instance
		);
	}

	public function testAddAnnotation() {
		$semanticData = new SemanticData(
			$this->dataItemFactory->newDIWikiPage( 'Foo' )
		);

		$attachments = [
			'Foo.png' => 1
		];

		$expected = [
			'propertyCount'  => 1,
			'propertyKeys'   => [ '_ATTCH_LINK' ],
			'propertyValues' => [ $this->fileNS . ':Foo.png' ],
		];

		$instance = new AttachmentLinkPropertyAnnotator(
			new NullPropertyAnnotator( $semanticData ),
			$attachments
		);

		$instance->setPredefinedPropertyList( [ '_ATTCH_LINK' ] );

		$instance->addAnnotation();

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$expected,
			$instance->getSemanticData()
		);
	}

	public function testAddAnnotation_EmptyAttachments() {
		$semanticData = new SemanticData(
			$this->dataItemFactory->newDIWikiPage( 'Foo' )
		);

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$attachments = [];

		$instance = new AttachmentLinkPropertyAnnotator(
			new NullPropertyAnnotator( $semanticData ),
			$attachments
		);

		$instance->addAnnotation();

		$this->assertEquals(
			$semanticData,
			$instance->getSemanticData()
		);
	}

}
