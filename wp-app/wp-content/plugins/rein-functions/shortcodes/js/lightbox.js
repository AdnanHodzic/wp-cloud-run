
//@prepros-prepend select2.min.js

(function($){

	/* Dom Loaded */
	$(document).ready(function($){

        // Tabs
        $('.sidebar ul li a').on('click', function(){
            var current_tab = $(this).parent('li').data('tab');
			$('input#ep-shortcodes-form-type').val(current_tab);
			$('.tabs-container .tab').hide();
			$('.tabs-container #'+current_tab).fadeIn('fast');
			$('.sidebar li.active').removeClass('active');
			$(this).parent('li').addClass('active');
			return false;
        });
        
        $(".select2.icon").select2({
			formatResult: format_icon,
			formatSelection: format_icon,
			allowClear: true,
			width: '80%',

		}); 

        // Columns shortcode
		var num_of_columns = 2;
		$('.column-structures a').on('click', function() {
			$('.column-structures a').removeClass('active');
            $(this).addClass('active');
            var split = $(this).attr('split').split('|');
			$('.column-structures input').val( $(this).attr('split') );
			num_of_columns = $(this).attr('split');
			num_of_columns = num_of_columns.split('|');
			num_of_columns = num_of_columns.length;
			$('#columns textarea').attr('disabled', true);
			var i = -1;
			while(i < (num_of_columns - 1)) {
				i++;
				$('#columns textarea').eq(i).attr('disabled', false).next().val( split[i] );
			}
			
			return false;
        });
        
        /* Submit tab */
  

        $('#insert-button').on('click', function(e){

            $('.tab:visible form input[type=submit]').click();
          
            e.preventDefault();
        });

        $('form').submit(function(e){

            generate_shortcode();

            var shortcode_content = $('textarea#shortcode-content').val();

            // alert(shortcode_content);
            if( shortcode_content != ''){
                epcl_insert_shortcode(shortcode_content);
            }
            
            return false;
        });

        function generate_shortcode(){
            var current_tab = $('.tab:visible');
            var is_array = false;
            var id = current_tab.attr('id');
            if( current_tab.attr('id') == 'accordions' || current_tab.attr('id') == 'columns' || current_tab.attr('id') == 'tabs' ){
                is_array = true;
                var values = current_tab.find('form').serializeControls(); 
            }else{
                var values = current_tab.find('form').serializeArray(); 
            }

            var epcl_shortcode = '[epcl_' + current_tab.attr('id');
            var content = '';

            if( is_array ){
                var epcl_shortcode = '[epcl_' + current_tab.attr('id')+']';
                $.each(values.item, function (index, field) {
                    if( field.width && field.content && id == 'columns'){
                        epcl_shortcode += '<p>[epcl_'+ current_tab.data('item') +' width="' + field.width + '"] '+field.content+' [/epcl_'+ current_tab.data('item') +']</p>';   
                    }else if( (field.title || field.content) && id == 'tabs'  ){
                        epcl_shortcode += '<p>[epcl_'+ current_tab.data('item') +' title="' + field.title + '"] '+field.content+' [/epcl_'+ current_tab.data('item') +']</p>';    
                    }else if( field.title || field.content ){
                        epcl_shortcode += '<p>[epcl_'+ current_tab.data('item') +' custom_class="' + field.custom_class + '" title="' + field.title + '"] '+field.content+' [/epcl_'+ current_tab.data('item') +']</p>';    
                    }                         
                });
            }else{
                $.each(values, function (indexInArray, field) {          
                    if(field.name == 'content'){
                        content = field.value;
                    }else{
                        epcl_shortcode += ' ' + field.name + '="' + field.value + '"';
                    }                
                });
                epcl_shortcode += ']'+content;
            }     
            
            epcl_shortcode += '[/epcl_' + current_tab.attr('id') + ']';

            $('textarea#shortcode-content').val(epcl_shortcode);

            // console.log(epcl_shortcode);

        }

        function format_icon(icon){
			if(icon.text)
			return '<i class="fa '+icon.id+'" style="font-size: 18px;"></i> '+icon.text;
		}

        $.fn.serializeControls = function() {
            var data = {};
          
            function buildInputObject(arr, val) {
              if (arr.length < 1)
                return val;  
              var objkey = arr[0];
              if (objkey.slice(-1) == "]") {
                objkey = objkey.slice(0,-1);
              }  
              var result = {};
              if (arr.length == 1){
                result[objkey] = val;
              } else {
                arr.shift();
                var nestedVal = buildInputObject(arr,val);
                result[objkey] = nestedVal;
              }
              return result;
            }
          
            $.each(this.serializeArray(), function() {
              var val = this.value;
              var c = this.name.split("[");
              var a = buildInputObject(c, val);
              $.extend(true, data, a);
            });
            
            return data;
          }

	});

})(jQuery);

function epcl_insert_shortcode(shortcode_content) {
     tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode_content);
     tinyMCEPopup.close();
}