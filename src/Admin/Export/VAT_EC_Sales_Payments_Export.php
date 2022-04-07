<?php

namespace Barn2\Plugin\EDD_VAT\Admin\Export;

use Barn2\VAT_Lib\Registerable,
	Barn2\VAT_Lib\Service;

/**
 * Registrations and setup of the Batch Export with EDD.
 *
 * @package     Barn2\edd-eu-vat
 * @author      Barn2 Plugins <support@barn2.com>
 * @license     GPL-3.0
 * @copyright   Barn2 Media Ltd
 */
class VAT_EC_Sales_Payments_Export implements Registerable, Service {

	/**
	 * File path to the batch class.
	 *
	 * @var string $batch_class_path
	 */
	private $batch_class_path;

	/**
	 * File path for the templates.
	 *
	 * @var string $template_path
	 */
	private $template_path;

	/**
	 * Constructor
	 *
	 * @param string $template_path
	 */
	public function __construct( $template_path ) {
		$this->batch_class_path = __DIR__ . '/Batch_VAT_EC_Sales_Payments_Export.php';
		$this->template_path    = trailingslashit( $template_path );
	}

	/**
	 * Register hooks and filters
	 */
	public function register() {
		add_action( 'edd_register_batch_exporter', [ $this, 'include_batch_exporter' ], 10 );
		add_action( 'edd_reports_tab_export_content_bottom', [ $this, 'include_meta_box' ], 10 );
	}

	/**
	 * Include batch exporter
	 */
	public function include_batch_exporter() {
		add_action( 'edd_batch_export_class_include', [ $this, 'include_vat_payments_batch_processer' ], 10, 1 );
	}

	/**
	 * Require Batch Export
	 *
	 * @param string $class
	 */
	public function include_vat_payments_batch_processer( $class ) {
		if ( 'Batch_VAT_EC_Sales_Payments_Export' === $class ) {
			require_once $this->batch_class_path;
		}
	}

	/**
	 * Include metabox template
	 */
	public function include_meta_box() {
		include $this->template_path . 'export-vat-ec-sales-payments.php';
	}

}
