$(function(){
	/*search*/
    $(".search").on("click",function(){
        $("#nav_left").hide(600,"linear");
        $(".nav_search").show();
        if($(window).width() > 768){
            $(this).parents(".nav_user").find(".navbar-right").css({
            "marginTop" : "-60px"
            })
        }
    })

    $(".nav_search .glyphicon-remove").on("click",function(){
        $("#nav_left").show(600,"linear");
        $(".nav_search").hide("slow");
         $(this).parents(".nav_user").find(".navbar-right").css({
            "marginTop" : "0px"
        })

    });

   
   
    



    //中英文切换
    $('.lag_change a').on("click",function(){
        var _this = $(this).find("img");
        _this.attr('src',_this.attr('src')=='images/icon_china.png'?'images/icon_cn.png':'images/icon_china.png');
    })


    
    //删除提示框
    $(".btn_delect").on("click",function(){
        layer.msg("您确定要删除吗", {
            time: 0, //不自动关闭
            btn: ["确定","取消"]
        });
    })


    
     /*导航菜单鼠标悬停时显示二级菜单

            var os = function(){
                var ua = navigator.userAgent,
                isWindowsPhone = /(?:WindowsPhone)/.test(ua),
                isSymbian = /(?:SymbianOS)/.test(ua) || isWindowsPhone,
                isAndroid = /(?:Android)/.test(ua),
                isFireFox = /(?:FireFox)/.test(ua),
                isChrome = /(?:Chrome|CriOS)/.test(ua),
                isTablet = /(?:iPad|PlayBook)/.test(ua) || 
                (isAndroid && !/(?:Mobile)/.test(ua)) || 
                (isFireFox && /(?:Tablet)/.test(ua)),
                isPhone = /(?:iPhone)/.test(ua) && !isTablet,
                isPc = !isPhone && !isAndroid && !isSymbian;

                return{
                    isTablet : isTablet,
                    isPhone : isPhone,
                    isAndroid : isAndroid,
                    isPc : isPc
                };
            }();

           
            function MaxWidthHandler(){
                $(".dropdown-toggle").on("mouseover",function(){
                    $(this).parents(".nav li").addClass("active");
                    $(this).next().show();

                })
                $(".dropdown-menu").on("mouseover",function(){
                    $(this).parents(".nav li").addClass("active");
                    $(this).show();
                })
               $(".dropdown-toggle").on("mouseout",function(){
                   $(this).next().hide();
                    $(this).parents(".nav li").removeClass("active");
                })
                $(".dropdown-menu").on("mouseout",function(){
                    $(this).hide();
                    $(this).parents(".nav li").removeClass("active");
                })
            }
          

      
            function MinWidthHandler(){
                var thisParent = $(this).parents(".nav li");
                $(".dropdown-toggle").on("click",function(){
                    if(thisParent.hasClass("active")){
                        $(this).parents(".nav li").addClass("active");
                        $(this).show();
                    }else{
                        $(this).parents(".nav li").removeClass("active");
                        $(this).show();
                    }
                })
            }
           
            if(!os.isAndroid || !os.isPhone){
                $(window).on("resize",function(){
                   if($(window).width() <= 768){
                         MinWidthHandler();
                    }
                })

                $(window).on("resize",function(){
                    if($(window).width() > 768){
                        MaxWidthHandler();
                    }
                })
            }
            window.onload=function(){
                MaxWidthHandler();
            }
        */    

})