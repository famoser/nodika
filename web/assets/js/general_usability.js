function boot_general_usability() {
    $("form").on("submit", function () {
        var $form = $(this);
        var $btns = $(".btn", $form);
        if (!$btns.hasClass("no-disable")) {
            $btns.addClass("disabled");
        }
    })
}