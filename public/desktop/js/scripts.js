var windowWidth = $(window).innerWidth();
var windowHeight = $(window).innerHeight();
var resizeTimeout;
var stickyOffset = ($('header').height()) * 2;
var headerHeight = $('header').height();

var app = {};

//Extend animate.css to get callback on animation end
$.fn.extend({
    animateCss: function(animationName, callback) {
        var animationEnd = (function(el) {
            var animations = {
                animation: 'animationend',
                OAnimation: 'oAnimationEnd',
                MozAnimation: 'mozAnimationEnd',
                WebkitAnimation: 'webkitAnimationEnd',
            };

            for (var t in animations) {
                if (el.style[t] !== undefined) {
                    return animations[t];
                }
            }
        })(document.createElement('div'));

        this.addClass('animated ' + animationName).one(animationEnd, function() {
            $(this).removeClass('animated ' + animationName);

            if (typeof callback === 'function') callback();
        });

        return this;
    }
});

//Animation listener
function animateCSS(element, animationName, callback) {
    const node = document.querySelector(element)
    node.classList.add('animated', animationName)

    function handleAnimationEnd() {
        node.classList.remove('animated', animationName)
        node.removeEventListener('animationend', handleAnimationEnd)

        if (typeof callback === 'function') callback()
    }

    node.addEventListener('animationend', handleAnimationEnd)
}


//Add margin-top to content for fixed header
app.initFixedHeader = function() {

    var headerHeight = $('header').outerHeight();
    //console.log(headerHeight);

    $('#main').css("margin-top", headerHeight - 1);
    $('#vouchers').css("margin-top", headerHeight);
    $('#my-appointments').css("margin-top", headerHeight + 50);

    //$('#main').animate({"margin-top": headerHeight+'px',"opacity": 1 },500);
    //$('#vouchers').animate({"margin-top": headerHeight+'px', "opacity": 1 },500);

    $('#main').animate({ "opacity": 1 }, 500);
    $('#vouchers').animate({ "opacity": 1 }, 500);
    $('#my-appointments').animate({ "opacity": 1 }, 500);

}

//Update margin-top on content
app.updateFixedHeader = function() {

    var headerHeight = $('header').outerHeight();
    var contentMargin, elem;

    if ($("#main").length) {

        elem = document.getElementById("main");

    } else if ($("#vouchers").length) {

        elem = document.getElementById("vouchers");

    } else {

        elem = document.getElementById("my-appointments");

    }

    contentMargin = parseInt(elem.style.marginTop);


    //console.log(contentMargin);
    //console.log(headerHeight);

    if (headerHeight != contentMargin) {

        //console.log(1);
        app.initFixedHeader();
        headerHeight = 0;

    }

}

//Hide form when successful submission
app.hideForm = function() {
    /*if ($(".alert-success").length) {
    		$("form#subscribe").css("display","none");
    }*/
    if ($(".alert-danger").length) {
        $("body > .container").css("display", "none");
    }
}

//Init Owl Carousel
app.initCarousel = function() {

    var touchDrag, items;
    var owl = $(".owl-carousel");

    if (windowWidth > 1199) {
        touchDrag = false;
        center = false;
        dots = false;
        margin = 50;
        stagePadding = 0;
    } else if (windowWidth > 991 && windowWidth < 1200) {
        touchDrag = false;
        center = false;
        dots = false;
        margin = 30;
        stagePadding = 0;
    } else if (windowWidth > 767 && windowWidth < 992) {
        touchDrag = true;
        center = false;
        dots = true;
        margin = 10;
        stagePadding = 70;
    } else {
        touchDrag = true;
        center = false;
        dots = true;
        margin = 15;
        stagePadding = 50;
    }


    if ($("#cat-service").length) {
        var itemsService = 2;
    } else {
        var itemsService = 3;
    }


    owl.owlCarousel({
        center: center,
        loop: false,
        mouseDrag: false,
        touchDrag: touchDrag,
        dots: dots,
        margin: margin,
        stagePadding: stagePadding,
        responsive: {
            0: {
                items: 1
            },
            667: {
                items: 2
            },
            991: {
                items: 3
            }
        }
    });

    // Fired before current slide change
    owl.on('initialized.owl.carousel', function(event) {
        // Do something

    });

};

