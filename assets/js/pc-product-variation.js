(function () {
  "use strict";

  var buttonDesignDom = jQuery("#pc-select_btn_design");
  var buttonUploadDom = jQuery("#pc-select_btn_upload");
  var buttonUploadAndDesignDom = jQuery("#pc-select_btn_upload-and-design");
  var buttonDesignLabel = buttonDesignDom.html();
  var buttonUploadLabel = buttonUploadDom.html();
  var buttonUploadAndDesignLabel = buttonUploadAndDesignDom.html();
  function toggleLoading(loading = true) {
    if (loading) {
      buttonDesignDom.addClass("pc-disabled");
      buttonDesignDom.html("Loading...");
      buttonUploadDom.addClass("pc-disabled");
      buttonUploadDom.html("Loading...");
      buttonUploadAndDesignDom.addClass("pc-disabled");
      buttonUploadAndDesignDom.html("Loading...");
    } else {
      buttonDesignDom.removeClass("pc-disabled");
      buttonDesignDom.html(buttonDesignLabel);
      buttonUploadDom.removeClass("pc-disabled");
      buttonUploadDom.html(buttonUploadLabel);
      buttonUploadAndDesignDom.removeClass("pc-disabled");
      buttonUploadAndDesignDom.html(buttonUploadAndDesignLabel);
    }
  }
  jQuery(".variations_form").on("show_variation", function () {
    var variation_id = jQuery('form input[name="variation_id"]').val();
    if (variation_id) {
      printcart_get_integration_id(variation_id);
    } else {
      buttonDesignDom.attr("data-productid", "");
      buttonUploadDom.attr("data-productid", "");
      // toggleLoading(false);
      buttonDesignDom.addClass("pc-disabled");
      buttonUploadDom.addClass("pc-disabled");
      buttonUploadAndDesignDom.addClass("pc-disabled");
    }
  });
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
        toggleLoading();
      },
      success: function (response) {
        toggleLoading(false);
        if (response.success && response.data && response.data.product_id) {
          if (response.data.enable_design) {
            buttonDesignDom.attr("data-productid", response.data.product_id);
          } else {
            buttonDesignDom.attr("data-productid", "");
            buttonDesignDom.addClass("pc-disabled");
          }
          if (response.data.enable_upload) {
            buttonUploadDom.attr("data-productid", response.data.product_id);
          } else {
            buttonUploadDom.attr("data-productid", "");
            buttonUploadDom.addClass("pc-disabled");
          }
        }
      },
    });
  }
})();
