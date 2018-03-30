$(function(){
	
	//index
	$(window).scroll(function(){	
		
	 if($(".banner").height()<=$(document).scrollTop()){
	 	
	 	if($('.header').css('top') =='0px' && $('.header').css('position') == 'absolute'){
	 		$('.header').css({"position":"fixed","top":"-87px",})
	 		$('.header').addClass("headerAB");
	 		$('.header').animate({top:'0px'})	
	 		$(".head-login img").attr("src","public/yz/images/head-logins.png");
	 	} else if($('.header').css('top') =='0px' && $('.header').css('position') == 'fixed') {
	 		
	 	}
	 }else{
	 
	 	if($('.header').css("position") == 'fixed'){
		 	$('.header').css({"position":"absolute","top":"0px"})
		 	$('.header').removeClass("headerAB");
		 	$(".head-login img").attr("src","public/yz/images/head-login_05.png");
	 	}
	 		
	 }
	})

    $(".xtli-ullip").bind("click",function(){
        $(this).find(".tree-selsct").slideToggle().end().siblings().find(".tree-selsct").slideUp();
    })
	
	
	$(".tan-kuan").bind("click",function(){
		$(".in-fixed").show(200);
	})
	
	$(".in-tanxuan a").eq(0).bind("click",function(){
		$(".in-fixed").hide(200);
	})
	
	$(".celan li").last().bind("click",function(){
		$("html,body").animate({"scrollTop":0});
	})
	
	
	
	///系统
	$(".whole").bind("click",function(){
		$(this).parent().addClass("xt-nav-li").siblings().removeClass("xt-nav-li backg");
		$(this).siblings().slideToggle().parent().siblings().find(".xtli-ulli").slideUp();
		if($(this).parent().attr("class").indexOf("backg")>-1){
				$(this).parent().removeClass("backg");
		}else{
			$(this).parent().addClass("backg");
		}
	})
$(".date_selector").bind("click",function(e){
			e.stopPropagation();
		})

	$(".adit-xuan p").on("click",function(){
				$(this).parent().siblings().val($(this).text());
				$(this).parent().slideUp();
			})
			
			$(".inp-xia input").on("click",function(){
				$(this).siblings().slideDown();
			})
})