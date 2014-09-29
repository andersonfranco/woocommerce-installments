<?php
/**
Plugin Name: WooCommerce Installments
Plugin URI: https://github.com/AndersonFranco/woocommerce-installments
Description: This plugin appends installments into the product price.
Author: Anderson Franco
Author URI: http://www.francotecnologia.com/
Version: 1.1.1
License: GPLv2 or later
*/

// ONLY PRODUCTS WITH PRICE GREATER THAN OR EQUAL TO:
define( 'FRANCOTECNOLOGIA_WC_PARCPAGSEG_PRICE_GTET', 0 ); // $ 1000.00

// MINIMUM MONTHLY PAYMENT - MUST BE GREATER THAN ZERO:
define( 'FRANCOTECNOLOGIA_WC_PARCPAGSEG_MINIMUM_MONTHLY_PAYMENT', 5 ); // $ 5.00 

// NUMBER OF THE COLUMNS OF THE TABLE:
define( 'FRANCOTECNOLOGIA_WC_PARCPAGSEG_TABLE_COLUMNS', 2 );

// ADD TO CART - BUTTON POSITION: TOP = true, BOTTOM = false
define( 'FRANCOTECNOLOGIA_WC_PARCPAGSEG_ADD_TO_CART_BUTTON_POSITION', false );

// USE COEFFICIENT TABLE / INTEREST RATES - LINE 55:
define( 'FRANCOTECNOLOGIA_WC_PARCPAGSEG_USE_COEFFICIENT_TABLE', false );

// CART PAGE MESSAGE:
// ADD %d TO SHOW MAX INSTALLMENTS ALLOWED
// OR LEAVE EMPTY FOR NO MESSAGE
define( 'FRANCOTECNOLOGIA_WC_PARCPAGSEG_CART_PAGE_MESSAGE', 
  'NO interest for %d months' // e.g. (Portuguese) * Pague sua compra em at&eacute; %d vezes
);

// Translate the words below in your own language:
// or (e.g. ou)
// Installments (e.g. Parcelas)
// Amount (e.g. Valor)
// InStock (e.g. Em estoque)
// OutOfStock (e.g. Sem estoque)

// // // // // // // // // // // // // // // // // // // // // // // // // // //

function francotecnologia_wc_parcpagseg_calculate_installment( $price = 0.00, $installment = 0 ) {

  $price        = (float) $price;
  $installment  = (int) $installment;
  $result       = new stdClass(); 

  if ( $installment < 1 || $installment > 12 ) {
    $result->price = 0;
    $result->total = 0;
  } else if ( FRANCOTECNOLOGIA_WC_PARCPAGSEG_USE_COEFFICIENT_TABLE ) {
    
    // INTEREST RATES OF PAGSEGURO.COM.BR
    $coefficient = array( 
      1, 0.52255, 0.35347, 
      0.26898, 0.21830, 0.18453, 
      0.16044, 0.14240, 0.12838, 
      0.11717, 0.10802, 0.10040
    );

    $result->price = sprintf( "%0.2f", $price * $coefficient[ $installment - 1 ] );
    $result->total = sprintf( "%0.2f", ( $price * $coefficient[ $installment - 1 ] ) * $installment );
  } else {
    $result->price = sprintf( "%0.2f", ($price / $installment) );
    $result->total = sprintf( "%0.2f", ($price / $installment) * $installment );
  }

  return $result;
}

function francotecnologia_wc_parcpagseg_get_price( $price = null ) {
  if ( $price === null ) {
    $product = get_product();
    if ( $product->get_price() ) {
      $price = $product->get_price();
    }
  }  
  return $price;
}

function francotecnologia_wc_parcpagseg_get_installments( $price = 0.00 ) {
  $installments = round( $price / FRANCOTECNOLOGIA_WC_PARCPAGSEG_MINIMUM_MONTHLY_PAYMENT );
  if ( $installments > 12 ) {
    $installments = 12;
  } else if ( $installments < 1 ) { 
    $installments = 1;
  }
  return $installments;
}

function francotecnologia_wc_parcpagseg_get_parceled_value( $price = null ) {
  $price = francotecnologia_wc_parcpagseg_get_price( $price );
  if ( $price > 0 ) {
    $installments = francotecnologia_wc_parcpagseg_get_installments( $price );
    $calc = francotecnologia_wc_parcpagseg_calculate_installment( $price, $installments );
    return $installments . 'x ' . wc_price( $calc->price );
  } else {
    return '';
  }
}

