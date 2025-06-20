( ( function ( $, mw, OO, uw ) {

	'use strict';

	/**
	 * Hooks into wiki text processing of UploadWizard to extract
	 * information about file secret
	 *
	 * @param {Object} details
	 * @param {Object} result
	 */
	mw.hook( 'uploadwizard.wikitext-submit-internal' ).add( ( details, result ) => {
		if ( 'encrypted' in details.upload && 'encryption' in result ) {
			details.upload.password = result.encryption.secret;
		}
	} );

	/**
	 * Hooks into mw.UploadWizardDetails buildInterface function to
	 * add some extra inputs into file update details screen
	 *
	 * @param {mw.UploadWizardDetails} uploadWizardDetails
	 */
	mw.hook( 'uploadwizard.buildinterface-details' ).add( ( uploadWizardDetails ) => {
		uploadWizardDetails.encryptionField = new OO.ui.CheckboxInputWidget( {} );
		uploadWizardDetails.encryptionFieldLayout = new uw.FieldLayout( uploadWizardDetails.encryptionField, {
			label: mw.message( 'mwe-upwiz-encrypt' ).text(),
			required: false,
			align: 'inline'
		} );
		uploadWizardDetails.$form.append( uploadWizardDetails.encryptionFieldLayout.$element );
	} );

	/**
	 * Hooks into details form submit to check if encryption was actually
	 * request for the file by the user
	 *
	 * @param {mw.UploadWizardDetails} uploadWizardDetails
	 * @param {Object} params
	 */
	mw.hook( 'uploadwizard.wikitext-submit' ).add( ( uploadWizardDetails, params ) => {
		if ( uploadWizardDetails.encryptionField.isSelected() ) {
			params.encrypt = true;
			uploadWizardDetails.upload.encrypted = true;
		}
	} );

	/**
	 * Hooks into uw.ui.Thanks addUpload function to supply view
	 * with extra password line if file encryption was requested
	 *
	 * @param {uw.ui.Thanks} thanks
	 * @param {Object} inputs
	 */
	mw.hook( 'uploadwizard.thanks-addupload' ).add( ( thanks, upload, $thanksDiv ) => {
		if ( 'encrypted' in upload && 'password' in upload ) {
			$thanksDiv.find( '.mwe-upwiz-data' ).append(
				$( '<p>' )
					.text( mw.message( 'mwe-upwiz-thanks-secret' ).text() )
					.append(
						$( '<br />' ),
						thanks.makeReadOnlyInput( upload.password )
					)
			);
		}
	} );

} )( jQuery, mediaWiki, OO, mediaWiki.uploadWizard ) );
