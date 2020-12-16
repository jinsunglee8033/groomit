/* HEADER ANIMATION FOR SCROLL */
$(window).scroll(function(){
	/* Setting movable header */
	var scrollTop = $(window).scrollTop();
	$('#topbar').toggleClass( 'fixed', scrollTop > 500 );
});

/* SCROLLING FUCNATIONALITY */
$(window).load(function(){
	$(document).ready(function($) {
		$('a[href^="#"]').bind('click.smoothscroll', function(e) {
			e.preventDefault();			
			// Get the current target hash
			var target = this.hash;			
			// Animate the scroll bar action so its smooth instead of a hard jump
			$('html, body').stop().animate({
				'scrollTop' : $(target).offset().top
			}, 900, 'swing', function() {
				window.location.hash = target;
				//alert(window.location.hash);
			});
		});
	});
});