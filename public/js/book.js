$(document).ready(function() {
    
		$(".remove").click(function(e) {
			console.log($(this).closest('li').attr('id'));
			console.log($(this).siblings('a').text());
		});
    
});