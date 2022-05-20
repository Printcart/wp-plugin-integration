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
          '<td><div class="design-thumbail" style="border: 1px solid #ddd;margin: 0 5px 5px 0;display: inline-block;text-align: center; vertical-align: top; background: #ddd; height: 100px; width: 100px"><img src="' +
          design.design_image.url +
          '"></div><input id="design-id" type="hidden" name="printcart_options_design[' +
          index +
          '][id]" value="' +
          design.id +
          '"><input id="design-preview" type="hidden" name="printcart_options_design[' +
          index +
          '][preview]" value="' +
          design.design_image.url +
          '"></td>';
      });
      html += "</tr></tbody></table>";
      document.getElementById("printcart-options-design").innerHTML = html;
    }
  },
  false
);
