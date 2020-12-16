var windowWidth = $(window).innerWidth();
var windowHeight = $(window).innerHeight();

function mapsCircle() {
    $(".cont-circle-map").each(function() {
        $(this).height($(this).width());
        $(this).find('iframe').height($(this).width());
        $('.click-moadl-map').height($(this).width());

    });
}

//Carousels for the mobile app slider
function initOwl() {

    var c1 = $('#carousel-right');
    c1.owlCarousel({
        loop: true,
        dots: true,
        touchDrag: true,
        autoplay: true,
        autoplayTimeout: 2000,
        mouseDrag: true,
        dotsContainer: '#customDots',
        responsive: {
            0: {
                nav: false,
                items: 1
            },
            668: {
                nav: true,
                navContainer: '#customNav',
                items: 1
            }

        }
        /*afterMove: function(elem) {
            var current = this.currentItem;
            c2.trigger('owl.goTo',current);
        }*/
    });

    c1.on('changed.owl.carousel', function(event) {

        var textItem = $("#cl-" + (event.item.index - 1));

        //console.log(textItem);
        //console.log(event.item.index + 1);
        //console.log(event.item.index);
        if (!$(textItem).hasClass('fadeInDown')) {
            $(textItem).addClass('fadeInDown');
        }


    });

    /*var c2 = $('#carousel-left');
	c2.owlCarousel({
		items:1,
		loop:true,
		dots: false,
		autoplay: true,
		autoplayTimeout: 5000,
		animateOut: 'fadeOutUp',
    	animateIn: 'fadeInDown',
		navigation: false,
		touchDrag: false,
		mouseDrag: false,
		afterMove: function (elem) {
		  var current = this.currentItem;
		  c1.trigger('owl.goTo',current);
		}
	});*/

    $('#shampoo-services').on('shown.bs.modal', function(e) {
        c1.trigger('stop.owl.autoplay');
        //c2.trigger('stop.owl.autoplay');
    })
    $('#shampoo-services').on('hidden.bs.modal', function(e) {
        c1.trigger('play.owl.autoplay');
        //c2.trigger('play.owl.autoplay');
    })
}

function centerCarouselLeft() {
    var carouselLeft = $("#carousel-left").height();
    var rowHeight = $("#carousel-left").parent().parent().height();
    var marginTop = (rowHeight - carouselLeft) / 2;

    $("#carousel-left").css("margin-top", marginTop - 100);
    $("#carousel-left").css("visibility", "visible");

}

function newHeaderScroll() {

    if ($(".main-nav").length) {

        var $win = $(window);

        if ($win.scrollTop() == 0) {

            $(".main-nav").css("background", "transparent");

        } else {

            $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

        }

        //Listen to scroll event and set header bg color accordingly
        $win.scroll(function() {

            console.log($win.scrollTop());


            if ($win.scrollTop() == 0) {

                console.log("top");
                $(".main-nav").css("background", "transparent");

            } else {

                $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

            }

        });
    }

}

function generateTemplate(gridID, callback) {

    var jsonFile, titleText, chevron;

    if (gridID == ".grid#press") {

        jsonFile = "../data/press-coverage.json";
        titleText = "View Post";
        chevron = "right";

    } else if (gridID == ".grid#media") {

        jsonFile = "../data/media-resources.json";
        titleText = "Download";
        chevron = "down";
    }

    $.getJSON(jsonFile, function(data) {

        var template, imageUrl, text, source, link;
        var isVideo = false;

        $.each(data, function(key, val) {

            imageUrl = val.imageUrl;
            text = val.text;
            source = val.source;
            link = val.link;
            isVideo = val.isVideo;

            template = '<div class="grid-item">';

            if (link && link != "") {

                template = template + '<a href="' + link + '" title="' + titleText + '" target="_blank">';
            }

            template = template + '<div class="grid-image">';

            if (imageUrl && imageUrl != "") {
                template = template + '<img src="../images/press/' + imageUrl + '" alt="' + source + '">';

                if (isVideo) {
                    template = template + '<div class="play-button"></div>';
                }
            }

            template = template + '</div><div class="grid-text">';

            if (text && text != "") {
                template = template + '<p>' + text + '</p>';
            }

            template = template + '</div>';

            if (source && source != "") {
                template = template + '<div class="grid-footer"><div class="display-table"><div class="table-cell"><p>' + source + '</p></div>';

                if (link && link != "") {

                    template = template + '<div class="table-cell"><i class="fas fa-chevron-' + chevron + '"></i></div>';
                }

                template = template + '</div></div>';
            }

            if (link && link != "") {

                template = template + '</a>';
            }

            template = template + '</div>';

            $(template).appendTo(gridID);

            if (callback) {
                callback();
            }
        });

    }).fail(function() {

        if (!$(gridID + " .alert-danger").length) {

            $('<div class="alert alert-danger" role="alert"><p>Error trying to retrieve posts.</p></div>').appendTo(gridID);
            $(gridID).css("height", "auto");
            $(gridID).css("text-align", "center");
        }

    })

}

