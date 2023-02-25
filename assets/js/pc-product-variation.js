(function () {
  "use strict";

  var buttonHtml = jQuery(".printcart-button-design");
  var buttonLabel = buttonHtml.html();
  jQuery(".variations_form").on("show_variation", function () {
    var variation_id = jQuery(".variations_form .variation_id").val();
    if (variation_id) {
      printcart_get_integration_id(variation_id);
    } else {
      buttonHtml.attr("data-productid", "");
      buttonHtml.prop("disabled", true);
    }
  });
  function printcart_trigger_button_design(disabled = false) {
    if (disabled) {
      buttonHtml.prop("disabled", true);
      buttonHtml.html('<i class="fa fa-spinner fa-spin"></i>Loading');
    } else {
      buttonHtml.prop("disabled", false);
      buttonHtml.html(buttonLabel);
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
        if (
          response.success &&
          response.data &&
          response.data.product_id &&
          response.data.enable_design
        ) {
          buttonHtml.attr("data-productid", response.data.product_id);
          buttonHtml.data("productid", response.data.product_id);
          printcart_trigger_button_design();
        } else {
          buttonHtml.attr("data-productid", "");
          buttonHtml.data("productid", "");
          buttonHtml.html(buttonLabel);
          buttonHtml.prop("disabled", true);
        }
      },
    });
  }
})();
