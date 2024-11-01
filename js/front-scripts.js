jQuery(document).ready(function($) {
  
	$('#filter_orders').on('keyup', function(){
		var text = $.trim($(this).val());
		
		if(text!=''){						
		
			var divs = $("ul.orders_list li");
			for (var i = 0; i < divs.length; i++) {
			  //console.log(divs[i]);
			  var para = $(divs[i]).find('a').html();
			  if(typeof para != 'undefined'){
				  var index = para.toLowerCase().indexOf(text.toLowerCase());
				  targetId = divs[i].id;
				  if (index != -1) {					 
					 $('#'+targetId).show();
					 
				  }else{
					 $('#'+targetId).hide();
					 
				  }
			  }
			}  
					
		}else{
			$("ul.orders_list li").show();
		}
		
	});  
	
	$('.cards-wrapper div.card-header').on('click', function(){
		$(this).parent().find('.card-collapse').toggleClass('collapse');
	});
	  
});