$(function(){
	$('#logo').before('<div class="arrow"></div>');
	$('#header').before('<div id="left" class="ption_f"><p class="left_line_y"></p></div>');
	$('#header').before('<div id="right" class="ption_f"><p class="right_line_y"></p></div>');
	$('#main').prepend('<div class="left_top_bg"></div>');
	$('#main .post-meta .thumb').parent().addClass('index').next('.post-content').remove();
	$('#secondary').prepend('<div class="right_top_bg"></div>');
	// tag
	$(".widget-tag li a").css({'opacity':'.6'})
	$(".widget-tag li a").hover(
		function() {
		   $(this).stop().fadeTo(300, 1);
		},
		function() {
		   $(this).stop().fadeTo(300, .6);
		}
	);

})