app.reinitCarousel = function() {

    $(".owl-carousel").trigger('destroy.owl.carousel').removeClass('owl-loaded');
    app.initCarousel();

}

//Equal services height
app.servicesHeight = function(elem, fadeElem) {

    var highest = 1;
    var thisHeight;

    $(elem).css("height", "auto");

    $(elem).each(function(index, element) {

        thisHeight = $(this).outerHeight();

        if (thisHeight > highest) {
            highest = thisHeight;
        }
    });

    $(elem).outerHeight(highest);
    if ($(fadeElem).length) {
        $(fadeElem).fadeTo("fast", 1);
    }
};


//Change status of element (active, selected)
app.selectElem = function(elem, elemClass) {
    //alert(elem);
    $(elem).click(function() {

        if ($(this).hasClass(elemClass)) {
            $(this).removeClass(elemClass);
        } else {
            $(elem).removeClass(elemClass);
            $(this).addClass(elemClass);
        }

    });

};


//In Select Pet page, add button Add pet to this appointment when selecting pet
app.addPetToAppointment = function() {

    $("#my-pets .my-card:not(#add-new-card)").click(function() {

        if ($(this).hasClass("selected")) {
            $(this).removeClass("selected");
        } else {
            $("#my-pets .my-card:not(#add-new-card)").removeClass("selected");
            $(this).addClass("selected");
        }

        //Fade in or out button according to selection
        if ($(".selected").length) {
            $('#btn_continue').show();
            $('#add-new-card').hide();
            $("#add-pet-appointment").fadeIn('fast');

        } else {
            $('#btn_continue').hide();
            $('#add-new-card').show();
            $("#add-pet-appointment").fadeOut('fast');

        }

    });

};

//Section Select Size
app.selectSize = function() {

    var petSize;

    $(".cont-dog-size").click(function() {
        if (windowWidth < 768) {
            petSize = $(this).children("p");
            $("#xs-selected-size").html($(petSize).html());
        }
        $(".cont-dog-size").removeClass("active");
        $(this).addClass("active");
    });
};

//Init bootstrap tooltips
app.initTooltips = function() {
    $('[data-toggle="tooltip"]').tooltip();
};

//Scroll down button
app.initScrollBtn = function() {
    $('a[href*=#]').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: $($(this).attr('href')).offset().top }, 1000, 'linear');
    });
};

//Prevent selecting checkbox when clicking link inside label
app.initCollapse = function() {
    $(".collapse").click(function(event) {
        event.stopPropagation();
        event.preventDefault();
        $(this).collapse();
    });
};

//Prevent selecting checkbox when clicking link inside label
app.initModalCheckbox = function() {
    $(".open-non-r").click(function(event) {
        event.stopPropagation();
        event.preventDefault();

        $("#non-refundable").modal();

        if ($(".open-non-r").data("id")) {

            var appointment_id = $(".open-non-r").data("id");
            $("#non-refundable #back-form button.red-btn").attr("onClick", "appointment_delete(" + appointment_id + ")");
            $("#non-refundable #back-form button.red-btn").removeAttr("disabled");
        } else {
            $("#non-refundable #back-form button.red-btn").attr("onClick", 'appointment_delete()');
        }
    });
};


//Switch between login modal content (login, forgot-password, temporary-key, update-password)
app.switchModal = function(contentID) {

    var targetContent, activeContent, animationIn, animationOut;

    $(".switch-content").click(function() {

        $(".modal").css("overflow-y", "hidden");

        targetContent = $(this).data("content");
        animationIn = $(this).data("animationin");
        animationOut = $(this).data("animationout");
        activeContent = $(this).closest(".modal-body");

        if (!animationOut) {
            animationOut = 'flipOutY';
        }

        if (!animationIn) {
            animationIn = 'flipInY';
        }

        $(activeContent).animateCss(animationOut, function() {
            $(activeContent).addClass("hidden");
            $("#" + targetContent).removeClass("hidden");
            $("#" + targetContent).animateCss(animationIn);
            setTimeout(function() { $(".modal").css("overflow-y", "auto"); }, 1000);

        });
    });

};

