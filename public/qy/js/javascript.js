window.onload=function(){
	
	$(".in-input input").on("click",function(){
		$(this).siblings(".in-xiala").slideToggle();
	})
	
	$('.in-xiala p').on("click",function(){
		$(this).parent().siblings("input").val($(this).text());
		$(this).parent().slideUp();
	})
	
	
	$(".in-liuxiao label").on("click",function(){
		$(this).toggleClass('on').siblings().removeClass('on');
	})
	
	
	
setInterval(function(){
		$(".licen-qylist ul").animate({"margin-left":"-="+($(".licen-qylist li").width()+62)+"px"},function(){
			$(this).append($(".licen-qylist li").eq(0));
			$(this).css("margin-left","-62px")
		})
	
	
},4000)
	
}


