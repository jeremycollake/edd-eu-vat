
var EDD_EU_VAT = ( function( $, window, document, params ) {

    // Back-compat
    window.euCountries = params.countries;

    function hideResult() {
        $( '#edd-vat-check-result' ).remove();
    }

    function showError( $el, message ) {
        $el.append( '<span id="edd-vat-check-result" class="edd-vat-check-result edd-vat-check-error">' + message + '</span>' );
    }

    var eddVatCheck = function( event ) {
        var $vatField = $( '#edd-card-vat-wrap' ),
            billingCountry = $( '#billing_country' ).val(),
            vatNumber = $( '#edd-vat-number' ).val();

        if ( !$vatField.length ) {
            return false;
        }

        if ( $vatField.data( 'check' ) ) {
            return false;
        }

        hideResult();

        if ( !vatNumber ) {
            showError( $vatField, params.messages.vat_number_missing );
            return false;
        }

        if ( !billingCountry ) {
            showError( $vatField, params.messages.country_missing );
            return false;
        }

        var postData = {
            action: 'edd_vat_check',
            billing_country: billingCountry,
            vat_number: vatNumber,
            nonce: $( '#edd-checkout-address-fields-nonce' ).val()
        };

        $vatField.data( 'check', true );

        var $spinner = $( '<span class="edd-loading-ajax edd-loading"></span>' );
        $( '#edd-vat-check-button' ).after( $spinner );

        $( document.body ).trigger( 'edd_eu_vat:before_vat_check', postData );

        $.ajax( {
            type: 'POST',
            data: postData,
            dataType: 'json',
            url: edd_global_vars.ajaxurl,
            xhrFields: {
                withCredentials: true
            }
        } )
            .done( function( response, textStatus, jqXHR ) {
                if ( jqXHR.status == 200 && typeof response.html !== 'undefined' ) {
                    var $updatedCart = $( $.parseHTML( response.html.trim() ) ).filter( '#edd_checkout_cart_form' );

                    // Update cart.
                    if ( $updatedCart.length ) {
                        $( '#edd_checkout_cart_form' ).replaceWith( $updatedCart );
                    }

                    // Update totals.
                    $( '.edd_cart_amount' ).html( response.total );

                    // Add VAT result message.
                    $vatField.append( response.vat_check_result );

                    // Remove EDD SL upgrade prices notices if we clear the VAT rate.
                    if ( response.tax_rate_raw === 0 && params.hide_edd_sl_notices ) {
                        $( '#edd-recurring-sl-auto-renew' ).each( function() {
                            $( this ).remove();
                        } );

                        $( '#edd-recurring-sl-cancel-replace' ).each( function() {
                            $( this ).remove();
                        } );
                    }

                    // Create tax data (in same format as EDD) and trigger edd_taxes_recalulcated to ensure everything is up to date.
                    var taxData = {
                        postdata: postData,
                        response: response
                    };

                    $( document.body )
                        .trigger( 'edd_taxes_recalculated', taxData )
                        .trigger( 'edd_eu_vat:vat_check', response );
                } else {
                    showError( $vatField, params.messages.ajax_error );
                }
            } )
			.fail( function( jqXHR, textStatus, errorThrown ) {
                showError( $vatField, params.messages.ajax_error );
            } )
            .always( function() {
                $spinner.remove();
                $vatField.data( 'check', false );

                $( document.body ).trigger( 'edd_eu_vat:vat_check_complete' );
            } );

        return false;
    };

    // Hide VAT field if country not in list of EU countries.
    function eddCountryCheck() {
        if ( !params.countries ) {
            return;
        }

        var billingCountry = $( '#billing_country' ).val();

        if ( billingCountry && -1 !== params.countries.indexOf( billingCountry ) ) {
            $( '#edd-card-vat-wrap' ).show();
        } else {
            $( '#edd-card-vat-wrap' ).hide();
            hideResult();
        }
    }

    $( function() {
        // Bind events for purchase form.
        $( '#edd_purchase_form' )
            .on( 'click', '#edd-vat-check-button', eddVatCheck )
            .on( 'change', '#billing_country', function( event ) {
                var vatData = $( '#edd-vat-check-result' ).data();

                // Clear previous VAT number and result if country is changed.
                if ( vatData && vatData.valid && vatData.country && vatData.country !== $( this ).val() ) {
                    $( '#edd-vat-number' ).val( '' );
                    hideResult();
                }

                eddCountryCheck();
            } )
            .on( 'change', '#edd-stripe-update-billing-address', function( event ) {
                // Prevent EDD Stripe hiding the VAT field when toggling the billing fields for saved addresses.
                eddCountryCheck();
            } );

    } );

    $( document.body )
        .on( 'edd_gateway_loaded', function( e, gateway ) {
            // Trigger EU country check when payment gateway loaded.
            eddCountryCheck();

            // Also check EU country when 'Add new' card option selected in EDD Stripe.
            $( '#edd-stripe-add-new' ).on( 'change', function( e ) {
                eddCountryCheck();
            } );
        } );

    return {
        checkVatNumber: eddVatCheck,
        checkCountry: eddCountryCheck
    };

}( jQuery, window, document, edd_eu_vat_params ) );