//Reset modal content on close
app.resetModal = function(modalID) {

    $(modalID + " .close").click(function(e) {

        $(modalID + " .modal-body").addClass("hidden");
        $(modalID + " .modal-body").first().removeClass("hidden");
    });

}

//Hide or show Fav Groomers in Schedule page
app.favGroomers = function() {

    $('#select_groomer').on('change', 'input[name=select_groomer]:checked', function() {
        var value = $(this).val();

        if (value == "select_fav_groomer") {
            $("#fav_groomers").removeClass("hidden");
        } else {
            $("#fav_groomers").addClass("hidden");
        }
    });

}


//Launch calendar
//This function uses plugin bootstrap-material-datetimepicker
//The core file of that plugin was modified to add a new method "changeMonth"
//Be aware when updating plugin to add this method
app.initCalendar = function(calendarPeriod, calendarUnit) {

    var cancelText;
    var calendarPeriod = 7;
    var calendarUnit = 'months';
    var calendarFromPeriod = localStorage.getItem('calendarFromPeriod');
    var calendarFromUnit = localStorage.getItem('calendarFromUnit');
    var minDate;
    var setExtraCharge;

    if (windowWidth > 767) {
        cancelText = "CANCEL";

    } else {
        cancelText = "X";
    }

    //console.log(calendarFromPeriod);
    //console.log(calendarFromUnit);


    if (calendarFromPeriod == 'null' || calendarFromUnit == 'null') {

        minDate = new Date();
        //minDate = moment('2020-04-27');
        setExtraCharge = true;

    } else {

        minDate = moment().add(calendarFromPeriod, calendarFromUnit);
        setExtraCharge = false;

    }

    //console.log(calendarFromPeriod);
    //console.log(calendarFromUnit);

    $('.date').bootstrapMaterialDatePicker({
        weekStart: 1,
        format: 'YYYY-MM-DD',
        time: false,
        year: false,
        minDate: minDate,
        maxDate: moment().add(calendarPeriod, calendarUnit),
        clearButton: false,
        cancelText: cancelText,
        okText: "CONTINUE"
    }).on('open', function() {


        if (setExtraCharge === true) {

            $('.date').bootstrapMaterialDatePicker('setDate', moment(new Date()).add(1, 'days'));

            setTimeout(function() {

                var calendarMonth = $(".dtp-actual-month").text();
                var thisMonth = moment().format('MMM');
                thisMonth = thisMonth.toUpperCase();
                var today = moment().date();
                var dNumber;

                if (calendarMonth == thisMonth) {

                    $(".dtp-select-day").each(function() {

                        dNumber = $(this).text();

                        if (dNumber == today) {
                            $(this).addClass("today");
                            $(this).append('<span class="today-price">+$20.00</span>');
                            $(this).removeClass('selected');
                        }
                    })

                }

            }, 100);
        }

    }).on('changeMonth', function() {

        var calendarMonth = $(".dtp-actual-month").text();
        var thisMonth = moment().format('MMM');

        thisMonth = thisMonth.toUpperCase();

        if (calendarMonth == thisMonth && setExtraCharge === true) {

            setTimeout(function() {

                var today = moment().date();
                var dNumber;

                $(".dtp-select-day").each(function() {

                    dNumber = $(this).text();

                    if (dNumber == today) {
                        $(this).addClass("today");
                        $(this).append('<span class="today-price">+$20.00</span>');
                    }


                });

            }, 100);

        }

    });

    $("#dt-container").fadeTo("fast", 1);
    localStorage.clear(calendarPeriod);
    localStorage.clear(calendarUnit);
};


app.cardMask = function() {
    $(".credit-card").inputmask();
};

//Check Radio in Payments page when clicking on card
app.selectRadio = function() {
    $(".my-card").click(function() {
        $(this).find("input").prop("checked", true);
    });
};


//Center cols if less than the amount required to fill the row
app.centerCols = function() {

    var countCols = $(".card-wrapper:not(#new-pet)").length;
    var isLG = windowWidth > 1199 && countCols < 4;
    var isMD = windowWidth < 1200 && windowWidth > 991 && countCols < 3;
    var isSM = windowWidth < 992 && windowWidth > 767 && countCols < 2;

    //console.log(isMD);
    //console.log(isSM);
    //console.log(isLG);
    //console.log(windowWidth);
    //console.log(countCols);

    if (isMD || isSM || isLG) {
        $(".card-wrapper").css("float", "none");
        $(".card-wrapper").css("display", "inline-block");
        $(".after-title").css("text-align", "center");
        $(".card-wrapper").css("vertical-align", "top");
    } else {
        $(".card-wrapper").css("float", "left");
        $(".card-wrapper").css("display", "block");
        $(".after-title").css("text-align", "inherit");
    }

};

