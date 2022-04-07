import { addAction } from '@wordpress/hooks';

addAction( 'barn2_wizard_on_restart', 'eev-wizard', ( wizard ) => {
	wizard.setStepsCompleted( true )
} )