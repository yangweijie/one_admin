$(function(){
	$('.post-meta img[src=""]').parent().remove();
	$('#main .post-meta .thumb').parent().addClass('index').next('.post-content').remove();
	$('#main .post-meta li:first').css('border', 0);
	//封面默认
	$('.thumb img').error(function(){
		this.src = no_pic;
	});
})
