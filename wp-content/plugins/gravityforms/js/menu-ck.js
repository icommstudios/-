/*
Copyright 2008 by Marco van Hylckama Vlieg
web: http://www.i-marco.nl/weblog/
email: marco@i-marco.nl
Free for use
*/function initMenus(){jQuery("ul.menu ul").hide();jQuery.each(jQuery("ul.menu"),function(){jQuery("#"+this.id+".expandfirst ul:first").show()});jQuery("ul.menu li .button-title-link").click(function(){var e=jQuery(this).next(),t=this.parentNode.parentNode.id;if(jQuery("#"+t).hasClass("noaccordion")){jQuery(this).next().slideToggle("normal");return!1}if(e.is("ul")&&e.is(":visible")){jQuery("#"+t).hasClass("collapsible")&&jQuery("#"+t+" ul:visible").slideUp("normal",function(){jQuery(this).prev().removeClass("gf_button_title_active")});return!1}if(e.is("ul")&&!e.is(":visible")){jQuery("#"+t+" ul:visible").slideUp("normal",function(){jQuery(this).prev().removeClass("gf_button_title_active")});e.slideDown("normal",function(){jQuery(this).prev().addClass("gf_button_title_active")});return!1}})}jQuery(document).ready(function(){initMenus()});