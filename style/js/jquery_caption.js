$(document).ready(function() {
	// Every image referenced from a Markdown document
	$(".recipe-content img").parent().each(function() {
		// Let's put a caption if there is one
		if($(this).contents().first().is('img') && $(this).contents().size() == 2 ) {
           $(this).contents().eq(0).wrap('<div class="image"></div>');
           $(this).contents().eq(1).wrap('<div class="caption"></div>');
           $(this).replaceWith($('<div class="figure">' + this.innerHTML + '</div>'));
          }
		});
});