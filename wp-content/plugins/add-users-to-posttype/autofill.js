jQuery(document).ready(function($){
	$input = $('input[name="autp_autofill"]');
	$input.keyup(function(){
		$inputVal = $input.val();
		if($inputVal.length > 2){
			userQuery($inputVal);
		}else{
			$('#autp_fillcont').empty();
		}
	});
	$('div.autp_fillentry a').live('click',function(){
		id = $(this).attr('data-ID');
		dname= $(this).text();
		console.log(dname);
		//$('select#autp_users option[value='+$id+']').prop('selected', true);
		$("input[name='autp_users']").val(id);
		//$("#autp_users_disp_name").html(dname);
		$('input[name="autp_autofill"]').val(dname);
		//$('#autp_fillcont').empty();
	});

	//XHR pool
	$.xhrPool = [];
	$.xhrPool.abortAll = function() {
	    $(this).each(function(idx, jqXHR) {
	        jqXHR.abort();
	    });
	    $(this).each(function(idx, jqXHR) {
	        var index = $.inArray(jqXHR, $.xhrPool);
	        if (index > -1) {
	            $.xhrPool.splice(index, 1);
	        }
	    });
	};

	$.ajaxSetup({
	    beforeSend: function(jqXHR) {
	        $.xhrPool.push(jqXHR);
	    },
	    complete: function(jqXHR) {
	        var index = $.inArray(jqXHR, $.xhrPool);
	        if (index > -1) {
	            $.xhrPool.splice(index, 1);
	        }
	    }
	});
	//AJAX user query
	function userQuery($inputVal){
		$('#userloader').addClass('active');
		$inputVal = $input.val();
		$.xhrPool.abortAll();
		$.ajax({
	        type: 'POST',
	        url: pluginDir+"userquery.php",
	        data: {userQuery: $inputVal},
	        success: function(msg) {
	            $('#autp_fillcont').html(msg);
	            $('#userloader').removeClass('active');
	        }
	    });
	}

});