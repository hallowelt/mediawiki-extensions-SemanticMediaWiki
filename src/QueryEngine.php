<?php

namespace SMW;

use SMW\Query\QueryResult;
use SMWQuery as Query;

/**
 * Interface for query answering that depend on concrete implementations to
 * provide the filtering and matching process for specific conditions against a
 * select back-end.
 *
 * @license GPL-2.0-or-later
 * @since 2.5
 *
 * @author mwjames
 */
interface QueryEngine {

	/**
	 * Returns a QueryResult object that matches the condition described by a
	 * query.
	 *
	 * @note If the request was made for a debug (querymode MODE_DEBUG) query
	 * then a simple HTML-compatible string is returned.
	 *
	 * @since 2.5
	 *
	 * @param Query $query
	 *
	 * @return QueryResult|string
	 */
	public function getQueryResult( Query $query );

}
