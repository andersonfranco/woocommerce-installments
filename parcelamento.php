<?php
/**
 * Plugin Name: WooCommerce Parcelamento PagSeguro
 * Plugin URI: http://francotecnologia.com/
 * Description: Added the price with installments.
 * Author: Anderson Franco
 * Author URI: http://www.francotecnologia.com/
 * Version: 1.0
 * License: GPLv2 or later
 */

function francotecnologia_wc_parcpagseg_calculate_parcels( $valor = 0.00, $parcelas = 0 ) {

  $valor    = (float) $valor;
  $parcelas = (int) $parcelas;
  $retorno  = new stdClass(); 

  if ( $parcelas < 1 || $parcelas > 12 ) {
    $retorno->valor = 0;
    $retorno->total = 0;
    return $retorno;
  }

  $fator = array( 1, 0.52255, 0.35347, 
    0.26898, 0.21830, 0.18453, 
    0.16044, 0.14240, 0.12838, 
    0.11717, 0.10802, 0.10040 );

  $retorno->valor = sprintf( "%0.2f", $valor * $fator[ $parcelas - 1 ] );
  $retorno->total = sprintf( "%0.2f", ( $valor * $fator[ $parcelas - 1 ] ) * $parcelas );

  return $retorno;
}

function francotecnologia_wc_parcpagseg_get_parceled_value( $valor = null ) {
  if ( $valor === null ) {
    $product = get_product();
    if ( $product->get_price() ) {
      $valor = $product->get_price();
    }
  }
  if ( $valor > 0 ) {
    $parcelas = round( $valor / 5 );
    if ( $parcelas > 12 ) {
      $parcelas = 12;
    } else if ( $parcelas < 1 ) { 
      $parcelas = 1;
    }
    $calc = francotecnologia_wc_parcpagseg_calculate_parcels( $valor, $parcelas );
    return $parcelas . 'x de ' . wc_price( $calc->valor );
  }
}

function francotecnologia_wc_parcpagseg_get_parceled_table( $valor = null ) {
  if ( $valor === null ) {
    $product = get_product();
    if ( $product->get_price() ) {
      $valor = $product->get_price();
    }
  }
  if ( $valor > 0 ) {
    $parcelas = round( $valor / 5 );
    if ( $parcelas > 12 ) {
      $parcelas = 12;
    } else if ( $parcelas < 1 ) { 
      $parcelas = 1;
    }
    $table = '<table class="francotecnologia_wc_parcpagseg_table">';
    $table .= '<tr>';
    $table .= str_repeat('<th>Parcelas</th><th>Valor</th>', $parcelas > 1 ? 2 : 1);
    $table .= '</tr>';
    foreach ( range(1, $parcelas) as $parcel ) {
      $calc = francotecnologia_wc_parcpagseg_calculate_parcels( $valor, $parcel );
      if ( $parcel % 2 == 1 ) {
        $table .= '<tr>';
      }      
      $table .= '<th>' . $parcel . '</th><td>' . wc_price( $calc->valor ) . '</td>';
      if ( $parcel % 2 == 0 ) {
        $table .= '</tr>';
      }      
    }
    if ( substr( $table, -5 ) != '</tr>' ) {
      $table .= '</tr>';
    }    
    $table .= '</table>';
    return $table;
  }
}

function francotecnologia_wc_parcpagseg_loop_item() {
  echo ' <span style="color: #00ADEF; font-size: 100%" class="price">ou ' . francotecnologia_wc_parcpagseg_get_parceled_value() . '</span>';
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
  $valorTotal = 0;
  if ( $woocommerce->cart->total ) {
    $valorTotal = francotecnologia_wc_parcpagseg_get_parceled_value( $woocommerce->cart->total );
  } else {
    $valorTotal = "12 vezes";
  }
  ?>
  <tr><th colspan="2" style="color: #00ADEF; font-size: 100%;border-bottom: 1px solid #e8e4e3;">* Pague sua compra em at&eacute; <?php echo $valorTotal; ?>.</th></tr>
  <?php
}

add_action('plugins_loaded','alter_woo_hooks');
function alter_woo_hooks() {
  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
  add_action( 'woocommerce_single_product_summary', 'francotecnologia_wc_parcpagseg_single_product', 10 );
  add_action( 'woocommerce_after_shop_loop_item_title', 'francotecnologia_wc_parcpagseg_loop_item', 20 );
  //
  add_action( 'woocommerce_cart_totals_after_order_total', 'francotecnologia_wc_parcpagseg_cart', 20);
}

