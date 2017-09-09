<?php

namespace SMW\DataModel\DataItems;

use SMWDataItem as DataItem;
use SMW\Exception\DataItemException;

/**
 * @license GNU GPL v2
 * @since 3.0
 *
 * @author mwjames
 */
class DINull extends DataItem {

	/**
	 * @since 3.0
	 */
	public function __construct() {
		$this->setOption( self::IS_NULL, true );
	}

	/**
	 * @see DataItem::getDIType
	 */
	public function getDIType() {
		return DataItem::TYPE_NOTYPE;
	}

	/**
	 * @see DataItem::getDIType
	 */
	public function getSortKey() {
		return '';
	}

	/**
	 * @see DataItem::getSerialization
	 */
	public function getSerialization() {
		return '';
	}

	/**
	 * @see DataItem::equals
	 */
	public function equals( DataItem $di ) {
		return $di instanceof Null;
	}

}
