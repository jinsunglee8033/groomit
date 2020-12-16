var windowWidth = $(window).innerWidth();
var windowHeight = $(window).innerHeight();

function mapsCircle() {
    $(".cont-circle-map").each(function() {
        $(this).height($(this).width());
        $(this).find('iframe').height($(this).width());
        $('.click-moadl-map').height($(this).width());

    });
}


function centerVertical(container, tocenter, plus) {
    var containerH = $(this).height();
    var toCenterH = $(tocenter).height();
    var plusH = $(plus).height();
    var paddingTop = ((containerH - toCenterH) / 2);
    $(container).css('padding-top', (paddingTop - plusH));
}

function setBannerHeight() {


    if (windowWidth < 768) {
        $("#banner").height(windowHeight);
    }
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
function topBannerHomer(){
    if ($("#home-main").length != 0) {
        if ($(window).scrollTop() == 0) {
            $("#top-banner-home").css("display", "block");
            console.log('muestra top banner home');
        } else {
            $("#top-banner-home").css("display", "none");
            console.log('NO muestra top banner home');
        }
    } 
}
function headerBG(){
    if ($("#home-main").length == 0) {
        $("#top-banner").css("display", "block");
        if ($(window).scrollTop() == 0) {
            $("#header").css("background", "transparent");
            
        } else {
            $("#header").css("background", "rgba(255, 255, 255, 0.8)");
            $("#top-banner").css("display", "none");

        }
    } else{
        if ($(window).scrollTop() == 0) {
            $("#header").css("background", "transparent");
        } else {
            $("#header").css("background", "rgba(255, 255, 255, 0.8)");
        }
    }
}

/* SCROLLING FUCNATIONALITY */
$(document).ready(function($) {
    headerBG();
    topBannerHomer();

    $(".navbar-toggle").click(function() {


            if ($(".navbar-collapse.collapse.in").length) {

                if ($(window).scrollTop() == 0) {

                    $("#header").css("background", "transparent");
                }

            } else {

                $("#header").css("background", "rgba(255, 255, 255, 0.8)");


            }



    });

    //Set nav v2 bg color to white on scroll
    newHeaderScroll();


    $('.contBMForm div[align="center"]').addClass('hide_last_bm');


    $("input[name*='fldfirstname']").attr("placeholder", "First Name*");
    $("input[name*='fldlastname']").attr("placeholder", "Last Name*");
    $("input[name*='fldEmail']").attr("placeholder", "Email*");
    $("input[name*='fldfield6']").attr("placeholder", "Phone Number");
    $("input#btnSubmit").attr("value", "SUBMIT");


    /*if ($( "#banner" ).hasClass( "banner-application" )){
      centerVertical ('#banner', '#centerBannerHome', '#header');
    } else if ($( "#banner" ).hasClass( "home" )) {
		setBannerHeight();
	} else {
      centerVertical ('#banner', '#centerBannerHome', '');
    }*/

    if ($("#banner").length) {
        setBannerHeight();
        $("#banner").css('margin-top', $("#top-banner").outerHeight());
    }

    $(function() {
        var $win = $(window);

        $win.scroll(function() {
                headerBG();
                topBannerHomer();
        });
    });


    /*if ($( "#top-banner" ).hasClass( "banner-application-ok" )){
      centerVertical ('#top-banner', '#centerBannerHome', '#header');
    }*/

    mapsCircle();
    /* HEADER ANIMATION FOR SCROLL */
    $(window).scroll(function() {
        /* Setting movable header */
        var scrollTop = $(window).scrollTop();
        $('#topbar').toggleClass('fixed', scrollTop > 500);
    });


    $('#collapseSix').on('hidden.bs.collapse', function() {
        $('.dateHidde').fadeIn();
    });

    $('#collapseSix').on('shown.bs.collapse', function() {
        $(".dateHidde").fadeOut("slow");
    });



    function availabilityBox() {
        $(".availabilityBoxCheck").each(function() {
            var availabilityBoxWMax = 0;

            if ($(this).width() > availabilityBoxWMax) {
                availabilityBoxWMax = $(this).width();
            }
            //$(".availabilityBoxCheck").width(availabilityBoxWMax);
            $(".availabilityBox").height(availabilityBoxWMax);

            $(".dateHidde").fadeOut("slow");


            //console.log(availabilityBoxWMax);

        });

        // $(".availabilityBoxDay").each(function() {
        //
        // 	var availabilityBoxDayH = $(this).height();
        // 	var availabilityBoxDaySpanH = $(this).find("span").height();
        // 	var availabilityBoxDaySpanHPadding = ( (availabilityBoxDayH) - (availabilityBoxDaySpanH/2) );
        // 	var availabilityBoxDaySpanHPaddingOk = (availabilityBoxDayH);
        // 	$(this).css("padding-top", availabilityBoxDaySpanHPadding);
        //
        // });

    }


    $('.openAvailability').click(function() {
        if ($(this).hasClass('collapsed')) {
            setTimeout(
                availabilityBox, 330)
        } else {
            $(".dateHidde").fadeIn("fast");
        }
    });

    $(window).resize(function() {

        windowWidth = $(window).innerWidth();
        windowHeight = $(window).innerHeight();

        $(".dateHidde").fadeIn("slow");
        setTimeout(
            availabilityBox, 330)
    });





    var offset;
    $(".scrollToTop").click(function() {
        var linkClickH = $(this).height();
        $('#accordion').on('shown.bs.collapse', function() {

            var panel = $(this).find('.in');
            var toScroll = panel.offset().top;

            var navH = $("#header").outerHeight();
            console.log(toScroll);

            var scrokktopOk = toScroll - (navH + linkClickH + 20);
            //var scrokktopOk = toScroll - (500);

            console.log(navH);
            console.log(linkClickH);

            $('html, body').animate({
                scrollTop: (scrokktopOk)
            }, 330);

        });
    });


    //Only one checkbox
    $(':checkbox').on('change', function() {
        var th = $(this),
            name = th.attr('name');
        if (th.is(':checked')) {
            $(':checkbox[name="' + name + '"]').not(th).prop('checked', false);
        }
    });


    $('#accordion').on('show.bs.collapse', function() {
        $('#accordion .in').collapse('hide');
    });

    $.validator.addMethod(
        "regex",
        function(value, element, regexp) {
            var check = false;
            return this.optional(element) || regexp.test(value);
        },
        "Please check your input."
    );

    $.validator.addMethod("same", function(value, element, param) {
        return this.optional(element) || value == param;
    }, "Please specify same value as {0}");

    //Init owl carousel - Homepage
    if ($("#home-main").length) {
        $("#cl-1").addClass('fadeInDown');
        initOwl();
        AOS.init();
    }

    //Init owl carousel - Homepage
    if ($("section#footer").length) {
        AOS.init();
    }


});

function generateTemplate(gridID, callback) {

    var jsonFile, titleText, chevron;

    if (gridID == ".grid#press") {

        jsonFile = "../data/press-coverage.json";
        titleText = "View Post";
        chevron = "right";

    } else if (gridID == ".grid#media") {

        jsonFile = "../data/media-resources.json?v=1.1";
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
            altImage = val.altImage;
            isVideo = val.isVideo;

            template = '<div class="grid-item">';

            if (link && link != "") {

                template = template + '<a href="' + link + '" title="' + titleText + '" target="_blank">';
            }

            template = template + '<div class="grid-image">';

            if (imageUrl && imageUrl != "") {
                template = template + '<img src="../images/press/' + imageUrl + '" alt="' + altImage + '">';

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

function initBanners() {

    $("#banner").animate({ opacity: 1 });

}

function initBannersNew() {

    $(".banner").animate({ opacity: 1 });

}


$(window).load(function() {

    /*if ($("#home-main").length) {
        centerCarouselLeft();
    }*/

    if ($("#press").length) {

        initPressSection();

    }

    if ($("#banner").length) {

        initBanners();

    }

    if ($(".banner").length) {

        initBannersNew();

    }

});


function previewImage(fileObj, imgPreviewId) {
    var allowExtention = ".jpg,.bmp,.gif,.png,.jpeg"; //allowed to upload file type
    //document.getElementById("hfAllowPicSuffix").value;
    var extention = fileObj.value.substring(fileObj.value.lastIndexOf(".") + 1).toLowerCase();
    var browserVersion = window.navigator.userAgent.toUpperCase();
    if (allowExtention.indexOf(extention) > -1) {
        if (fileObj.files) {
            if (window.FileReader) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(imgPreviewId).setAttribute("src", e.target.result);
                };
                reader.readAsDataURL(fileObj.files[0]);
            } else if (browserVersion.indexOf("SAFARI") > -1) {
                alert("don't support  Safari6.0 below broswer");
            }
        } else if (browserVersion.indexOf("MSIE") > -1) {
            if (browserVersion.indexOf("MSIE 6") > -1) { //ie6
                document.getElementById(imgPreviewId).setAttribute("src", fileObj.value);
            } else { //ie[7-9]
                fileObj.select();
                fileObj.blur();
                var newPreview = document.getElementById(imgPreviewId);

                newPreview.style.border = "solid 1px #eeeeee";
                newPreview.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale',src='" + document.selection.createRange().text + "')";
                newPreview.style.display = "block";

            }
        } else if (browserVersion.indexOf("FIREFOX") > -1) { //firefox
            var firefoxVersion = parseFloat(browserVersion.toLowerCase().match(/firefox\/([\d.]+)/)[1]);
            if (firefoxVersion < 7) { //firefox7 below
                document.getElementById(imgPreviewId).setAttribute("src", fileObj.files[0].getAsDataURL());
            } else { //firefox7.0+
                document.getElementById(imgPreviewId).setAttribute("src", window.URL.createObjectURL(fileObj.files[0]));
            }
        } else {
            document.getElementById(imgPreviewId).setAttribute("src", fileObj.value);
        }
    } else {
        alert("only support" + allowExtention + "suffix");
        fileObj.value = ""; //clear Selected file
        if (browserVersion.indexOf("MSIE") > -1) {
            fileObj.select();
            document.selection.clear();
        }

        document.getElementById(imgPreviewId).setAttribute("src", '');

    }
}


$(window).on('resize', function() {
    mapsCircle();

    windowWidth = $(window).innerWidth();
    windowHeight = $(window).innerHeight();

    if ($("#banner.home").length) {
        setBannerHeight();
    }
    $("#banner").css('margin-top', $("#top-banner").outerHeight());
});