(function () {
  window.addEventListener(
    "message",
    function (event) {
      if (event.data && event.data.message === "finishProcess") {
        var designs = event.data.data.data;
        var html    = "";
        if (!designs || designs.length <= 0) return;

        html += "<div><b>Preview designs</b></div><table><tbody><tr>";

        designs.forEach(function (design, index) {
          html +=
            '<td><div class="printcart-design-thumbail"><img src="'
            + design.design_image.url
            + '"></div><input id="design-id" type="hidden" name="printcart_options_design['
            + index + '][id]" value="' + design.id
            + '"><input id="design-preview" type="hidden" name="printcart_options_design['
            + index + '][preview]" value="' + design.design_image.url+'"></td>';
        });

        html += "</tr></tbody></table>";

        document.getElementById("printcart-options-design").innerHTML = html;
      }
    },
    false
  );
})();
