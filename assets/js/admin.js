jQuery(document).ready(function ($) {
  // Handle audit button click
  $(".kuraai-run-audit").on("click", function (e) {
    e.preventDefault();

    var button = $(this);
    button.prop("disabled", true).text("Running Audit...");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "kuraai_run_audit",
        nonce: button.data("nonce"),
      },
      success: function (response) {
        if (response.success) {
          $(".kuraai-audit-results").html("");

          $.each(response.data, function (index, item) {
            if (item.type === "suggestion") {
              $(".kuraai-audit-results").append(
                '<div class="suggestion"><p>' + item.content + "</p></div>"
              );
            } else if (item.type === "cta") {
              $(".kuraai-audit-results").append(
                '<div class="notice notice-info"><p>' +
                  item.content +
                  "</p></div>"
              );
            }
          });
        } else {
          alert("Error: " + response.data);
        }
      },
      complete: function () {
        button.prop("disabled", false).text("Run Store Audit");
      },
    });
  });

  // Handle competitor audit button click
  $(".kuraai-run-competitor-audit").on("click", function (e) {
    e.preventDefault();

    var button = $(this);
    var competitorUrl = $("#kuraai-competitor-url").val();

    if (!competitorUrl) {
      alert("Please enter a competitor URL");
      return;
    }

    button.prop("disabled", true).text("Analyzing...");

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "kuraai_run_competitor_audit",
        nonce: button.data("nonce"),
        competitor_url: competitorUrl,
      },
      success: function (response) {
        if (response.success) {
          $(".kuraai-competitor-results").html("");

          $.each(response.data, function (index, item) {
            if (item.type === "suggestion") {
              $(".kuraai-competitor-results").append(
                "<li>" + item.content + "</li>"
              );
            } else if (item.type === "cta") {
              $(".kuraai-competitor-results").append(
                '<div class="notice notice-info"><p>' +
                  item.content +
                  "</p></div>"
              );
            }
          });
        } else {
          alert("Error: " + response.data);
        }
      },
      complete: function () {
        button.prop("disabled", false).text("Analyze Competitor");
      },
    });
  });
});
