(function () {
  "use strict";
  var printcartDesigner = new PrintcartDesigner();
  var designTool;
  var orderUpload;
  jQuery(document).ready(function ($) {
    $("#pc-select_btn_design").on("click", function (e) {
      e.preventDefault();
      if ($(this).hasClass("pc-disabled")) return;
      var productId = $(this).data("productid");
      if (!productId) {
        alert("The product has not been enabled Design Tool.");
        return;
      }
      closePopup();
      designTool = printcartDesigner.initDesignTool({
        token: pc_frontend.unauth_token,
        productId: productId,
        options: {
          showRuler: pc_frontend.options.showRuler,
          showGrid: pc_frontend.options.showGrid,
          showBleedLine: pc_frontend.options.showBleedLine,
          showDimensions: pc_frontend.options.showDimensions,
        },
      });
      designTool.render();
    });

    $("#pc-select_btn_upload").on("click", function (e) {
      e.preventDefault();
      if ($(this).hasClass("pc-disabled")) return;
      var productId = $(this).data("productid");
      if (!productId) {
        alert("The product has not been enabled Uploader.");
        return;
      }
      closePopup();
      orderUpload = printcartDesigner.initUploader({
        token: pc_frontend.unauth_token,
        productId: productId,
      });
      orderUpload.open();
    });

    function closePopup() {
      $("#pc-select_wrap").removeClass("is-visible");
    }

    $("#pc-select_close-btn").on("click", function () {
      closePopup();
    });

    $("#pc-content-overlay").on("click", function (e) {
      if (e.target.id == "pc-content-overlay") {
        closePopup();
      }
    });

    $("#pc-select_btn_upload-and-design").on("click", function (e) {
      if ($(this).hasClass("pc-disabled")) return;
      e.preventDefault();
      jQuery("#pc-select_wrap").addClass("is-visible");
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
          var imageUrl = design.preview_image.url + "?t=" + Date.now();
          html +=
            '<td><div class="printcart-design-thumbail"><img src="' +
            imageUrl +
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
        designTool.close();
      }
      if (event.data && event.data.uploaderEvent === "upload-success") {
        var designUpload = event.data.data;
        var html = "";
        if (!designUpload || designUpload.length <= 0) return;

        html += "<div><b>Preview order upload</b></div><table><tbody><tr>";
        designUpload.forEach(function (uploadFile, index) {
          var designImage = uploadFile.data;
          var imageUrl = designImage.design_image.url + "?t=" + Date.now();
          html +=
            '<td><div class="printcart-design-thumbail"><img src="' +
            imageUrl +
            '"></div><input id="upload-design-id" type="hidden" name="printcart_options_design_upload[' +
            index +
            '][id]" value="' +
            designImage.id +
            '"><input id="design-preview" type="hidden" name="printcart_options_design_upload[' +
            index +
            '][preview]" value="' +
            designImage.design_image.url +
            '"></td>';
        });

        html += "</tr></tbody></table>";

        document.getElementById("printcart-options-design-upload").innerHTML =
          html;
        orderUpload.close();
      }
    },
    false
  );
})();
