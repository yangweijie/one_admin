$(function(){
	$('.site-name').after('<div id="tv"><img alt="" src="/Public/css/theme/songup/images/tv.gif"></div>');
	$('#secondary').prepend($('#header .site-search'));
	$('#secondary').prepend('<div id="sidebar-topimg"><!--工具条顶部图象--></div>');
	$('#secondary').append('<div id="sidebar-bottomimg"></div>');
	$('.post-title').wrap('<div class="post-top"></div>');
	$('.post-top').prepend('<div class="CateIconSP"><div class="DateYM">11-05</div><div class="DateDay">14</div></div>');
	$('.post-top').after($(this).next('.thumb'));
	$('.post-top').each(function(i,v){
		var date = $(this).next('ul');
		date = date.find('li[title]').attr('title');
		var date_arr = date.split(',');
		$(this).find('.DateYM').text(date_arr[0]+'-'+date_arr[1]);
		$(this).find('.DateDay').text(date_arr[2]);
		$(this).next('ul').find('li[title]').remove();
	});
	$('.post').addClass('clearfix');
	$('.thumb.f_l img').attr('width', '480').error(function(){
		this.src = '/Public/css/theme/songup/images/unknow_img.jpg';
	});
	$('.thumb').each(function(){
		$(this).insertBefore($(this).parents('.post-meta'));
	});
	$('.post-meta').prepend('<li>&nbsp;&#166;<a href="javascript:scroll(0,0);">返回顶部</a></li>');
	$('.page').parent().addClass('pageContent');
})
