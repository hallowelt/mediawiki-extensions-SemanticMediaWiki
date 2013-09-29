<?php

namespace SMW\Test;

use SMW\SharedDependencyContainer;
use SMW\BaseTemplateToolbox;

/**
 * Tests for the BaseTemplateToolbox class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\BaseTemplateToolbox
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class BaseTemplateToolboxTest extends SemanticMediaWikiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\BaseTemplateToolbox';
	}

	/**
	 * Helper method that returns a BaseTemplateToolbox object
	 *
	 * @since 1.9
	 *
	 * @return BaseTemplateToolbox
	 */
	private function newInstance( $skinTemplate = null, $settings = array(), &$toolbox = '' ) {

		if ( $skinTemplate === null ) {
			$skinTemplate = $this->newMockBuilder()->newObject( 'SkinTemplate' );
		}

		$instance = new BaseTemplateToolbox( $skinTemplate, $toolbox );

		$container = new SharedDependencyContainer();
		$container->registerObject( 'Settings', $this->newSettings( $settings ) );
		$instance->setDependencyBuilder( $this->newDependencyBuilder( $container ) );

		return $instance;
	}

	/**
	 * @test BaseTemplateToolbox::__construct
	 *
	 * @since 1.9
	 */
	public function testConstructor() {
		$this->assertInstanceOf( $this->getClass(), $this->newInstance() );
	}

	/**
	 * @test BaseTemplateToolbox::process
	 * @dataProvider skinTemplateDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $setup
	 * @param $expected
	 */
	public function testProcess( $setup, $expected ) {

		$toolbox  = '';

		$instance = $this->newInstance( $setup['skinTemplate'], $setup['settings'], $toolbox );

		$this->assertTrue(
			$instance->process(),
			'Asserts that process() always returns true'
		);

		if ( $expected['count'] > 0 ) {
			$this->assertCount(
				$expected['count'],
				$toolbox['smw-browse'],
				'Asserts that process() produced a return result'
			);
		} else {
			$this->assertEmpty(
				$toolbox,
				'Asserts that process() returned empty'
			);
		}

	}

	/**
	 * @return array
	 */
	public function skinTemplateDataProvider() {

		$provider = array();

		// #0 Standard title
		$settings = array(
			'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
			'smwgToolboxBrowseLink'           => true
		);

		$mockSkin = $this->newMockBuilder()->newObject( 'Skin', array(
			'getTitle'   => $this->newTitle(),
			'getContext' => $this->newContext()
		) );

		$mockSkinTemplate = $this->newMockBuilder()->newObject( 'SkinTemplate', array(
			'getSkin'  => $mockSkin,
		) );

		$mockSkinTemplate->data['isarticle'] = true;

		$provider[] = array(
			array( 'skinTemplate' => $mockSkinTemplate, 'settings' => $settings ),
			array( 'count'        => 4 ),
		);

		// #1 isarticle = false
		$mockSkinTemplate = $this->newMockBuilder()->newObject( 'SkinTemplate', array(
			'getSkin'  => $mockSkin,
		) );

		$mockSkinTemplate->data['isarticle'] = false;

		$provider[] = array(
			array( 'skinTemplate' => $mockSkinTemplate, 'settings' => $settings ),
			array( 'count'        => 0 ),
		);

		// #2 smwgToolboxBrowseLink = false
		$mockSkinTemplate = $this->newMockBuilder()->newObject( 'SkinTemplate', array(
			'getSkin'  => $mockSkin,
		) );

		$mockSkinTemplate->data['isarticle'] = true;

		$settings = array(
			'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
			'smwgToolboxBrowseLink'           => false
		);

		$provider[] = array(
			array( 'skinTemplate' => $mockSkinTemplate, 'settings' => $settings ),
			array( 'count'        => 0 ),
		);

		// #3 smwgNamespacesWithSemanticLinks = false
		$settings = array(
			'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => false ),
			'smwgToolboxBrowseLink'           => true
		);

		$provider[] = array(
			array( 'skinTemplate' => $mockSkinTemplate, 'settings' => $settings ),
			array( 'count'        => 0 ),
		);

		// #4 Special page
		$settings = array(
			'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
			'smwgToolboxBrowseLink'           => true
		);

		$mockTitle = $this->newMockBuilder()->newObject( 'Title', array(
			'isSpecialPage' => true
		) );

		$mockSkin = $this->newMockBuilder()->newObject( 'Skin', array(
			'getTitle'   => $mockTitle,
			'getContext' => $this->newContext()
		) );

		$mockSkinTemplate = $this->newMockBuilder()->newObject( 'SkinTemplate', array(
			'getSkin'  => $mockSkin,
		) );

		$mockSkinTemplate->data['isarticle'] = true;

		$provider[] = array(
			array( 'skinTemplate' => $mockSkinTemplate, 'settings' => $settings ),
			array( 'count'        => 0 ),
		);

		return $provider;
	}

}
