window.onload=function(){
		if($(window).width() <= 750) {
			document.documentElement.style.fontSize = document.documentElement.clientWidth / 7.5 + 'px';
		} else {
			document.documentElement.style.fontSize = 100 + 'px';
		}
	
		$(window).resize(function() {
			if($(window).width() <= 750) {
				document.documentElement.style.fontSize = document.documentElement.clientWidth / 7.5 + 'px';
			} else {
				document.documentElement.style.fontSize = 100 + 'px';
			}
		})
}
		
$(function(){
	
	
	$(".div-fabu input").on("click",function(){
		$(this).siblings(".div-fabux").slideToggle();
	})
	$(".div-fabux p").on("click",function(){
		$(this).parent().siblings("input").val($(this).text());
		$(this).parent().slideUp();
	})
	
	$(".head-click").on("click",function(){
		$(".head-nav").slideDown();
	})
	
	$(".guanbi").on("click",function(){
		$(".head-nav").slideUp();
	})
	
})

