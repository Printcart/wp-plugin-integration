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
})();
