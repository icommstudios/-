// Clear inputs on focus

$(document).ready(function() {
    swapValue = [];
    $(".clear-val").each(function(i) {
        swapValue[i] = $(this).val();
        $(this).focus(function() {
            if ($(this).val() == swapValue[i]) {
                $(this).val("");
            }
            $(this).addClass("focus");
        }).blur(function() {
            if ($.trim($(this).val()) == "") {
                $(this).val(swapValue[i]);
                $(this).removeClass("focus");
            }
        });
    });
});

// Call jQuery UI Datepicker
$(function() {
    $("#deadline").datepicker();
});

// Show alerts
jQuery(window).load(function() {
    jQuery('#alert-wrapper').has('.alert').fadeIn('medium');
    jQuery('#alert-wrapper').delay(6000).fadeOut('medium');
});


// toggle reference-popup on click
$('.show-references').on('click', function() {
    $(this).next('div.reference-popup').slideToggle();
    return false
});

// toggle category
$('.show-subcategories').on('click', function() {

	var children = $(this).closest('ul').children('.sub-cat');
	if ( $(children).first().is(":visible") ) {
		$(this).children('.show-subcategories-indicator').html('+');
	} else {
		$(this).children('.show-subcategories-indicator').html('&ndash;');
	}
    $(this).closest('ul').children('.sub-cat').toggle(); 
    return false;
});