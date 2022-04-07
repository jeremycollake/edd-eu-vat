<?php

namespace Barn2\Plugin\EDD_VAT;

/**
 * Holds VAT information relating to a EDD payment record.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class Payment_VAT {

	public $vat_number;
	public $is_vat_number_valid;
	public $is_reverse_charged;
	public $is_eu_payment;
	public $name;
	public $address;
	public $consultation_number;

}
