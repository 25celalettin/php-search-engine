var timer;
$(document).ready(function() {

   $(".result").on("click", function() {
      
      var url = $(this).attr("href");
      var linkId = $(this).attr("data-linkId");
      if (!linkId) {
         alert("data-linkId attribute has not found");
      }

      increaseLinkClick(linkId, url);

      return false;
   });

   var grid = $(".imageResults");
   grid.masonry({
      itemSelector: ".gridItem",
      columnWidth: 200,
      gutter: 5,
      isInitLayout: false
   });

   $("[data-fancybox]").fancybox({

		caption : function( instance, item ) {
	        var caption = $(this).data('caption') || '';
	        var siteUrl = $(this).data('siteurl') || '';


	        if ( item.type === 'image' ) {
	            caption = (caption.length ? caption + '<br />' : '')
	             + '<a href="' + item.src + '">View image</a><br>'
	             + '<a href="' + siteUrl + '">Visit page</a>';
	        }

	        return caption;
	    },
	    afterShow : function( instance, item ) {
	        increaseImageClicks(item.src);
	    }


	});

});

function loadImage(src, className) {

	var image = $("<img>");

	image.on("load", function() {
		$("." + className + " a");  // $("." + className + " a").append(image);

		clearTimeout(timer);

		timer = setTimeout(function() {
			$(".imageResults").masonry();
		}, 500);

	});

	image.on("error", function() {
		
		$("." + className).remove();

		$.post("ajax/setBroken.php", {src: src});

	});

	image.attr("src", src);

}

function increaseLinkClick(linkId, url) {
   $.post("ajax/updateLinkCount.php", {linkId: linkId})
   .done(function(result) {
      if (result != "") {
         alert(result);
         return;
      }
      window.location.href = url;
   });
}

function increaseImageClicks(imageUrl) {

	$.post("ajax/updateImageCount.php", {imageUrl: imageUrl})
	.done(function(result) {
		if(result != "") {
			alert(result);
			return;
		}
	});

}