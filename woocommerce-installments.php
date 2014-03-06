<?php
/**
Plugin Name: WooCommerce Installments
Plugin URI: https://github.com/AndersonFranco/woocommerce-installments
Description: This plugin appends installments into the product price.
Author: Anderson Franco
Author URI: http://www.francotecnologia.com/
Version: 1.0.1
License: GPLv2 or later
*/

function francotecnologia_wc_parcpagseg_calculate_installment( $price = 0.00, $installment = 0 ) {

  $price        = (float) $price;
  $installment  = (int) $installment;
  $result       = new stdClass(); 

  if ( $installment < 1 || $installment > 12 ) {
    $result->price = 0;
    $result->total = 0;
    return $result;
  }

  $coefficient = array( 
    1, 0.52255, 0.35347, 
    0.26898, 0.21830, 0.18453, 
    0.16044, 0.14240, 0.12838, 
    0.11717, 0.10802, 0.10040
  );

  $result->price = sprintf( "%0.2f", $price * $coefficient[ $installment - 1 ] );
  $result->total = sprintf( "%0.2f", ( $price * $coefficient[ $installment - 1 ] ) * $installment );

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
  $installments = round( $price / 5 );
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
    return $installments . 'x de ' . wc_price( $calc->price );
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
    $table .= str_repeat('<th>Parcelas</th><th>Valor</th>', $installments > 1 ? 2 : 1);
    $table .= '</tr>';
    foreach ( range(1, $installments) as $parcel ) {
      $calc = francotecnologia_wc_parcpagseg_calculate_installment( $price, $parcel );
      if ( $parcel % 2 == 1 ) {
        $table .= '<tr>';
      }      
      $table .= '<th>' . $parcel . '</th><td>' . wc_price( $calc->price ) . '</td>';
      if ( $parcel % 2 == 0 ) {
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
  echo ' <span style="color: #00ADEF; font-size: 100%" class="price">ou ' 
       . francotecnologia_wc_parcpagseg_get_parceled_value() . '</span>';
}

function francotecnologia_wc_parcpagseg_single_product() {
  $product = get_product();
  ?>
  <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
    <p class="price"><?php echo $product->get_price_html(); ?> <span style="color: #00ADEF; font-size: 75%">ou <?php echo francotecnologia_wc_parcpagseg_get_parceled_value(); ?></span></p>
    <?php echo francotecnologia_wc_parcpagseg_get_parceled_table(); ?>
    <meta itemprop="price" content="<?php echo $product->get_price(); ?>" />
    <meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
    <link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />
  </div>
  <?php
}

function francotecnologia_wc_parcpagseg_cart() {
  global $woocommerce;
  if ( $woocommerce->cart->total ) {
    $installments = francotecnologia_wc_parcpagseg_get_parceled_value( $woocommerce->cart->total );
  } else {
    $installments = "12 vezes";
  }
  ?>
  <tr><th colspan="2" style="color: #00ADEF; font-size: 100%;border-bottom: 1px solid #e8e4e3;">* Pague sua compra em at&eacute; <?php echo $installments; ?>.</th></tr>
  <?php
}

function francotecnologia_wc_parcpagseg_alter_woo_hooks() {
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
  add_action( 'woocommerce_single_product_summary', 'francotecnologia_wc_parcpagseg_single_product', 10 );
  add_action( 'woocommerce_after_shop_loop_item_title', 'francotecnologia_wc_parcpagseg_loop_item', 20 );
  add_action( 'woocommerce_cart_totals_after_order_total', 'francotecnologia_wc_parcpagseg_cart', 20 );
}

add_action('plugins_loaded','francotecnologia_wc_parcpagseg_alter_woo_hooks');
