(function () {
  "use strict";
  var designer;
  jQuery(document).ready(function ($) {
    $(".printcart-button-design").on("click", function (e) {
      e.preventDefault();
      var productId = $(this).data("productid");
      if (!productId) {
        alert("The product has not been enabled Design Tool.");
      }
      designer = new PrintcartDesigner({
        token: pc_frontend.unauth_token,
        productId: productId,
        options: {
          showRuler: pc_frontend.options.showRuler,
          showGrid: pc_frontend.options.showGrid,
          showBleedLine: pc_frontend.options.showBleedLine,
          showDimensions: pc_frontend.options.showDimensions,
        },
      });
      designer.render();
    });

    window.addEventListener(
      "message",
      function (event) {
        if (
          event.data &&
          event.data.message === "closeDesignTool" &&
          event.data.closeDesignTool
        ) {
          designer.close();
        }
      },
      false
    );
  });

  window.addEventListener(
    "message",
    function (event) {
      if (event.data && event.data.message === "finishProcess") {
        var designs = event.data.data.data;
        var html = "";
        if (!designs || designs.length <= 0) return;

        html += "<div><b>Preview designs</b></div><table><tbody><tr>";

        designs.forEach(function (design, index) {
          html +=
            '<td><div class="printcart-design-thumbail"><img src="' +
            design.preview_image.url +
            '"></div><input id="design-id" type="hidden" name="printcart_options_design[' +
            index +
            '][id]" value="' +
            design.id +
            '"><input id="design-preview" type="hidden" name="printcart_options_design[' +
            index +
            '][preview]" value="' +
            design.preview_image.url +
            '"></td>';
        });

        html += "</tr></tbody></table>";

        document.getElementById("printcart-options-design").innerHTML = html;
        designer.close();
      }
    },
    false
  );
})();
