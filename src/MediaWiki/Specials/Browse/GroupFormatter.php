<?php

namespace SMW\MediaWiki\Specials\Browse;

use Html;
use SMW\DIWikiPage;
use SMW\Localizer\Message;
use SMW\Property\SpecificationLookup;
use SMW\Schema\SchemaFinder;
use SMWDataItem as DataItem;

/**
 * @private
 *
 * @license GPL-2.0-or-later
 * @since   3.0
 *
 * @author mwjames
 */
class GroupFormatter {

	/**
	 * Identifies a group label
	 */
	const MESSAGE_GROUP_LABEL = 'smw-property-group-label-';

	/**
	 * Identifies a group label
	 */
	const MESSAGE_GROUP_DESCRIPTION = 'smw-property-group-description-';

	/**
	 * @var SpecificationLookup
	 */
	private $propertySpecificationLookup;

	/**
	 * @var SchemaFinder
	 */
	private $schemaFinder;

	/**
	 * @var bool
	 */
	private $showGroup = true;

	/**
	 * @var string
	 */
	private $lastGroup = '';

	/**
	 * @var array
	 */
	private $groupLinks = [];

	/**
	 * @since 3.0
	 *
	 * @param SpecificationLookup $propertySpecificationLookup
	 */
	public function __construct( SpecificationLookup $propertySpecificationLookup, SchemaFinder $schemaFinder ) {
		$this->propertySpecificationLookup = $propertySpecificationLookup;
		$this->schemaFinder = $schemaFinder;
	}

	/**
	 * @since 3.0
	 *
	 * @param bool $showGroup
	 */
	public function showGroup( $showGroup ) {
		$this->showGroup = $showGroup;
	}

	/**
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function isLastGroup( $group ) {
		return $this->lastGroup === $group;
	}

	/**
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function hasGroups() {
		return $this->groupLinks !== [];
	}

	/**
	 * @since 3.0
	 *
	 * @param array &$properties
	 */
	public function findGroupMembership( array &$properties ) {
		$list = $this->prepareListFromSchema(
			$this->schemaFinder->getSchemaListByType( 'PROPERTY_GROUP_SCHEMA' )
		);

		$groupedProperties = [];
		$this->groupLinks = [];

		foreach ( $properties as $key => $property ) {

			$group = $this->findGroup( $property, $list );

			if ( !isset( $groupedProperties[$group] ) ) {
				$groupedProperties[$group] = [];
			}

			$groupedProperties[$group][] = $property;
		}

		ksort( $groupedProperties, SORT_NATURAL | SORT_FLAG_CASE );
		$properties = $groupedProperties;

		$keys = array_keys( $groupedProperties );
		$this->lastGroup = end( $keys );
	}

	/**
	 * @since 3.0
	 *
	 * @param string $group
	 *
	 * @return string
	 */
	public function getGroupLink( $group ) {
		if ( !isset( $this->groupLinks[$group] ) || $this->groupLinks[$group] === '' ) {
			return $group;
		}

		return Html::rawElement(
			'span',
			[
				'class' => 'group-link'
			],
			$this->groupLinks[$group]
		);
	}

	/**
	 * @since 3.0
	 *
	 * @param string $id
	 * @param DIWikiPage $dataItem
	 *
	 * @return string
	 */
	public function getMessageClassLink( $id, DIWikiPage $dataItem ) {
		$gr = str_replace( '_', ' ', $dataItem->getDBKey() );
		$key = mb_strtolower( str_replace( ' ', '-', $gr ) );

		return Html::rawElement(
			'a',
			[
				'href' => DIWikiPage::newFromText( $id . $key, NS_MEDIAWIKI )->getTitle()->getFullURL(),
				'class' => !Message::exists( $id . $key ) ? 'new' : ''
			],
			$id . $key
		);
	}