function francotecnologia_wc_parcpagseg_get_parceled_table( $price = null ) {
  $price = francotecnologia_wc_parcpagseg_get_price( $price );
  if ( $price > 0 ) {
    $installments = francotecnologia_wc_parcpagseg_get_installments( $price );
    $table = '<table class="francotecnologia_wc_parcpagseg_table">';
    $table .= '<tr>';
    $table .= str_repeat('<th>' . __('Installments') . '</th><th>' . __('Amount') . '</th>', FRANCOTECNOLOGIA_WC_PARCPAGSEG_TABLE_COLUMNS);
    $table .= '</tr>';
    $tdCounter = 0;
    foreach ( range(1, $installments) as $parcel ) {
      $calc = francotecnologia_wc_parcpagseg_calculate_installment( $price, $parcel );
      $tdCounter = 1 + $tdCounter % FRANCOTECNOLOGIA_WC_PARCPAGSEG_TABLE_COLUMNS;
      if ( $tdCounter == 1 ) {
        $table .= '<tr>';
      }      
      $table .= '<th>' . $parcel . '</th><td>' . wc_price( $calc->price ) . '</td>';
      if ( $tdCounter == FRANCOTECNOLOGIA_WC_PARCPAGSEG_TABLE_COLUMNS ) {
        $table .= '</tr>';
      }      
    }
    if ( substr( $table, -5 ) != '</tr>' ) {
      $table .= '</tr>';
    }    
    $table .= '</table>';
    return $table;
  } else {
    return '';
  }
}

function francotecnologia_wc_parcpagseg_loop_item() {
  if ( francotecnologia_wc_parcpagseg_get_price() >= FRANCOTECNOLOGIA_WC_PARCPAGSEG_PRICE_GTET ) {
    echo ' <span style="color: #00ADEF; font-size: 100%" class="price">' . __('or') . ' '
         . francotecnologia_wc_parcpagseg_get_parceled_value() . '</span>';
  }
}

function francotecnologia_wc_parcpagseg_single_product() {
  if ( francotecnologia_wc_parcpagseg_get_price() < FRANCOTECNOLOGIA_WC_PARCPAGSEG_PRICE_GTET ) {
    woocommerce_template_single_price();
    return;
  }
  $product = get_product();
  ?>
  <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
    <p class="price"><?php echo $product->get_price_html(); ?> <span style="color: #00ADEF; font-size: 75%"><?php echo __('or') . ' ' . francotecnologia_wc_parcpagseg_get_parceled_value(); ?></span></p>
    <?php echo francotecnologia_wc_parcpagseg_get_parceled_table(); ?>
    <meta itemprop="price" content="<?php echo $product->get_price(); ?>" />
    <meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
    <link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? __('InStock') : __('OutOfStock'); ?>" />
  </div>
  <?php
}

function francotecnologia_wc_parcpagseg_cart() {
  global $woocommerce;
  if ( $woocommerce->cart->total < FRANCOTECNOLOGIA_WC_PARCPAGSEG_PRICE_GTET ) {
    return;
  }
  if ( $woocommerce->cart->total ) {
    $installments = francotecnologia_wc_parcpagseg_get_parceled_value( $woocommerce->cart->total );
  } else {
    $installments = "";
  }
  ?>
  <tr><th colspan="2" style="color: #00ADEF; font-size: 100%;border-bottom: 1px solid #e8e4e3;"><?php echo stripos(FRANCOTECNOLOGIA_WC_PARCPAGSEG_CART_PAGE_MESSAGE,'%d') !== false ? sprintf(FRANCOTECNOLOGIA_WC_PARCPAGSEG_CART_PAGE_MESSAGE, $installments) : FRANCOTECNOLOGIA_WC_PARCPAGSEG_CART_PAGE_MESSAGE; ?></th></tr>
  <?php
}

function francotecnologia_wc_parcpagseg_alter_woo_hooks() {
  // Product Page
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );  
  add_action( 'woocommerce_single_product_summary', 'francotecnologia_wc_parcpagseg_single_product', 
    ((FRANCOTECNOLOGIA_WC_PARCPAGSEG_ADD_TO_CART_BUTTON_POSITION)?30:10) );

  // Catalog
  add_action( 'woocommerce_after_shop_loop_item_title', 'francotecnologia_wc_parcpagseg_loop_item', 20 );

  // Cart
  if ( FRANCOTECNOLOGIA_WC_PARCPAGSEG_CART_PAGE_MESSAGE != '' ) {
    add_action( 'woocommerce_cart_totals_after_order_total', 'francotecnologia_wc_parcpagseg_cart', 20 );
  }
}

add_action('plugins_loaded','francotecnologia_wc_parcpagseg_alter_woo_hooks');
