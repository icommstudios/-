// Clear inputs on focus

$(document).ready(function () {
    swapValue = [];
    $(".clear-val").each(function (i) {
        swapValue[i] = $(this).val();
        $(this).focus(function () {
            if ($(this).val() == swapValue[i]) {
                $(this).val("");
            }
            $(this).addClass("focus");
        }).blur(function () {
            if ($.trim($(this).val()) == "") {
                $(this).val(swapValue[i]);
                $(this).removeClass("focus");
            }
        });
    });
});