	private function findGroup( $property, $list ) {
		if ( $this->showGroup === false ) {
			return '';
		}

		$dataItem = null;
		$group = '';
		$msg_key = '';

		// Special handling for a `Category` property instance that itself cannot
		// be annotated with a `Is property group` therefor use the fixed
		// `smw-category-group` message to point to a group
		if ( $property->getKey() === '_INST' && Message::exists( 'smw-category-group' ) ) {
			$group = Message::get( 'smw-category-group' );
		} elseif ( ( $dataItem = $this->propertySpecificationLookup->getPropertyGroup( $property ) ) instanceof DataItem ) {
			$group = str_replace( '_', ' ', $dataItem->getDBKey() );
		} elseif ( $list !== [] ) {
			$group = $this->findGroupFromList( $list, $property, $dataItem, $msg_key );
		}

		if ( $group === '' || $group === null ) {
			return '';
		}

		$desc = '';
		$link = '';

		// Convention key to allow a category to transtable using the
		// `smw-group-...` as key and transforms a group `Foo bar` to
		// `smw-group-foo-bar`
		$key = mb_strtolower( str_replace( ' ', '-', $group ) );

		if ( $msg_key === '' ) {
			$msg_key = self::MESSAGE_GROUP_LABEL . $key;
		}

		if ( Message::exists( $msg_key ) ) {
			$group = Message::get( $msg_key, Message::TEXT, Message::USER_LANGUAGE );
		}

		if ( Message::exists( self::MESSAGE_GROUP_DESCRIPTION . $key ) ) {
			$desc = Message::get(
				self::MESSAGE_GROUP_DESCRIPTION . $key,
				Message::TEXT,
				Message::USER_LANGUAGE
			);
		}

		if ( $dataItem instanceof DataItem ) {
			$link = Html::rawElement(
				'a',
				[
					'href' => $dataItem->getTitle()->getFullURL()
				],
				$group
			);
		}

		if ( $desc !== '' ) {
			$link = Html::rawElement(
				'span',
				[
					'class' => 'smw-highlighter smwttinline',
					'data-state' => 'inline'
				],
				$link . Html::rawElement(
					'span',
					[
						'class' => 'smwttcontent'
					],
					$desc
				)
			);
		}

		if ( !isset( $this->groupLinks[$group] ) || $this->groupLinks[$group] === '' ) {
			$this->groupLinks[$group] = $link;
		}

		return $group;
	}

	private function prepareListFromSchema( $schemaList ) {
		$list = [];

		foreach ( $schemaList->getList() as $schemaDefinition ) {
			foreach ( $schemaDefinition->get( 'groups' ) as $data ) {

				$message_key = '';

				if ( isset( $data['properties'] ) ) {
					$property_keys = $data['properties'];
				} elseif ( isset( $data['property_keys'] ) ) {
					$property_keys = $data['property_keys'];
				} else {
					continue;
				}

				if ( isset( $data['message_key'] ) ) {
					$message_key = $data['message_key'];
				}

				if ( isset( $data['canonical_name'] ) ) {
					$group = $data['canonical_name'];
				} elseif ( isset( $data['group_name'] ) ) {
					$group = $data['group_name'];
				} elseif ( $message_key === '' ) {
					continue;
				}

				if ( $message_key !== '' && !Message::exists( $message_key ) ) {
					$message_key = $group;
				}

				$list[$group] = [
					'properties' => array_flip( $property_keys ),
					'msg_key' => $message_key,
					'item' => DIWikiPage::newFromText( $schemaDefinition->getName(), SMW_NS_SCHEMA )
				];
			}
		}

		return $list;
	}

	private function findGroupFromList( $list, $property, &$dataItem, &$label ) {
		foreach ( $list as $group => $data ) {

			$properties = $data['properties'];

			if ( !isset( $properties[$property->getKey()] ) && !isset( $properties[$property->getLabel()] ) ) {
				continue;
			}

			$label = $data['msg_key'];
			$dataItem = $data['item'];

			return $group;
		}
	}

}
