function boot_general_usability() {
    $("form").on("submit", function () {
        var $form = $(this);
        var $btns = $(".btn", $form);
        $btns.addClass("disabled");
    })
}