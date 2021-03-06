<?php
/**
 * Cart action for wishlists
 *
 * @since             1.0.0
 * @package           TInvWishlist\Public
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Cart action for wishlists
 */
class TInvWL_Public_Cart {

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	static $_n;

	/**
	 * Default post object.
	 *
	 * @var array
	 */
	static $_request;

	/**
	 * Default post object.
	 *
	 * @var array
	 */
	static $_post;
	/**
	 * This class
	 *
	 * @var \TInvWL_Public_Cart
	 */
	protected static $_instance = null;

	/**
	 * Get this class object
	 *
	 * @param string $plugin_name Plugin name.
	 * @return \TInvWL_Public_Cart
	 */
	public static function instance( $plugin_name = TINVWL_PREFIX ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $plugin_name );
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @param string $plugin_name Plugin name.
	 */
	function __construct( $plugin_name ) {
		self::$_n	 = $plugin_name;
		$this->define_hooks();
	}

	/**
	 * Define hooks
	 */
	function define_hooks() {
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( __CLASS__, 'remove_item_data' ) );
		add_action( 'woocommerce_cart_emptied', array( __CLASS__, 'remove_item_data' ) );
		if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );
		} else {
			add_action( 'woocommerce_checkout_create_order', array( $this, 'add_order_item_meta_v3' ) );
		}
	}

	/**
	 * Add product to cart from wishlist
	 *
	 * @param array   $wishlist Wishlist object.
	 * @param integer $wl_product Wishlist product id.
	 * @param integer $wl_quantity Product quantity.
	 * @return boolean
	 */
	public static function add( $wishlist = null, $wl_product = 0, $wl_quantity = 1 ) {
		if ( empty( $wishlist ) ) {
			$wishlist = tinv_wishlist_get();
		}
		$wlp = null;
		if ( 0 === $wishlist['ID'] ) {
			$wlp = TInvWL_Product_Local::instance();
		} else {
			$wlp = new TInvWL_Product( $wishlist );
		}
		$product = $wlp->get_wishlist( array( 'ID' => $wl_product ) );
		$product = array_shift( $product );
		if ( empty( $product ) ) {
			return false;
		}
		if ( empty( $product['data'] ) ) {
			return false;
		}

		self::prepare_post( $product );

		$product = apply_filters( 'tinvwl_addproduct_tocart', $product );
		$product_id		 = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product['product_id'] ) );
		$quantity		 = empty( $wl_quantity ) ? 1 : wc_stock_amount( $wl_quantity );
		$variation_id	 = $product['variation_id'];
		$variations		 = ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product['data']->variation_data : ( $product['data']->is_type( 'variation' ) ? wc_get_product_variation_attributes( $product['data']->get_id() ) : array() ) );

		if ( ! empty( $variation_id ) && is_array( $variations ) ) {
			foreach ( $variations as $name => $value ) {
				if ( '' === $value ) {
					// Could be any value that saved to a custom meta.
					if ( array_key_exists( 'meta', $product ) && array_key_exists( $name, $product['meta'] ) ) {
						$variations[ $name ] = $product['meta'][ $name ];
					} else {
						continue;
					}
				}
			}
		}

		$passed_validation = $product['data']->is_purchasable() && ( $product['data']->is_in_stock() || $product['data']->backorders_allowed() ) && 'external' !== ( version_compare( WC_VERSION, '3.0.0', '<' ) ? $product['data']->product_type : $product['data']->get_type() );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', $passed_validation, $product_id, $quantity, $variation_id, $variations );
		if ( $passed_validation ) {
			$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );
			if ( $cart_item_key ) {
				do_action( 'tinvwl_addedproduct_tocart', $cart_item_key, $product_id, $quantity, $variation_id );
				if ( ( 'private' !== $wishlist['status'] && tinv_get_option( 'processing', 'autoremove_anyone' ) ) || $wishlist['is_owner'] && 'tinvwl-addcart' === tinv_get_option( 'processing', 'autoremove_status' ) ) {
					self::ar_f_wl( $wishlist, $product_id, $quantity, $variation_id, $product['meta'] );
				}
				self::set_item_data( $cart_item_key, $wishlist['share_key'], $quantity );
				self::unprepare_post();
				if ( get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes' ) {
					wp_safe_redirect( wc_get_cart_url() );
				}
				return array( $product_id => $quantity );
			}
		}
		self::unprepare_post();
		return false;
	}

	/**
	 * Prepare _POST data
	 *
	 * @param array $product Wishlist Product.
	 */
	public static function prepare_post( $product ) {
		self::$_post	 = $_POST; // @codingStandardsIgnoreLine WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		self::$_request	 = $_REQUEST;
		if ( array_key_exists( 'meta', $product ) && ! empty( $product['meta'] ) ) {
			$_POST		 = $product['meta']; // May be a conflict there will be no GET attributes.
			$_REQUEST	 = $product['meta'];
		} else {
			$_POST		 = array();
			$_REQUEST	 = array();
		}
	}

	/**
	 * Unrepare _POST data
	 */
	public static function unprepare_post() {
		$_POST		 = self::$_post;
		$_REQUEST	 = self::$_request;
	}

	/**
	 * Get product added from wishlist
	 *
	 * @param string $cart_item_key Cart product key.
	 * @param array  $wishlist Wishlist object.
	 * @return array
	 */
	public static function get_item_data( $cart_item_key, $wishlist = null ) {
		$data = (array) WC()->session->get( 'tinvwl_wishlist_cart', array() );
		if ( empty( $data[ $cart_item_key ] ) ) {
			$data[ $cart_item_key ] = array();
		}

		if ( empty( $wishlist ) ) {
			return $data[ $cart_item_key ];
		} else {
			return empty( $data[ $cart_item_key ][ $wishlist ] ) ? 0 : $data[ $cart_item_key ][ $wishlist ];
		}
	}

	/**
	 * Set product added from wishlist
	 *
	 * @param string  $cart_item_key Cart product key.
	 * @param array   $wishlist Wishlist object.
	 * @param integer $quantity Product quantity.
	 * @return boolean
	 */
	public static function set_item_data( $cart_item_key, $wishlist, $quantity = 1 ) {
		$data = (array) WC()->session->get( '_tinvwl_wishlist_cart', array() );
		if ( empty( $data[ $cart_item_key ] ) ) {
			$data[ $cart_item_key ] = array();
		}

		if ( array_key_exists( $wishlist, $data[ $cart_item_key ] ) ) {
			$data[ $cart_item_key ][ $wishlist ] += $quantity;
		} else {
			$data[ $cart_item_key ][ $wishlist ] = $quantity;
		}

		WC()->session->set( 'tinvwl_wishlist_cart', $data );
		return true;
	}

	/**
	 * Remove product added from wishlist
	 *
	 * @param string $cart_item_key Cart product key.
	 * @param array  $wishlist Wishlist object.
	 * @return boolean
	 */
	public static function remove_item_data( $cart_item_key = null, $wishlist = null ) {
		$data = (array) WC()->session->get( 'tinvwl_wishlist_cart', array() );
		if ( empty( $cart_item_key ) ) {
			WC()->session->set( 'tinvwl_wishlist_cart', array() );
			return true;
		}
		if ( ! array_key_exists( $cart_item_key, $data ) ) {
			return false;
		}
		if ( empty( $wishlist ) ) {
			unset( $data[ $cart_item_key ] );
		} else {
			if ( ! array_key_exists( $wishlist, $data[ $cart_item_key ] ) ) {
				return false;
			}
			unset( $data[ $cart_item_key ][ $wishlist ] );
		}
		WC()->session->set( 'tinvwl_wishlist_cart', $data );
		return true;
	}

	/**
	 * Add meta data for product when created order
	 *
	 * @param string $item_id Order item id.
	 * @param string $values Not used.
	 * @param string $cart_item_key Cart product key.
	 */
	public function add_order_item_meta( $item_id, $values, $cart_item_key ) {
		$data = self::get_item_data( $cart_item_key );
		$data = apply_filters( 'tinvwl_addproduct_toorder', $data, $cart_item_key, $values );
		if ( ! empty( $data ) ) {
			wc_add_order_item_meta( $item_id, '_tinvwl_wishlist_cart', $data );
		}
	}

	/**
	 * Add meta data for product when created order
	 *
	 * @param \WC_Order $order Order object.
	 */
	public function add_order_item_meta_v3( $order ) {
		foreach ( $order->get_items() as $item ) {
			$data = self::get_item_data( $item->legacy_cart_item_key );
			$data = apply_filters( 'tinvwl_addproduct_toorder', $data, $item->legacy_cart_item_key, $item->legacy_values );
			$item->update_meta_data( '_tinvwl_wishlist_cart', $data );
		}
	}

	/**
	 * Autoremove product from wishlist
	 *
	 * @param array   $wishlist Wishlist object.
	 * @param integer $product_id Product id.
	 * @param integer $quantity Quantity product.
	 * @param integer $variation_id Variation product id.
	 * @param array   $meta Meta array for post form.
	 * @return integer
	 */
	private static function ar_f_wl( $wishlist, $product_id, $quantity = 1, $variation_id = 0, $meta = array() ) {
		$product_id		 = absint( $product_id );
		$quantity		 = absint( $quantity );
		$variation_id	 = absint( $variation_id );
		if ( ! tinv_get_option( 'processing', 'autoremove' ) || empty( $wishlist ) || empty( $product_id ) || empty( $quantity ) ) {
			return $quantity;
		}
		$wlp = null;
		if ( 0 === $wishlist['ID'] ) {
			$wlp = TInvWL_Product_Local::instance();
		} else {
			$wlp = new TInvWL_Product( $wishlist, self::$_n );
		}
		if ( empty( $wlp ) ) {
			return 0;
		}
		$products	 = $wlp->get_wishlist( array(
			'product_id'	 => $product_id,
			'variation_id'	 => $variation_id,
			'meta'			 => $meta,
			'external'		 => false,
		) );
		$product	 = array_shift( $products );
		if ( empty( $product ) ) {
			return $quantity;
		}
		$wlp->remove_product_from_wl( 0, $product_id, $variation_id, $product['meta'] );
		return 0;
	}
}
