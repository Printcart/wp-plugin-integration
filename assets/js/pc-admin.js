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
              window.location = url;
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
  });
})();
