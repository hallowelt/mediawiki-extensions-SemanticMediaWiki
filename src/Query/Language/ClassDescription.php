<?php

namespace SMW\Query\Language;

use Exception;
use SMW\DataValueFactory;
use SMW\DIWikiPage;
use SMW\Localizer;

/**
 * Description of a single class as given by a wiki category, or of a
 * disjunction of such classes. Corresponds to (disjunctions of) atomic classes
 * in OWL and to (unions of) classes in RDF.
 *
 * @license GNU GPL v2+
 * @since 1.6
 *
 * @author Markus Krötzsch
 */
class ClassDescription extends Description {

	/**
	 * @var array of DIWikiPage
	 */
	protected $m_diWikiPages;

	/**
	 * @var boolean
	 */
	private $isNot = false;

	/**
	 * @param mixed $content DIWikiPage or array of DIWikiPage
	 * @param boolean $isNot
	 *
	 * @throws Exception
	 */
	public function __construct( $content, $isNot = false ) {
		if ( $content instanceof DIWikiPage ) {
			$this->m_diWikiPages = array( $content );
		} elseif ( is_array( $content ) ) {
			$this->m_diWikiPages = $content;
		} else {
			throw new Exception( "ClassDescription::__construct(): parameter must be an DIWikiPage object or an array of such objects." );
		}

		$this->isNot = $isNot;
	}

	/**
	 * @param ClassDescription $description
	 */
	public function addDescription( ClassDescription $description ) {
		$this->m_diWikiPages = array_merge( $this->m_diWikiPages, $description->getCategories() );
	}

	/**
	 * @see Description::getFingerprint
	 * @since 2.5
	 *
	 * @return string
	 */
	public function getFingerprint() {

		$hash = array();

		foreach ( $this->m_diWikiPages as $subject ) {
			$hash[$subject->getHash()] = true;
		}

		ksort( $hash );

		return 'Cl:' . md5( implode( '|', array_keys( $hash ) ) . $this->isNot );
	}

	/**
	 * @since 3.0
	 *
	 * @return boolean
	 */
	public function isNot() {
		return $this->isNot;
	}

	/**
	 * @return array of DIWikiPage
	 */
	public function getCategories() {
		return $this->m_diWikiPages;
	}

	public function getQueryString( $asValue = false ) {

		$first = true;
		$isNot = $this->isNot ? '!' : '';
		$ns = Localizer::getInstance()->getNamespaceTextById( NS_CATEGORY );

		foreach ( $this->m_diWikiPages as $wikiPage ) {
			$wikiValue = DataValueFactory::getInstance()->newDataValueByItem( $wikiPage, null );
			if ( $first ) {
				$result = '[[' . $ns . ':' . $isNot . $wikiValue->getText();
				$first = false;
			} else {
				$result .= '||' . $wikiValue->getText();
			}
		}

		$result .= ']]';

		if ( $asValue ) {
			return ' <q>' . $result . '</q> ';
		}

		return $result;
	}

	public function isSingleton() {
		return false;
	}

	public function getSize() {

		if ( $GLOBALS['smwgQSubcategoryDepth'] > 0 ) {
			return 1; // disj. of cats should not cause much effort if we compute cat-hierarchies anyway!
		}

		return count( $this->m_diWikiPages );
	}

	public function getQueryFeatures() {

		if ( count( $this->m_diWikiPages ) > 1 ) {
			return SMW_CATEGORY_QUERY | SMW_DISJUNCTION_QUERY;
		}

		return SMW_CATEGORY_QUERY;
	}

	public function prune( &$maxsize, &$maxdepth, &$log ) {

		if ( $maxsize >= $this->getSize() ) {
			$maxsize = $maxsize - $this->getSize();
			return $this;
		} elseif ( $maxsize <= 0 ) {
			$log[] = $this->getQueryString();
			$result = new ThingDescription();
		} else {
			$result = new ClassDescription( array_slice( $this->m_diWikiPages, 0, $maxsize ), $this->isNot );
			$rest = new ClassDescription( array_slice( $this->m_diWikiPages, $maxsize ), $this->isNot );

			$log[] = $rest->getQueryString();
			$maxsize = 0;
		}

		$result->setPrintRequests( $this->getPrintRequests() );
		return $result;
	}

}
