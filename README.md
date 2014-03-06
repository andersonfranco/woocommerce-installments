# WooCommerce Installments #
**Contributors:** Anderson Franco  
**Tags:** woocommerce, pagseguro, paypal, payment, parcelamento, installments  
**Tested with:** WordPress 3.8.1 and WooCommerce 2.1.3  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl.html  

This plugin appends installments into the product price.

Was created to be used with PagSeguro, PayPal and similars.

## English ##

### Description ###

This plugin appends installments into the product price

### Installation ###

* Upload the entire 'woocommerce-installments' folder to the '/wp-content/plugins/' directory
* Activate the plugin through the 'Plugins' menu in WordPress

## Portuguese ##

### Descri&ccedil;&atilde;o ###

Este plugin adiciona ao preço do produto opções de parcelamento.

Criado inicialmente para ser exibir as opções de parcelamento do PagSeguro. No entanto, pode ser customizado para qualquer outro serviço, bastando apenas editar a tabela de cálculo (fator) e o valor da parcela mínima.

### Instala&ccedil;&atilde;o ###

* Envie toda a pasta 'woocommerce-installments' para a pasta '/wp-content/plugins/'
* Ative o plugin através do menu Plugins no WordPress

## Screenshots ##

### 1. Catalog page. ###
![1. Catalog page.](http://andersonfranco.github.io/images/woocommerce-installments/catalog.png)

### 2. Product page. ###
![2. Product page.](http://andersonfranco.github.io/images/woocommerce-installments/product.png)

### 3. Cart page. ###
![3. Cart page.](http://andersonfranco.github.io/images/woocommerce-installments/cart.png)

## FAQ ##

### Sample of CSS to show the price like the screenshots ###

  ul.products li.product .price {  
    display: inline;  
  }  

  .francotecnologia_wc_parcpagseg_table th,  
  .francotecnologia_wc_parcpagseg_table td {  
    padding: 0;  
    border-bottom: 1px solid #e8e4e3;  
  }  

## Changelog ##

### 1.0.0 ###

* Initial Release
