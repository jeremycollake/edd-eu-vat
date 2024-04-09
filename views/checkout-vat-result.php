<?php
/**
 * Template for the VAT check result.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<span id="edd-vat-check-result" class="edd-vat-check-result <?php echo esc_attr( $result_class ); ?>" data-valid="<?php echo esc_attr( $vat_details->is_valid() ); ?>" data-country="<?php echo esc_attr( $vat_details->country_code ); ?>">
	<?php echo esc_html( $result_text ); ?>
</span>

