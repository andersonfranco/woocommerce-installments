jQuery(document).ready(function() {
  jQuery( '.variations_form .variations select' ).on("change", function() {
    setTimeout(function(){
      jQuery('.francotecnologia_wc_parcpagseg_table').hide();
      jQuery('.francotecnologia_wc_parcpagseg_table_with_variation_id_' + jQuery('input[name=variation_id]').val() ).show();
    }, 100);
  });
});
