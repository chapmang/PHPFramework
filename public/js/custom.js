$(document).ready(function() {
    
		$("#clickTest").click(function(e) {
			$.ajax({
			cache: false,
			url: 'home/test',
			dataType: 'json',
			contentType: 'charset=utf-8',
			data: {
				location: "Geoff"
                }
            })
			.done(function(response) {
				alert(response);
			})
			.fail(function(errorMsg) {
				console.log(errorMsg);
			});
		});
    
});