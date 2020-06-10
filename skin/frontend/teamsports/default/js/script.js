
jQuery(document).ready(function() {
      jQuery('.js-activated').dropdownHover().dropdown();
      
      var owl = jQuery("#owl-demo");
     
    owl.owlCarousel({
    itemsCustom : [
    [0, 1],
    [450, 2],
    [600, 3],
    [700, 3],
    [1000, 4],
    [1200, 4],
    [1400, 4],
    [1600, 4]
    ],
    navigation : true
     
    });
     
    var owl = jQuery("#owl-demo2");
     
    owl.owlCarousel({
    itemsCustom : [
    [0, 1],
    [450, 2],
    [600, 3],
    [700, 3],
    [1000, 4],
    [1200, 4],
    [1400, 4],
    [1600, 4]
    ],
    navigation : true
     
    });
     
    var owl = jQuery("#owl-demo3");
     
    owl.owlCarousel({
    itemsCustom : [
    [0, 2],
    [450, 3],
    [600, 4],
    [700, 5],
    [1000, 5],
    [1200, 6],
    [1400, 6],
    [1600, 6]
    ],
    navigation : true
     
    });
    
	jQuery('.menu:last a').addClass('sale'); 
	
      var sync1 = jQuery("#sync1");
      var sync2 = jQuery("#sync2");

      sync1.owlCarousel({
        singleItem : true,
        slideSpeed : 1000,
        navigation: true,
        pagination:false,
        afterAction : syncPosition,
        responsiveRefreshRate : 200,
      });

      sync2.owlCarousel({
        slideSpeed : 1000,
        navigation: true,
        pagination:false,
        afterAction : syncPosition,
        responsiveRefreshRate : 200,
        items : 3,
        itemsDesktop      : [1199,3],
        itemsDesktopSmall     : [979,3],
        itemsTablet       : [768,2],
        itemsMobile       : [479,1],
       /* afterInit : function(el){
          el.find(".owl-item").eq(0).addClass("synced");
        }*/
      });

      function syncPosition(el){
        var current = this.currentItem;
        jQuery("#sync2")
          .find(".owl-item")
          .removeClass("synced")
          .eq(current)
          .addClass("synced")
       /* if(jQuery("#sync2").data("owlCarousel") !== undefined){
          center(current)
        }*/

      }

      jQuery("#sync2").on("click", ".owl-item", function(e){
        e.preventDefault();
        var number = jQuery(this).data("owlItem");
        sync1.trigger("owl.goTo",number);
      });

      function center(number){
        var sync2visible = sync2.data("owlCarousel").owl.visibleItems;

        var num = number;
        var found = false;
        for(var i in sync2visible){
          if(num === sync2visible[i]){
            var found = true;
          }
        }

        if(found===false){
          if(num>sync2visible[sync2visible.length-1]){
            sync2.trigger("owl.goTo", num - sync2visible.length+2)
          }else{
            if(num - 1 === -1){
              num = 0;
            }
            sync2.trigger("owl.goTo", num);
          }
        } else if(num === sync2visible[sync2visible.length-1]){
          sync2.trigger("owl.goTo", sync2visible[1])
        } else if(num === sync2visible[0]){
          sync2.trigger("owl.goTo", num-1)
        }
      }

	jQuery('.js-activated').dropdownHover().dropdown();
	
	 
	jQuery("#chk1").click(function () {
		 if (jQuery("#password").attr("type")=="password" && jQuery("#confirmation").attr("type")=="password") {
		 jQuery("#password").attr("type", "text");
		 jQuery("#confirmation").attr("type", "text");
		 }
		 else{
		 jQuery("#password").attr("type", "password");
		 jQuery("#confirmation").attr("type", "password");
		 }
		 
	 });
	 
	 /*jQuery('.step1-continue').click(function(){
	 	jQuery('.first-step').css('display','none');
	 	jQuery('.order_box .first a').removeClass('active');
	 	jQuery('.order_box .second a').addClass('active');
	 	jQuery('.second-step').css('display','block');
	 	return false;
	 });*/
	 jQuery('.order_box ul .first').click(function(){
	 	jQuery('.first-step').css('display','block');
	 	jQuery('.order_box .first a').addClass('active');
	 	jQuery('.order_box .second a').removeClass('active');
	 	jQuery('.order_box .third a').removeClass('active');
	 	jQuery('.second-step').css('display','none');
	 	jQuery('.third-step').css('display','none');
	 	return false;
	 });
	  jQuery('.order_box ul .second').click(function(){
	 	jQuery('.first-step').css('display','none');
	 	jQuery('.order_box .first a').removeClass('active');
	 	jQuery('.order_box .second a').addClass('active');
	 	jQuery('.order_box .third a').removeClass('active');
	 	jQuery('.second-step').css('display','block');
	 	jQuery('.third-step').css('display','none');
	 	return false;
	 });
	 
	 jQuery('.go-back-step2').click(function(){
	 	jQuery('.second-step').css('display','none');
	 	jQuery('.third-step').css('display','none');
	 	jQuery('.order_box .second a').removeClass('active');
	 	jQuery('.order_box .third a').removeClass('active');
	 	jQuery('.first-step').css('display','block');
	 	jQuery('.order_box .first a').addClass('active');
	 	return false;
	 });
	 jQuery('.step2-continue').click(function(){
	 	jQuery('.second-step').css('display','none');
	 	jQuery('.third-step').css('display','block');
	 	jQuery('.order_box .second a').removeClass('active');
	 	jQuery('.order_box .third a').addClass('active');
	 	jQuery('.first-step').css('display','none');
	 	jQuery('.order_box .first a').removeClass('active');
	 	return false;
	 });
	 
	 jQuery('.third-step .kit_back a').click(function(){
	 	jQuery('.first-step').css('display','none');
	 	jQuery('.third-step').css('display','none');
	 	jQuery('.order_box .first a').removeClass('active');
	 	jQuery('.order_box .third a').removeClass('active');
	 	jQuery('.second-step').css('display','block');
	 	jQuery('.order_box .second a').addClass('active');
	 	return false;
	 });
	 
	 
      var i = 1;
      var p = 1;
      var q = 1;

jQuery(".accordion-section-title").each( function() {

jQuery(this).attr("href", "#accordion-"+p);

p++;

});
jQuery(".accordion-section-content").each( function() {

jQuery(this).attr("id", "accordion-"+i);

i++;


});
jQuery('.accordion-section').each( function() {

jQuery(this).attr("id", "accordion-section-"+q);

q++;

});
	
	
	
	 
	 jQuery('.conf_child').parent().addClass('conf_child_parent');
	 jQuery('.conf_child_parent .all-input').css('display','none');
	 
	 var totalqty = 0;
   jQuery(".conf_child").keyup(function(){
   var id = jQuery(this).attr("name");
   var value = parseFloat(jQuery(this).val());

   jQuery(this).attr('value', value);
   if(value != '0'){
    jQuery(this).parent().addClass("base");

   }
   if (isNaN(value)){
    jQuery(this).parent().removeClass("base");
   }

  });
   jQuery(".header_Top_Right ul .log .login").click(function(event){
     jQuery(".log_details").fadeToggle( "slow", "linear" );
     event.preventDefault();
   });
  
/*
 jQuery("#accordion-section-4,#accordion-section-5").wrapAll("<div class='accordion-section-multiple'></div>");
 
 jQuery("#accordion-section-8,#accordion-section-9").wrapAll("<div class='accordion-section-multiple1'></div>");
 
 jQuery("#accordion-section-10,#accordion-section-11").wrapAll("<div class='accordion-section-multiple2'></div>");
  
     jQuery('.accordion-section-multiple .accordion-section-title').click(function(){
                        jQuery('#accordion-section-5 .accordion-section-content_file').css('display','block');
                    });


 jQuery('.accordion-section-multiple1 .accordion-section-title').click(function(){
                         jQuery('#accordion-section-9 .accordion-section-content_file').css('display','block');
                     });
                      jQuery('.accordion-section-multiple2 .accordion-section-title').click(function(){
                         jQuery('#accordion-section-11 .accordion-section-content_file').css('display','block');
                     });*/



    });

jQuery(window).scroll(function() {    
    var scroll = jQuery(window).scrollTop();

    if (scroll >= 100) {
       jQuery(".header_Bottm").addClass("fixed");
    } else {
        jQuery(".header_Bottm").removeClass("fixed");
    }
    
});

jQuery(document).ready(function(){
jQuery('.header_Bottm .main-wrap-desk  a.level-top').click(function(event){
  if(jQuery(this).hasClass('current')){
  jQuery(this).parent().find('ul.level0').toggle();
  }
  else{
    jQuery('.header_Bottm .main-wrap-desk  a.level-top.current').parent().find('ul.level0').hide();
    jQuery('.header_Bottm .main-wrap-desk  a.level-top.current').removeClass('current');
    jQuery(this).toggleClass('current');
    jQuery(this).parent().find('ul.level0').toggle();
  }
 event.preventDefault();


});
});