//Center cols if less than the amount required to fill the row
app.centerColsAddons = function() {

    var countCols = $("#add-ons #st-opt h4").length;

    if (countCols == 1) {
        $("#add-ons #st-opt h4").parent().css("float", "none");
        $("#add-ons #st-opt h4").parent().css("margin-left", "auto");
        $("#add-ons #st-opt h4").parent().css("margin-right", "auto");

    }

};

app.btnGroupsMaxWidth = function() {
    if ($("#shampoo label").length == 1) {
        $("#shampoo").parent().removeClass("col-md-7").addClass("col-sm-6");
        if ($("#is-matted").length) {
            $("#is-matted").parent().removeClass("col-md-5 col-sm-8 col-sm-offset-2 col-md-offset-0").addClass("col-sm-6");
        }
    }
}


//Manually submit form after confirming
app.submitForm = function() {

    var targetForm;

    $(".submitForm").click(function() {

        targetForm = $(this).data("form");

        $('#' + targetForm).submit();

    });

};


app.updateRating = function() {
    $('.starrr').on('starrr:change', function(e, value) {
        $('.rating').val(value);
    });

};

//Dashboard Edit Tip input
app.editTip = function() {

    var tipValue;

    $(".tip-options .btn-st-opt.red-btn").click(function() {
        $(this).parent().addClass("hidden");
        $(this).parent().parent().find($(".other-tip-edit")).removeClass("hidden");
    });

    $("#cancel-tip").click(function(e) {

        $(this).parent().addClass("hidden");
        $(".tip-options").removeClass("hidden");

    });

};


//Set divider in Dashboard according to the visible modules
app.addDivider = function() {

    var totalDivs = $('.bookings-history').length;

    if ($(totalDivs).length > 1) {
        $('.bookings-history').each(function(index, element) {
            if (index == totalDivs - 1) {
                $(this).addClass("divider");
            }
        });
    }

};

//My Appointments: organize pets panel according to amount of pets
app.arrangePets = function() {

    var petsCount;

    $(".pets-container").each(function(index, element) {

        petsCount = $(this).find(".col-md-6").length;

        if (petsCount == 1) {

            $(this).find(".col-md-6").addClass("col-md-offset-3");

        } else if (petsCount > 2) {

            $(this).css("overflow-y", "scroll");

        }
    });



};

//My Appointments: init Carousel
app.myAppointmentsCarousel = function() {

    var owl = $(".appointment-carousel.owl-carousel");
    var mouseDrag, touchDrag, dots;

    if ($(".bookings-history").length > 1) {
        touchDrag = true;
        dots = true;
        mouseDrag = true;
        nav = true;
    } else {
        touchDrag = false;
        dots = true;
        mouseDrag = false;
        nav = false;
    }



    owl.owlCarousel({
        loop: true,
        mouseDrag: mouseDrag,
        touchDrag: touchDrag,
        nav: nav,
        dots: true,
        items: 1
    });

    owl.on('resized.owl.carousel', function(event) {

        $(".history-content").css('height', 'auto');
        app.boxHeight();

        $(".appointment-carousel").css('height', 'auto');
        app.carouselHeight();


    });

};

//My Appointments: highlight heart on click
/*app.groomerFav = function () {

	$(".cell-groomer .glyphicon").on("click", function() {
		if ($(this).hasClass("glyphicon-heart")) {

			$(this).removeClass("glyphicon-heart").addClass("glyphicon-heart-empty");

		} else {

			$(this).removeClass("glyphicon-heart-empty").addClass("glyphicon-heart");
		}
	});
};*/

