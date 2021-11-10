<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

	if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
		$product_added = $_product->get_name();
	}
}

$args = array(
	'post_type'      => 'product',
	'posts_per_page' => 2
);

$loop = new WP_Query( $args );
?>
<!--
<div class="subscription-con">
 <?php 

    while ( $loop->have_posts() ) : $loop->the_post();
        global $product;
		
		if ( \SkyVerge\WooCommerce\Memberships\Teams\Product::has_per_member_pricing( $product ) ) : 
		?>
			<form class="cart" action="<?php echo do_shortcode('[add_to_cart_url id="'. $product->get_id() .'"]'); ?>" method="post" enctype='multipart/form-data'>
				<div class="executive-outer-con">
					<div class="the-executive-checkout active">
						<div class="radio-con">
							<input type="radio" id="the-essential-radio" name="essential-radio" class=" radio-style" <?=($product->name == $product_added)?"checked":"";?>>
						</div>	
						<div class="essential-description">
							<h1 class="the-essestial-text"><?=$product->name;?></h1>
							<h2 class="checkout-price"><?=get_woocommerce_currency_symbol().$product->price;?>/member</h2>
							<p class="checkout-bill-text">Billed yearly. For teams over 6.</p>
						</div>
					</div>
					<div class="team-name-con">
						<label for="team_name" class="">Team Name&nbsp;<abbr class="required" title="required">*</abbr></label>
						<input type="text" class="input-text " name="team_name" id="team_name" placeholder="" value="Your Team Name">
					</div>
					<div class="executive-member-con">
							<p>How many members?</p>
							<?php
								woocommerce_quantity_input( array(
									'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
									'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
									'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
								) );
							?>
					</div>
					
				</div>
			</form>
		<?php else : ?>
			<form class="cart" action="<?php echo do_shortcode('[add_to_cart_url id="'. $product->get_id() .'"]'); ?>" method="post" enctype='multipart/form-data'>
				<div class="the-essentials-checkout">
					<div class="radio-con">
						<input type="radio" id="the-essential-radio" name="essential-radio" class="radio-style" <?=($product->name == $product_added)?"checked":"";?>>
					</div>	
					<div class="essential-description">
						<h1 class="the-essestial-text"><?=$product->name; ?></h1>
						<h2 class="checkout-price"><?=get_woocommerce_currency_symbol().$product->price;?>/year</h2>
						<p class="checkout-bill-text">Billed yearly.</p>
					</div>
				</div>
			</form>
		<?php endif;

    endwhile;

    wp_reset_query();
?>
</div>-->

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>
	
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	
	<!--<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>-->
	
	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
