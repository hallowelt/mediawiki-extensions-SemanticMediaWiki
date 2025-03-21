<?php

namespace SMW\Importer\ContentCreators;

use ContentHandler;
use Onoi\MessageReporter\MessageReporterAwareTrait;
use RequestContext;
use SMW\Importer\ContentCreator;
use SMW\Importer\ImportContents;
use SMW\MediaWiki\Connection\Database;
use SMW\MediaWiki\TitleFactory;
use SMW\Utils\CliMsgFormatter;
use User;

/**
 * @license GPL-2.0-or-later
 * @since 2.5
 *
 * @author mwjames
 */
class TextContentCreator implements ContentCreator {

	use MessageReporterAwareTrait;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * @var Database
	 */
	private $connection;

	/**
	 * @var CliMsgFormatter
	 */
	private $cliMsgFormatter;

	/**
	 * @since 2.5
	 *
	 * @param TitleFactory $titleFactory
	 * @param Database $connection
	 */
	public function __construct( TitleFactory $titleFactory, Database $connection ) {
		$this->titleFactory = $titleFactory;
		$this->connection = $connection;
	}

	/**
	 * @since 2.5
	 *
	 * @param ImportContents $importContents
	 */
	public function canCreateContentsFor( ImportContents $importContents ) {
		return $importContents->getContentType() === ImportContents::CONTENT_TEXT;
	}

	/**
	 * @since 2.5
	 *
	 * @param ImportContents $importContents
	 */
	public function create( ImportContents $importContents ) {
		$this->cliMsgFormatter = new CliMsgFormatter();

		$indent = '   ...';
		$indent_e = '      ';
		$name = $importContents->getName();

		if ( $name === '' ) {
			return $this->messageReporter->reportMessage(
				$this->cliMsgFormatter->oneCol( "... no valid page name, abort import.", 3 )
			);
		}

		$title = $this->titleFactory->makeTitleSafe(
			$importContents->getNamespace(),
			$name
		);

		if ( $title === null ) {
			return $this->messageReporter->reportMessage(
				$this->cliMsgFormatter->oneCol( "... $name returned with a null title, abort import.", 3 )
			);
		}

		$page = $this->titleFactory->createPage( $title );
		$prefixedText = $title->getPrefixedText();

		$replaceable = false;
		$action = '';

		if ( $importContents->getOption( 'canReplace' ) ) {
			$replaceable = $importContents->getOption( 'canReplace' );
		} elseif ( $importContents->getOption( 'replaceable' ) ) {
			$replaceable = $importContents->getOption( 'replaceable' );
		}

		if ( isset( $replaceable['LAST_EDITOR'] ) && $replaceable['LAST_EDITOR'] === 'IS_IMPORTER' ) {
			$replaceable = $this->isCreatorLastEditor( $page );
		}

		if ( $title->exists() && !$replaceable ) {
			return $this->messageReporter->reportMessage(
				$this->cliMsgFormatter->twoCols( "... $prefixedText ...", '[EXISTS,SKIP]', 3 )
			);
		} elseif ( $title->exists() && $replaceable ) {
			$action = 'EXISTS,REPLACE';
			$len = $this->cliMsgFormatter->getLen( $action ) + 3;

			$this->messageReporter->reportMessage(
				$this->cliMsgFormatter->firstCol( "... $prefixedText ...", 3, $len )
			);
		} elseif ( $title->exists() ) {
			$action = 'EXISTS,REPLACE';
			$len = $this->cliMsgFormatter->getLen( $action ) + 3;

			$this->messageReporter->reportMessage(
				$this->cliMsgFormatter->firstCol( "... $prefixedText ...", 3, $len )
			);
		} else {
			$action = 'CREATE';
			$len = $this->cliMsgFormatter->getLen( $action ) + 3;

			$this->messageReporter->reportMessage(
				$this->cliMsgFormatter->firstCol( "... $prefixedText ...", 3, $len )
			);
		}

		// Avoid a possible "Notice: WikiPage::doEditContent: Transaction already
		// in progress (from DatabaseUpdater::doUpdates), performing implicit
		// commit ..."
		$this->connection->onTransactionCommitOrIdle( function () use ( $page, $title, $importContents, $action ) {
			$this->doCreateContent( $page, $title, $importContents, $action );
		} );
	}

	private function doCreateContent( $page, $title, $importContents, $action ) {
		$content = ContentHandler::makeContent(
			$this->fetchContents( $importContents ),
			$title
		);

		$user = null;

		if ( $importContents->getImportPerformer() !== '' ) {
			$user = User::newSystemUser( $importContents->getImportPerformer(), [ 'steal' => true ] );
		}

		// Use the global user if necessary (same as doEditContent())
		$user = $user ?? RequestContext::getMain()->getUser();
		$status = $page->doUserEditContent(
			$content,
			$user,
			$importContents->getDescription(),
			EDIT_FORCE_BOT
		);

		if ( !$status->isOk() ) {
			$action = 'FAILED';

			foreach ( $status->getErrorsArray() as $error ) {
				$importContents->addError( $error );
			}
		}

		$title->invalidateCache();

		$this->messageReporter->reportMessage(
			$this->cliMsgFormatter->secondCol( "[$action]" )
		);
	}

	private function fetchContents( $importContents ) {
		if ( $importContents->getContentsFile() === '' ) {
			return $importContents->getContents();
		}

		$contents = file_get_contents( $importContents->getContentsFile() );

		// http://php.net/manual/en/function.file-get-contents.php
		return mb_convert_encoding(
			$contents,
			'UTF-8',
			mb_detect_encoding(
				$contents,
				'UTF-8, ISO-8859-1, ISO-8859-2',
				true
			)
		);
	}

	private function isCreatorLastEditor( $page ) {
		$lastEditor = User::newFromID(
			$page->getUser()
		);

		if ( !$lastEditor instanceof User ) {
			return false;
		}

		$creator = $page->getCreator();

		if ( !$creator instanceof User ) {
			return false;
		}

		return $creator->equals( $lastEditor );
	}

}
