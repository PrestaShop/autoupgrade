$(document).ready(function(){

	$('#showStep17-1, #showStep17-abort').click(function(e){
		e.preventDefault();
		$('#hideStep17-2').hide();
		$('#hideStep17, #hideStep17basic, #hideStep17-1').show();

		if ('showStep17-abort' === $(this).attr('id')) {
			$('#showStep17-2').show();
		} else {
			$('#showStep17-1').hide();
		}

	});

	$('#showStep17-2').click(function(e){
		e.preventDefault();
		$('#hideStep17-1, #hideStep17basic').hide();
		$('#hideStep17-2').show();
		$(this).hide();
	});

});