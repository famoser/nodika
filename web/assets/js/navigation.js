function boot_navigation() {
    $('#menu-toggle').click(function () {
        var menu = $(".menu-content");
        menu.toggleClass('open');
        $(".menu-toggle").toggleClass('open');
        $(".nav-icon").toggleClass('open');

        if ($(".help-content").hasClass("open") && menu.hasClass("open")) {
            clickHelpToggle();
        }
    });
    var clickHelpToggle = function () {
        $(".help-content").toggleClass('open');
        $(".help-toggle").toggleClass('open');
    };
    $('#help-toggle').click(clickHelpToggle);

}