(function () {
  "use strict";

  function openDesign(e) {
    var src = jQuery(e).data("url");

    if (src) {
      jQuery("#pc-designtool-box").addClass("active");
      var iframe = jQuery("#pc-designtool-iframe");
      iframe.attr("src", src);
    }

    jQuery(".pc-close-iframe").on("click", function () {
      jQuery("#pc-designtool-box").removeClass("active");
    });
  }
  jQuery(document).ready(function ($) {
    $(".pc-connect-dashboard").on("click", function (e) {
      var url = $(this).data("url");
      var text = $(this).html();

      $.ajax({
        type: "post",
        dataType: "json",
        url: pc_admin.url,
        data: {
          action: "printcart_generate_key",
        },
        context: this,
        beforeSend: function () {
          $(this).html("Generating key...");
          $(this).attr("disabled", "disabled");
        },
        success: function (response) {
          $(this).attr("disabled", false);
          $(this).html(text);
          if (response.success && response.data) {
            if (response.data.consumer_key && response.data.consumer_secret) {
              var parma =
                "&consumer_key=" +
                response.data.consumer_key +
                "&consumer_secret=" +
                response.data.consumer_secret;
              url += parma;
              window.open(url, "_blank");
            } else {
              $(this).attr("disabled", false);
              $(this).html("Try again");
            }
          }
        },
        error: function (error) {
          $(this).attr("disabled", false);
          $(this).html("Try again");
        },
      });
    });
    jQuery(".printcart-w2p-button-check-connection").on("click", function () {
      var text = jQuery(this).html();
      var sid = jQuery("input[name='printcart_sid']").val();
      var secret = jQuery("input[name='printcart_secret']").val();
      var result_check = jQuery(".printcart-w2p-result-check");
      result_check.html("");

      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: pc_admin.url,
        data: {
          action: "printcart_w2p_check_connection_dashboard",
          sid: sid,
          secret: secret,
        },
        context: this,
        beforeSend: function () {
          jQuery(this).html("Checking...");
          jQuery(this).attr("disabled", "disabled");
        },
        success: function (response) {
          jQuery(this).attr("disabled", false);
          jQuery(this).html(text);
          console.log(response);
          if (
            response.success &&
            response.data.connected &&
            response.data.unauth_token
          ) {
            result_check.html(
              '<div style="color: #0f631e"><b>Connected.</b></div>'
            );
            jQuery("input[name='printcart_unauth_token']").val(
              response.data.unauth_token
            );
          } else {
            result_check.html('<div style="color: #f11"><b>Error.</b></div>');
            jQuery("input[name='printcart_unauth_token']").val("");
          }
        },
        error: function (error) {
          jQuery(this).attr("disabled", false);
          jQuery(this).html("Try again");
        },
      });
    });
  });
})();
