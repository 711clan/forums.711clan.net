// JavaScript Document
$(document).ready(function() {

	animateOpacityIn();
	$(".clickOut").hide();
	$(".clickText").click(function() {
		$(".clickOut:hidden").show("slow");
	});
	$(".clickOut").click(function() {
		$(".clickOut").hide("slow");
	});
	
    $(".steveSecret").hide();
	$(".steveSecretClick").click(function() {
		$(".steveSecret:visible").hide("slow");
	});
	$(".steveSecretClick").click(function() {
		$(".steveSecret:hidden").show("slow");
	});
	
	
});

function animateOpacityOut() {
	$(".steveHax").animate({
			opacity: 0,
            fontSize: "0px",
		}, 1500, function(){animateOpacityIn();})
		
	/*$("#vbshout").animate({
			height: "0px"
		}, 1500, function(){animateOpacityIn();})*/
};

function animateOpacityIn() {
	$(".steveHax").animate({
			opacity: 1,
            fontSize: "80px",		
		}, 1500, function(){animateOpacityOut();})
		
	/*$("#vbshout").animate({
			height: "500px"	
		}, 1500, function(){animateOpacityOut();})*/
};