jQuery(document).ready(function($) {
    var class_name;
    $(".liSelection").on('click',function(){
        
        $('.liSelection').removeClass('selected');
        $(this).addClass('selected');
        
    })
	$('body').on('change', '.wcod_wrapper_div select[multiple="multiple"]', function(){
		
		if($(this).find('option').eq(0).is(':selected')){
			$(this).find('option:selected').prop("selected", false);
			$(this).find('option').eq(0).prop("selected", true);
		}
	});

    $(".wcod_order_items .li-Item").on('click',function(){
        $(this).toggleClass('default');
		var selected = $('.wcod_order_groups li.liSelection.selected');
		if(selected.length==0){
		   selected = $('.wcod_order_groups li.liSelection').eq(0);
		   selected.addClass('selected');		   
		}
		
		class_name = ($(this).hasClass('default')?'default':selected.data('class'));
		var id = $(this).data('id');
		
		$(this).find('input[type="hidden"]').attr('name','grouping['+class_name+']['+id+']');
		
		if($(this).hasClass('default')){
			$(this).attr('class', 'list-group-item li-Item default');
		}else{
			$(this).attr('class', 'list-group-item li-Item flagged '+class_name);
		}
		
    })

	$('body').on('click', '.wcod_saved_colors a', function(event){
		event.preventDefault();
		var txt;
		var r = confirm("Do you want to delete this label?");
		if (r == true) {
			document.location.href = $(this).attr('href');
		} else {
			return false;
		} 
	});

    

    $("body").on("click",".add_class", function () {
        
        var row_add = '<div class="row">';

        row_add +='<div class="form-group col-md-3"><input type="text" class="form-control" name="class_name[]"></div>';
        row_add +='<div class="form-group col-md-3"><input type="color" class="form-control" name="background_color[]"></div>'; 
        row_add +='<div class="form-group col-md-3"><input type="color" class="form-control" name="color[]" id="txt_color"></div>';         
        row_add += '<div class="form-group col-md-2"><div class="btn btn-success btn-sm add_class">+</div> <div class="btn btn-danger btn-sm del_class">-&nbsp;</div></div>';
        row_add += '</div>'
        //newRow.append(cols);
        //$(":submit").before(row_add);
		$(this).parent().parent().after(row_add);
        counter++;
    });

    $("body").on("click",".del_class" , function () {
        
        $(this).parent().parent().remove();
        
    });
       
    
	$('.wcod_wrapper_div a.nav-tab').click(function(){
		$(this).siblings().removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.nav-tab-content').hide();
		$('.nav-tab-content').eq($(this).index()).show();
		window.history.replaceState('', '', wcod_obj.this_url+'&t='+$(this).index());
		$('form input[name="jps_tn"]').val($(this).index());
		wcod_obj.wcod_tab = $(this).index();
		$('.wrap.jps-wrapper').attr('class', function(i, c){
    		var cstr = c.replace(/(^|\s)tab-\S+/g, '');
			console.log(cstr);
			cstr += ' tab-'+wcod_obj.wcod_tab;
			console.log(cstr);
			return cstr;
		});
	});  
});
function wcod_removeParam(key) {
    var url = document.location.href;
    var params = url.split('?');
    if (params.length == 1) return;

    url = params[0] + '?';
    params = params[1];
    params = params.split('&');

    $.each(params, function (index, value) {
        var v = value.split('=');
        if (v[0] != key) url += value + '&';
    });

    url = url.replace(/&$/, '');
    url = url.replace(/\?$/, '');

    document.location.href = url;
}