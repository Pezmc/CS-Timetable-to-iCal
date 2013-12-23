var currentPage = 0;
var furthestPage = 0;
var pages = ['#year', '#course', '#semester', '#courses', '#result'];

$(document).ready(function() {

    /** Make the links on the page clickable **/
    function makeClickable($page, $nextpage) {
        if (currentPage >= 4) return true;

        /** Form page **/
        if (currentPage == 3) {
            $page.find('form').submit(function(e) {

                // Send a post
                var url = 'ajax.php' + $(this).attr('action');
                $nextpage.load(url, $(this).serializeArray(), function() {
                    makeClickable($(this), $(pages[currentPage + 1]));
                });

                // Move to the next page
                furthestPage = currentPage + 1;
                $('#wizard').steps("next");

                e.preventDefault();
            });

            return true;
        }

        /** Every other page **/
        $page.find('a').each(function() {
            $(this).click(function(e) {

                // Load the content for the next page
                var url = 'ajax.php' + $(this).attr('href');
                $nextpage.load(url, function() {
                    makeClickable($(this), $(pages[currentPage + 1]));
                });

                // Move to the next page
                furthestPage = currentPage + 1;
                $('#wizard').steps("next");

                e.preventDefault();
            });

        });
    }

    $('#year').load('ajax.php', function() {
        makeClickable($(pages[0]), $(pages[1]));
    });

    // Clear and tick all functionality for page 4
    $('body').on('click', '#tickall', function() {
        $(this).parent().find(':checkbox').prop('checked', 'checked');
    }).on('click', '#clear', function() {
        $(this).parent().find(':checkbox').prop('checked', '');
    });

});

$("#wizard").steps({
    headerTag: "h1",
    bodyTag: "section",
    transitionEffect: "slideLeft",
    labels: {
        finish: "Download"
    },
    onStepChanging: function(event, currentIndex, newIndex) {
        // Don't let the user skip pages
        if (newIndex > furthestPage) {
            return false;
        }

        return true;
    },
    onStepChanged: function(event, currentIndex, priorIndex) {
        currentPage = currentIndex;
        if (currentPage > furthestPage) furthestPage = currentIndex;
    },
    onFinishing: function(event, currentIndex) {

        var downloadURL = "ajax.php?page=6";
        $.ajax({
            type: "HEAD",
            url: downloadURL
        }).done(function(data, textStatus, jqXHR) {

            var requiredType = 'text/calendar';

            // Validate the content type header for our calendar
            if (jqXHR.getResponseHeader('Content-Type')) {
                var content = jqXHR.getResponseHeader('Content-Type');
                if (content.toLowerCase().substring(0, requiredType.length) == requiredType) {

                    // Set the thanks message
                    $(pages[4]).fadeOut(function() {

                        // Unfortunately window location must come after all ajax
                        $(this).load("ajax.php?page=7", function() {

                            $(this).fadeIn();

                            // Should force a download
                            window.location.href = downloadURL;
                        });

                    });

                } else {
                    alert("Server error while building calendar. Please try again.");
                }
            } else {
                alert("Server error while building calendar. Please try again.");
            }

        }).fail(function(jqXHR, textStatus, errorThrown) {
            alert("Unexpected HTTP error on download request. " + errorThrown);
        });

        return true;
    }
});