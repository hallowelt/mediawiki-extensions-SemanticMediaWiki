## SMW::SQLStore::Installer::BeforeCreateTablesComplete

### Adding primary keys

Demonstrates how to add primary keys ([#3559][issue-3559]) to Semantic MediaWiki table definitions.

```php
MediaWikiServices::getInstance()->getHookContainer()->register( 'SMW::SQLStore::Installer::BeforeCreateTablesComplete', function( array $tables, $messageReporter ) {

	// #3559
	// Incomplete list to only showcase how to modify the table definition
	$primaryKeys = [
		'smw_di_blob'     => 'p_id,s_id,o_hash',
		'smw_di_bool'     => 'p_id,s_id,o_value',
		'smw_di_uri'      => 'p_id,s_id,o_serialized',
		'smw_di_coords'   => 'p_id,s_id,o_serialized',
		'smw_di_wikipage' => [ 'addPrimaryID', 'id' ],
		'smw_di_number'   => 'p_id,s_id,o_serialized',

		// smw_fpt ...

		'smw_prop_stats'  => 'p_id',
		'smw_query_links' => 's_id,o_id',
		'smw_prop_stats'  => 'p_id',
		'smw_ft_search'   => 's_id,p_id,o_sort'
	];

	/**
	 * @var \Onoi\MessageReporter\MessageReporter
	 */
	$messageReporter->reportMessage( "Setting primary indices.\n" );

	/**
	 * @var \SMW\SQLStore\TableBuilder\Table[]
	 */
	foreach ( $tables as $table ) {
		$key = $primaryKeys[$table->getName()] ?? false;
		if ( is_string( $key ) ) {
			$table->setPrimaryKey( $primaryKeys[$table->getName()] );
		} elseif ( is_array( $key ) && $key[0] === "addPrimaryID" && is_string( $key[1] ?? false ) ) {
			$table->addColumn( $key[1], 'id_primary' );
		}
	}

	$messageReporter->reportMessage( "\ndone.\n" );

} );
```

## See also

- [`hook.sqlstore.installer.beforecreatetablescomplete`](https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks/hook.sqlstore.installer.beforecreatetablescomplete.md)

[issue-3559]:https://github.com/SemanticMediaWiki/SemanticMediaWiki/issues/3559