//My Appointments: equal height of boxes
app.boxHeight = function() {

    if (windowWidth > 767 && $(".groomit-credits").length == 0) {

        /*$("#my-appointments .modify-appointment").css("position", "absolute");
        $("#my-appointments .modify-appointment").css("margin-top", "0");
        $("#my-appointments .modify-appointment").css("bottom", "20px");
        $("#bottom-panel .modify-appointment").css("position", "absolute");
        $("#bottom-panel .modify-appointment").css("margin-top", "0");
        $("#bottom-panel .modify-appointment").css("bottom", "20px");*/
        $("#my-appointments .modify-appointment").css("position", "relative");
        $("#my-appointments .modify-appointment").css("margin-top", "0");
        $("#my-appointments .modify-appointment").css("bottom", "0");
        $("#bottom-panel .modify-appointment").css("position", "relative");
        $("#bottom-panel .modify-appointment").css("margin-top", "0");
        $("#bottom-panel .modify-appointment").css("bottom", "0");

        var maxHeight = 1;
        var thisHeight;
        var elems = $(".history-content");
        var count = elems.length;

        elems.each(function(index, element) {

            thisHeight = $(this).outerHeight();

            //console.log(thisHeight);

            if (thisHeight > maxHeight) {
                maxHeight = thisHeight;
            }

        });

        /*if ($("#my-appointments").length) {

            if (index == (count - 1)) {
                elems.css("height", maxHeight + 90);
            }

        } else {*/

        elems.height(maxHeight);
        $("#my-appointments .modify-appointment").css("position", "absolute");
        $("#my-appointments .modify-appointment").css("margin-top", "0");
        $("#my-appointments .modify-appointment").css("bottom", "20px");
        $("#bottom-panel .modify-appointment").css("position", "absolute");
        $("#bottom-panel .modify-appointment").css("margin-top", "0");
        $("#bottom-panel .modify-appointment").css("bottom", "20px");

        //  }

    } else {

        $("#my-appointments .modify-appointment").css("position", "relative");
        $("#my-appointments .modify-appointment").css("margin-top", "0");
        $("#my-appointments .modify-appointment").css("bottom", "0");
        $("#bottom-panel .modify-appointment").css("position", "relative");
        $("#bottom-panel .modify-appointment").css("margin-top", "0");
        $("#bottom-panel .modify-appointment").css("bottom", "0");

    }



};

app.carouselHeight = function() {

    if (windowWidth > 767) {

        carouselHeight = 1;

        $('.appointment-carousel').each(function() {

            thisHeight = $(this).height();

            if (thisHeight > carouselHeight) {

                carouselHeight = thisHeight;
            }

        });

        $('.appointment-carousel').height(carouselHeight);
    }


}

/* FAQs scroll */
app.initFAQScroll = function() {

    var offset
    $(".scrollToTop").click(function() {
        var linkClickH = $(this).height();
        $('#accordion').on('shown.bs.collapse', function() {

            var panel = $(this).find('.in');
            var toScroll = panel.offset().top;
            var scrokktopOk = toScroll - (linkClickH + (headerHeight + 20));

            $('html, body').animate({
                scrollTop: (scrokktopOk)
            }, 330);
            $('#accordion').off('shown.bs.collapse');

        });

    });


}

//Load shampoo data in pop up
app.loadShampoo = function() {

    var modal;

    $('#shampooInfoModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var recipient = button.data('shampoo');
        modal = $(this);

        $.getJSON('/desktop/js/shampoo-list.json', function(data) {

            var shampoo = $.grep(data, function(shampoo) { return shampoo.id === recipient; })[0];

            if (shampoo) {

                modal.find('.shampooName').text(shampoo.name).animate({ opacity: 1 });
                modal.find('.shampooInfo').text(shampoo.info).animate({ opacity: 1 });
                modal.find('.shampooInfo').text(shampoo.info).animate({ opacity: 1 });
                modal.find('.shampooDescription').html(shampoo.description).animate({ opacity: 1 });
                modal.find('.imageName').attr("src", "/desktop/img/shampoo/" + shampoo.image);
                modal.find('.imageName').animate({ opacity: 1 });


            } else {
                modal.find('.shampooDescription').text("No information was found for this shampoo");
            }
        });

    })


    $('#shampooInfoModal').on('hidden.bs.modal', function(event) {

        modal = $(this);

        modal.find('.shampooName').animate({ opacity: 0 });
        modal.find('.shampooInfo').animate({ opacity: 0 });
        modal.find('.shampooDescription').animate({ opacity: 0 });
        modal.find('.imageName').animate({ opacity: 0 });

    })

}

