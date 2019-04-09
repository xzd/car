/**
 * Created by Hoodps on 2017/8/10.
 */
$(function(){
    //垂直导航栏，点击下拉子菜单
    $(".main>a").click(function(){
        $(this).next().slideToggle(500)
            .parent().siblings().find(".slider-list").slideUp(500);
        $(this).parent().addClass('active').siblings().removeClass('active');
    });
    $('.slider-list-li').click(function(){
        $(this).addClass('current').siblings().removeClass('current');
    });
    $('.current-arrow>a').click(function(){
        $('.slider-list-item').slideToggle(500);
        $(this).parent().toggleClass('arr');
    });
    $('.slider-list-item li').click(function(){
        $(this).addClass('curr').siblings().removeClass('curr');
    });
    //弹出禁封提示
    $('.btn-forbidden').click(function(){
        $('.dialog,.mask').show();
    });
    $('.confirm,.cancel').click(function(){
        $('.dialog,.mask').hide();
    });
    $('.cancelBtn,.cancel-b').click(function(){
       $('.dialog,.mask-layer').hide();
    });

    //切换页码
    $('.page span').click(function(){
        $(this).addClass('curren-page').siblings().removeClass('curren-page');
    });
    //弹出货源详情
    // $('tbody tr').click(function(){
    //     $('.dialog,.mask-layer').show();
    // });
    //功能开关切换
    $('.switch').click(function(){
        $(this).toggleClass('switch-open').toggleClass('switch-close');
        var start = $('#start').val();
        if (start == 1) {
            $('#start').val(0);
            $('.status').text('关闭状态');
        } else {
            $('#start').val(1);
            $('.status').text('开启状态');
        }
    });


    /*图片*/
    $('.imgJs').each(function() {
        var imgHeight = $(this).height(),
            aHeight = $(this).parent().height();
        if(imgHeight<aHeight){
            $(this).css({'height':aHeight,'width':'auto'})
        }
    });

    /*图片显示大图*/
    $('.deImg').each(function() {
        $(this).click(function(){
            $('body').css({'height':'100%','overflow':'hidden'});
            $(this).parents('.mask-layer').find('.imgBg').fadeIn(500);
            var src = $(this).attr('src');
            $('.showImg').attr('src',src);
            // var winHeight = $(window).height();
            // var imgHeight = $('.showImg').height();
            // if(imgHeight<winHeight){
            //     var curHeight = winHeight -imgHeight;
            //     var radHeight = curHeight /2;
            //     $('.imgBgMain').css('margin-top',radHeight);
            // }
        });
        $('.imgBg').click(function(){
            $('body').removeAttr('style');
            $(this).fadeOut(500);
        });
    });
});