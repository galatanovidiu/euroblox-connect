<?php


class EBX_WC_Integration_Init {
	/**
	 * Construct the plugin.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		include_once 'EBX_WC_Integration.php';
		add_filter( 'woocommerce_integrations', [ $this, 'add_integration' ] );
	}

	/**
	 * Add a new integration to WooCommerce.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'EBX_WC_Integration';

		return $integrations;
	}
}


$EBX_WC_Integration_Init = new EBX_WC_Integration_Init();
