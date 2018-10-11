<?php
if ( ! class_exists( 'WooCommerce_Checkout_Vat_No_Plugin' ) ) {
	class WooCommerce_Checkout_Vat_No_Plugin {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance;

		/**
		 * Returns an instance of this class.
		 *
		 * @since  1.0.0
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {

			if ( null == self::$instance ) {
				self::$instance = new WooCommerce_Checkout_Vat_No_Plugin();
			}

			return self::$instance;
		}

		/**
		 * Initializes the plugin by setting filters and administration functions.
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );

			add_filter( 'woocommerce_checkout_fields', array( $this, 'add_vat_number_field' ) );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta' ), 11, 2 );

			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'add_vat_number_to_admin' ) );
			add_action( 'save_post', array( $this, 'save_order_meta' ), 10, 3 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_validation' ), 10, 2 );
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since 1.0.0
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain(
				'woocommerce-checkout-vat-no',
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages/'
			);
		}

		/**
		 * Enqueue public-facing JavaScript and stylesheet.
		 *
		 * @since 1.0.0
		 */
		public function assets() {

			// This check says that it's not checkout page.
			if ( ! is_checkout() ) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'woo-checkout-vat-no-script', plugin_dir_url( __FILE__ ) . "js/scripts{$min}.js", array( 'jquery' ), '1.0.0', true );
			wp_enqueue_style( 'woo-checkout-vat-no-style', plugin_dir_url( __FILE__ ) . 'css/styles.css', array(), '1.0.0' );
		}

		/**
		 * Adds a VAT No. field on checkout page.
		 *
		 * @since 1.0.0
		 * @param array $fields
		 */
		public function add_vat_number_field( $fields ) {
			$fields['billing']['vat_number'] = array(
				'label'       => __( 'VAT No.', 'woocommerce-checkout-vat-no' ),
				'placeholder' => _x( 'VAT No.', 'placeholder', 'woocommerce-checkout-vat-no' ),
				'required'    => false,
				'class'       => array( 'form-row-wide', 'hidden' ),
				'clear'       => true,
				'priority'    => 35,
			);

			return $fields;
		}

		/**
		 * Save VAT No. to the order meta.
		 *
		 * @since 1.0.0
		 * @param int   $order_id
		 * @param array $data
		 */
		public function update_order_meta( $order_id, $data ) {
			if ( ! empty( $_POST['vat_number'] ) ) {
				update_post_meta( $order_id, 'vat_number', sanitize_text_field( $_POST['vat_number'] ) );
			}
		}

		/**
		 * Adds a VAT. No field on Edit order admin page.
		 *
		 * @since 1.0.0
		 * @param WC_Order $order Order object.
		 */
		public function add_vat_number_to_admin( $order ) {
			$vat_number = get_post_meta( $order->get_id(), 'vat_number', true );

			if ( ! empty( $vat_number ) ) : ?>
				<div class="address">
					<p>
						<strong><?php esc_html_e( 'VAT No.', 'woocommerce-checkout-vat-no' ); ?></strong>
						<?php echo $vat_number; ?>
					</p>
				</div>
			<?php endif; ?>

			<div class="edit_address"><?php
				woocommerce_wp_text_input( array(
					'id'            => 'vat_number',
					'label'         => esc_html__( 'VAT No.', 'woocommerce-checkout-vat-no' ),
					'wrapper_class' => 'form-field-wide',
					'value'         => $vat_number,
					'description'   => '',
					'desc_tip'      => false,
				) );
			?></div><?php
		}

		/**
		 * Save the order data on admin panel.
		 *
		 * @since 1.0.0
		 * @param int $order_id
		 */
		public function save_order_meta( $post_id, $post, $update ) {
			$do_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
			$is_autosave = wp_is_post_autosave( $post_id );
			$is_revision = wp_is_post_revision( $post_id );

			if ( $do_autosave || $is_autosave || $is_revision ) {
				return;
			}

			$post_type = get_post_type( $post );

			if ( 'shop_order' !== $post_type ) {
				return;
			}

			if ( isset( $_POST[ 'vat_number' ] ) ) {
				$vat_number = wc_clean( $_POST['vat_number'] );

				if ( '' === $vat_number ) {
					delete_post_meta( $post_id, 'vat_number' );
					return;
				}

				$result = $this->vat_no_validation( $vat_number );

				if ( $result ) {
					update_post_meta( $post_id, 'vat_number', $vat_number );
				} else {
					add_option( 'woo_checkout_vat_no_show_fail_notice', true, '', 'no' );
				}
			}
		}

		/**
		 * Show admin notice if VAT number is not valid.
		 *
		 * @since 1.0.0
		 */
		public function admin_notices() {
			if ( get_option( 'woo_checkout_vat_no_show_fail_notice' ) ) {
				$message = sprintf( '<strong>%1$s</strong> %2$s', esc_html__( 'Error:', 'woocommerce-checkout-vat-no' ), esc_html__( 'is not a valid VAT number.', 'woocommerce-checkout-vat-no' ) );
				echo '<div class="notice notice-error is-dismissible"><p>'. $message .'</p></div>';
				delete_option( 'woo_checkout_vat_no_show_fail_notice' );
			}
		}

		/**
		 * Show on Checkout page a notice if VAT No. is not valid.
		 *
		 * @since 1.0.0
		 * @param array    $data   An array of posted data.
		 * @param WP_Error $errors Validation errors.
		 */
		public function checkout_validation( $data, $errors ) {

			if ( ! empty( $data['vat_number'] ) ) {
				$result = $this->vat_no_validation( $data['vat_number'] );

				if ( ! $result ) {
					$errors->add( 'validation', sprintf( '<strong>%1$s</strong> %2$s', esc_html__( 'VAT No.', 'woocommerce-checkout-vat-no' ), esc_html__( 'is not a valid.', 'woocommerce-checkout-vat-no' ) ) );
				}
			}
		}

		/**
		 * Run a VAT No. validator.
		 *
		 * @since  1.0.0
		 * @param  int $check_it
		 * @return bool
		 */
		public function vat_no_validation( $check_it ) {
			require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

			$validator = new DvK\Vat\Validator();

			return $validator->validateFormat( $check_it );
		}
	}
}