//Init animation on scroll
app.initAOS = function() {

    if ($(".aos").length) {
        AOS.init();
    }
}

//Vouchers/payment: Show/hide recipient fields
app.showRecipient = function() {

    $("#is_gift").change(function() {
        var ischecked = $(this).is(':checked');
        if (ischecked) {
            $("#recipient-info").fadeIn('fast');
            //$("#terms-fg").removeClass('last');
            $("#recipient_location").prop('required', true);
            $("#recipient_name").prop('required', true);
            $("#recipient_email").prop('required', true);
        } else {
            $("#recipient-info").fadeOut('fast');
            //$("#terms-fg").addClass('last');
            $("#recipient_location").prop('required', false);
            $("#recipient_name").prop('required', false);
            $("#recipient_email").prop('required', false);
        }
    });

}

//Vouchers/payment enable fields after selecting location
app.enableFields = function() {
    $('#recipient_location').on('change', function() {
        if (this.value == "") {
            $("#sender").attr('disabled', "disabled");
            $("#recipient_name").attr('disabled', "disabled");
            $("#recipient_email").attr('disabled', "disabled");
            $("#recipient_email_confirm").attr('disabled', "disabled");
            $("#voucher_message").attr('disabled', "disabled");
        } else {
            $("#sender").removeAttr("disabled");
            $("#recipient_name").removeAttr("disabled");
            $("#recipient_email").removeAttr("disabled");
            $("#recipient_email_confirm").removeAttr("disabled");
            $("#voucher_message").removeAttr("disabled");
        }
    });
}

app.submitOnClick = function(modalID) {

    var modalBody;

    $(modalID).on('shown.bs.modal', function(e) {
        $('input').keypress(function(k) {

            if (k.which == 13) {
                modalBody = $(this).closest(".modal-body").attr("id");
                $(this).closest(".modal-body").find(".type-submit").click();
                if (modalBody != "register-form") {
                    $(e.currentTarget).unbind();
                }


            }

        });

    })

    $(modalID).on('hidden.bs.modal', function(e) {
        $('input').unbind("keypress");
    });

}


//Document Ready
app.documentReady = function() {

    /*if ($('#block-appointments').length) {
        $('#block-appointments').modal('show');
    }*/

    if ($("#zip-not-available").length) {
        app.hideForm();
    }

    if ($(".owl-carousel:not(.appointment-carousel)").length) {
        app.initCarousel();
    }

    if ($('#select_groomer').length) {
        app.favGroomers();
    }

    app.selectElem(".cont-select-pet", "selected");
    app.selectElem("#my-payments .my-card:not(#add-new-card)", "selected");
    app.selectElem("#my-appointments .my-card:not(#add-new-card)", "selected");

    app.selectRadio();


    if ($(".card-wrapper").length) {
        app.centerCols();
    }

    app.selectSize();

    if ($('[data-toggle="tooltip"]').length) {
        app.initTooltips();
    }

    if ($("#add-ons #st-opt").length) {
        app.centerColsAddons();
        app.btnGroupsMaxWidth();
    }

    //app.initScrollBtn();
    app.switchModal();
    //app.initCollapse();

    if ($(".open-non-r").length) {
        app.initModalCheckbox();
    }

    if ($('.credit-card').length) {
        app.cardMask();
    }

    if ($('.submitForm').length) {
        app.submitForm();
    }

    if ($('.starrr').length) {
        $('.starrr').starrr();
        app.updateRating();
    }

    app.editTip();

    if ($('bottom-panel .bookings-history').length) {
        app.addDivider();
    }

    if ($('.pets-container').length) {
        app.arrangePets();
    }

    /*if ($(".cell-groomer .glyphicon").length) {
        app.groomerFav();
    }*/

    if ($("#register-modal").length) {
        app.resetModal("#register-modal");
    }

    if ($(".scrollToTop").length) {
        app.initFAQScroll();
    }

    app.loadShampoo();

    app.initAOS();

    if ($("#is_gift").length) {
        app.showRecipient();
        app.enableFields();
    }

    app.submitOnClick("#login-modal-parent");

    $("#time").focus(function() {
        $("#arrival-time").modal("show");
    });

    $("#af-smart-banner").on("remove", function() {
        //console.log("removed");
        app.resizeHeader();
    })
};