function pressGrid() {

    var $pressGrid;

    $pressGrid = $(".grid#press").imagesLoaded(function() {

        // init Masonry after all images have loaded
        $pressGrid.masonry({
            columnWidth: '.grid-sizer',
            itemSelector: '.grid-item',
            gutter: '.gutter-sizer',
            percentPosition: false,
            stagger: 30,
            resize: true
        });

        $(".grid#press").animate({ opacity: 1.0 }, "slow");

    });

}

function mediaGrid() {

    var $mediaGrid;

    $mediaGrid = $(".grid#media").imagesLoaded(function() {

        // init Masonry after all images have loaded
        $mediaGrid.masonry({
            columnWidth: '.grid-sizer',
            itemSelector: '.grid-item',
            gutter: '.gutter-sizer',
            percentPosition: false,
            stagger: 30,
            resize: true
        });

        $(".grid#media").animate({ opacity: 1.0 }, "slow");

    });


}

function newHeaderScroll() {

    if ($(".main-nav").length) {

        var $win = $(window);

        if ($win.scrollTop() == 0) {

            $(".main-nav").css("background", "transparent");

        } else {

            $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

        }

        //Listen to scroll event and set header bg color accordingly
        $win.scroll(function() {


            if ($win.scrollTop() == 0) {

                if ($(".navbar-collapse.show").length == 0) {

                    $(".main-nav").css("background", "transparent");
                }

            } else {

                $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

            }

        });
    }

}

//Populate grid in Press section and init masonry
function initPressSection() {

    var toggleTabs = false;

    //Add html markup from json and init masonry
    if (windowWidth > 767) {

        generateTemplate('.grid#press', function() {
            pressGrid();
        });

    } else {

        generateTemplate('.grid#press');
        $('.grid#press').animate({ opacity: 1.0 }, "slow");
    }

    //Init tabs
    $('#press-section-tabs a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    })

    //On tab change, init Media masonry if tab is Media
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {

        var currentTab = e.target;

        if ($(currentTab).attr('href') == "#media-resources" && !toggleTabs) {

            generateTemplate('.grid#media', function() {
                mediaGrid();
            });

            toggleTabs = true;

        }

    })

}

function initHeaderWithTopbar() {
    var $win = $(window);


    if ($(".main-nav").length) {

        //If menu is open, always stay with white bg
        if ($(".navbar-collapse.collapse.show").length) {

            $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

        } else {

            //Set transparent bg when scroll is on top and white when not
            if ($win.scrollTop() == 0) {

                $(".main-nav").css("background", "transparent");

            } else {

                $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

            }

            //Listen to scroll and repeat behaviour according to scroll position
            $win.scroll(function() {
                if ($win.scrollTop() == 0) {

                    $(".top-bar").css("display", "none");
                    $(".main-nav").css("background", "transparent");
                    $(".main-nav").css("top", "0");

                } else {

                    $(".top-bar").css("display", "block");
                    $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");
                    $(".main-nav").css("top", "30.4px");

                }

            });

        }
    }
}

function initCollapsedMenu() {

    $(".navbar-toggler").click(function() {

        if ($(".navbar-collapse.show").length) {

            if ($(window).scrollTop() == 0) {

                $(".main-nav").css("background", "transparent");

            } else {

                $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

            }

        } else {

            $(".main-nav").css("background", "rgba(255, 255, 255, 0.8)");

        }


    });
}


$(document).ready(function($) {

    //Set nav v2 bg color to white on scroll - different behaviour depending on the visibility of the top bar
    if ($(".top-bar--hidden").length) {

        newHeaderScroll();

    } else {

        initHeaderWithTopbar();
    }

    //Set header bg color to white when menu is open
    initCollapsedMenu();

    //Init maps on Homepage
    mapsCircle();

    //investor form functions
    $('.contBMForm div[align="center"]').addClass('hide_last_bm');

    $("input[name*='fldfirstname']").attr("placeholder", "First Name*");
    $("input[name*='fldlastname']").attr("placeholder", "Last Name*");
    $("input[name*='fldEmail']").attr("placeholder", "Email*");
    $("input[name*='fldfield6']").attr("placeholder", "Phone Number");
    $("input#btnSubmit").attr("value", "SUBMIT");



});


$(window).load(function() {

    if ($("#press").length) {
        initPressSection();
    }

});


$(window).on('resize', function() {

    windowWidth = $(window).innerWidth();
    windowHeight = $(window).innerHeight();

    mapsCircle();

});