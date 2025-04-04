<?php

namespace SMW;

use MediaWiki\Json\JsonUnserializer;
use SMWDataItem;

/**
 * This class implements Concept data items.
 *
 * @note These special data items for storing concept declaration data in SMW
 * should vanish at some point since Container values could encode this data
 * just as well.
 *
 * @since 1.6
 *
 * @ingroup SMWDataItems
 *
 * @author Markus Krötzsch
 * @author mwjames
 */
class DIConcept extends \SMWDataItem {

	/**
	 * Query string for this concept. Possibly long.
	 * @var string
	 */
	protected $m_concept;
	/**
	 * Documentation for this concept. Possibly long.
	 * @var string
	 */
	protected $m_docu;
	/**
	 * Flags of query features.
	 * @var int
	 */
	protected $m_features;
	/**
	 * Size of the query.
	 * @var int
	 */
	protected $m_size;
	/**
	 * Depth of the query.
	 * @var int
	 */
	protected $m_depth;

	/**
	 * Status
	 * @var int
	 */
	protected $cacheStatus;

	/**
	 * Date
	 * @var int
	 */
	protected $cacheDate;

	/**
	 * Count
	 * @var int
	 */
	protected $cacheCount;

	/**
	 * @param string $concept the concept query string
	 * @param string $docu user documentation
	 * @param int $queryFeatures flags about query features
	 * @param int $size concept query size
	 * @param int $depth concept query depth
	 */
	public function __construct( $concept, $docu, $queryFeatures, $size, $depth ) {
		$this->m_concept  = $concept;
		$this->m_docu     = $docu;
		$this->m_features = $queryFeatures;
		$this->m_size     = $size;
		$this->m_depth    = $depth;
	}

	public function getDIType() {
		return SMWDataItem::TYPE_CONCEPT;
	}

	public function getConceptQuery() {
		return $this->m_concept;
	}

	public function getDocumentation() {
		return $this->m_docu;
	}

	public function getQueryFeatures() {
		return $this->m_features;
	}

	public function getSize() {
		return $this->m_size;
	}

	public function getDepth() {
		return $this->m_depth;
	}

	public function getSortKey() {
		return $this->m_docu;
	}

	public function getSerialization() {
		return serialize( $this );
	}

	/**
	 * Sets cache status
	 *
	 * @since 1.9
	 *
	 * @param string
	 */
	public function setCacheStatus( $status ) {
		$this->cacheStatus = $status;
	}

	/**
	 * Sets cache date
	 *
	 * @since 1.9
	 *
	 * @param string
	 */
	public function setCacheDate( $date ) {
		$this->cacheDate = $date;
	}

	/**
	 * Sets cache count
	 *
	 * @since 1.9
	 *
	 * @param int
	 */
	public function setCacheCount( $count ) {
		$this->cacheCount = $count;
	}

	/**
	 * Returns cache status
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	public function getCacheStatus() {
		return $this->cacheStatus;
	}

	/**
	 * Returns cache date
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	public function getCacheDate() {
		return $this->cacheDate;
	}

	/**
	 * Returns cache count
	 *
	 * @since 1.9
	 *
	 * @return int
	 */
	public function getCacheCount() {
		return $this->cacheCount;
	}

	/**
	 * Create a data item from the provided serialization string and type
	 * ID.
	 * @return DIConcept
	 */
	public static function doUnserialize( $serialization ) {
		$result = unserialize( $serialization );
		if ( $result === false ) {
			throw new DataItemException( "Unserialization failed." );
		}
		return $result;
	}

	public function equals( SMWDataItem $di ) {
		if ( $di->getDIType() !== SMWDataItem::TYPE_CONCEPT ) {
			return false;
		}
		return $di->getSerialization() === $this->getSerialization();
	}

	/**
	 * Implements \JsonSerializable.
	 *
	 * @since 4.0.0
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		$json = parent::jsonSerialize();
		$json['cacheStatus'] = $this->cacheStatus;
		$json['cacheDate'] = $this->cacheDate;
		$json['cacheCount'] = $this->cacheCount;
		return $json;
	}

	/**
	 * Implements JsonUnserializable.
	 *
	 * @since 4.0.0
	 *
	 * @param JsonUnserializer $unserializer Unserializer
	 * @param array $json JSON to be unserialized
	 *
	 * @return self
	 */
	public static function newFromJsonArray( JsonUnserializer $unserializer, array $json ) {
		$obj = parent::newFromJsonArray( $unserializer, $json );
		$obj->cacheStatus = $json['cacheStatus'];
		$obj->cacheDate = $json['cacheDate'];
		$obj->cacheCount = $json['cacheCount'];
		return $obj;
	}

}
