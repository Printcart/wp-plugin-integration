(function () {
  jQuery(".variations_form").on("show_variation", function () {
    var variation_id = jQuery(".variations_form .variation_id").val();

    if (variation_id) {
      printcart_get_integration_id(variation_id);
    }
  });

  function printcart_trigger_button_design(disabled = false) {
    if (disabled) {
      jQuery("#pcdesigntool-design-btn").prop("disabled", true);
      jQuery("body").append(
        '<div class="printcart-button-design-loading"><i class="fa fa-spinner fa-spin"></i>Loading</div>'
      );
    } else {
      jQuery("#pcdesigntool-design-btn").prop("disabled", false);
      jQuery("#pcdesigntool-design-btn").html("Start Design");
    }
  }

  function printcart_get_integration_id(variation_id) {
    jQuery.ajax({
      type: "post",
      dataType: "json",
      url: pc_frontend.url,
      data: {
        action: "printcart_get_product_integration_by_variation",
        variation_id: variation_id,
      },
      context: this,
      beforeSend: function () {
        printcart_trigger_button_design(true);
      },
      success: function (response) {
        if (response.success && response.data) {
          jQuery("#printcart-design-tool-sdk-wrap").append(response.data);
          printcart_trigger_button_design();
        } else {
          jQuery("#printcart-design-tool-sdk").remove();
          jQuery("body .printcart-button-design-loading").remove();
        }
      },
    });
  }
})();