app.resizeHeader = function() {

    if ($('header').hasClass('fixed')) {
        var headerHeight = $('header').height();

        if ($("#af-smart-banner").length) {
            //console.log(1);
            var afBanner = $('#af-smart-banner').height();
            $('#main').css("margin-top", (headerHeight + afBanner) - 1);
            $('#vouchers').css("margin-top", (headerHeight + afBanner));
            $('#my-appointments').css("margin-top", headerHeight + 50 + afBanner);

        } else {
            //console.log(2);
            $('#main').css("margin-top", headerHeight - 1);
            $('#vouchers').css("margin-top", headerHeight);
            $('#my-appointments').css("margin-top", headerHeight + 50);

        }

    }

}

app.initStickyFooter = function() {


    if ($("#progress-bar".length)) {

        stickyOffset = stickyOffset + 6;
    }

}

//Window resize
app.windowResize = function() {

    windowWidth = $(window).innerWidth();
    windowHeight = $(window).innerHeight();

    /*clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(function() {

        app.resizeHeader();
    }, 100);*/

    app.resizeHeader();

    if ($(".card-wrapper").length) {
        app.centerCols();
    }
    if ($("#bottom-panel .history-content").length) {
        $(".history-content").css('height', 'auto');
        app.boxHeight();
    }
    if ($(".select-service").length) {
        app.servicesHeight(".service-items");
        app.reinitCarousel();
    }
    if ($(".package-specs").length) {
        app.servicesHeight(".package-specs li");
    }


};

//Window load
app.windowLoad = function() {

    //app.initFixedHeader();

    if ($(".select-service").length) {
        app.servicesHeight(".service-items", ".select-service");
        app.selectElem(".select-service", "selected");
    }
    if ($(".package-specs").length) {
        app.servicesHeight(".package-specs li");
    }

    if ($('#my-appointments').length) {
        app.myAppointmentsCarousel();
    }
    if ($("#my-appointments .history-content").length) {
        app.boxHeight();
    }
    if ($("#bottom-panel .history-content").length) {
        app.boxHeight();
    }
    if ($('.date').length) {
        /*app.initCalendar();*/
        $("#dt-container").fadeTo("fast", 1);
    }

    app.initStickyFooter();

    if ($('.appointment-carousel').length) {
        app.carouselHeight();
    }


};


$(window).scroll(function() {
    var sticky = $('header'),
        scroll = $(window).scrollTop();

    if (scroll >= stickyOffset && !sticky.hasClass('fixed')) {
        sticky.addClass('fixed fixed-shadow');

        if ($("#af-smart-banner").length) {

            var afBanner = $('#af-smart-banner').height();
            $('#main').css("margin-top", (headerHeight - afBanner) - 1);
            $('#vouchers').css("margin-top", (headerHeight - afBanner));
            $('#my-appointments').css("margin-top", (headerHeight + 50) - afBanner);

        } else {

            $('#main').css("margin-top", headerHeight - 1);
            $('#vouchers').css("margin-top", headerHeight);
            $('#my-appointments').css("margin-top", headerHeight + 50);
        }

        animateCSS('.fixed', 'slideInDown');
    }
    if (scroll > $('header').height() && sticky.hasClass('fixed') && !sticky.hasClass('fixed-shadow')) {
        sticky.addClass('fixed-shadow');
    }
    if ($(window).scrollTop() == 0 && sticky.hasClass('fixed')) {
        //animateCSS('.fixed', 'slideOutUp', function() { sticky.removeClass('fixed'); });
        sticky.removeClass('fixed');
        sticky.removeClass('fixed-shadow');
        $('#main').css("margin-top", "7rem");
        $('.main--no-margin#main').css("margin-top", "0");
        $('#vouchers').css("margin-top", "7rem");
        $('#my-appointments').css("margin-top", "7rem");
    }
});


$(document).ready(function() {
    app.documentReady();
});

$(window).load(function() {
    app.windowLoad();
});

$(window).resize(function() {
    app.windowResize();
});