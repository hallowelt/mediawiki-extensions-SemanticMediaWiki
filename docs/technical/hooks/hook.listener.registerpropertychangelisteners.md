## SMW::Listener::ChangeListener::RegisterPropertyChangeListeners

* Since: 3.2
* Description: Hook to allow adding custom listeners to watch property changes as they appear during a data update.
* Reference class: [`PropertyChangeListener.php`][PropertyChangeListener.php]

### Signature

```php
use MediaWiki\MediaWikiServices;
use SMW\Listener\ChangeListener\ChangeListeners\PropertyChangeListener;

MediaWikiServices::getInstance()->getHookContainer()->register( 'SMW::Listener::ChangeListener::RegisterPropertyChangeListeners', function( PropertyChangeListener $propertyChangeListener ) {

	return true;
} );
```

### Example

```php
use MediaWiki\MediaWikiServices;
use SMW\Listener\ChangeListener\ChangeRecord;
use SMW\Listener\ChangeListener\ChangeListeners\PropertyChangeListener;
use SMW\DIProperty;

class ActOnPropertyChange {

	/**
	 * @param PropertyChangeListener $propertyChangeListener
	 */
	public function registerPropertyChangeListener( PropertyChangeListener $propertyChangeListener ) {

		$propertyChangeListener->addListenerCallback(
			new DIProperty( 'PropertyFoo' ),
			[ $this, 'onChange' ]
		);

		$propertyChangeListener->addListenerCallback(
			new DIProperty( 'PropertyBar' ),
			[ $this, 'onChange' ]
		);
	}

	/**
	 * @param DIProperty $property
	 * @param ChangeRecord $changeRecord
	 */
	public function onChange( DIProperty $property, ChangeRecord $changeRecord ) {

		if ( $property->getKey() === 'PropertyFoo' ) {
			foreach ( $changeRecord as $record ) {
				...
			}
		}

		if ( $property->getKey() === 'PropertyBar' ) {
			foreach ( $changeRecord as $record ) {
				...
			}
		}
	}
}

MediaWikiServices::getInstance()->getHookContainer()->register( 'SMW::Listener::ChangeListener::RegisterPropertyChangeListeners', function( PropertyChangeListener $propertyChangeListener ) {
	$actOnPropertyChange = new ActOnPropertyChange();
	$actOnPropertyChange->registerPropertyChangeListener( $propertyChangeListener );
} );
```

[PropertyChangeListener.php]:https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/src/Listener/ChangeListener/ChangeListeners/PropertyChangeListener.php
