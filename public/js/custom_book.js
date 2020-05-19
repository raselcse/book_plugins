jQuery(function ($) {

	/*
	 * Load More
	 */
    var ppp = $('#more_books').data('ppp'); // Post per page
    var cat = $('#more_books').data('category');
    var maxPage = $('#more_books').data('maxpage');
    var pageNumber = 1;

    function load_posts() {
        pageNumber++;
        var str = '&cat=' + cat + '&pageNumber=' + pageNumber + '&ppp=' + ppp + '&action=more_book_ajax';
        console.log(str);
        $("#more_books").text("Loading...");
        $.ajax({
            type: "POST",
            dataType: "html",
            url: ajax_posts.ajaxurl,
            data: str,
            success: function (data) {
                var $data = $(data);
                if ($data.length) {

                    $("#book-section").append($data);
                    $("#more_books").text("Load More");
                    $("#more_books").attr("disabled", false);
                    if (maxPage == pageNumber) {
                        $("#more_books").after("<p class='all_books_loaded'>All books have loaded</p>");
                        $("#more_books").remove();
                    }
                } else {
                    $("#more_books").attr("disabled", true);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }

        });
        return false;
    }

    $("#more_books").on("click", function (e) { // When btn is pressed.
        e.preventDefault();
        $("#more_books").attr("disabled", true); // Disable the button, temp.

        load_posts();

        console.log(maxPage + ' ' + pageNumber);
    });

});