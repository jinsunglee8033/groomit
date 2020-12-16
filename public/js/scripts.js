//Center banner logo
function centerV (container, element) {
    var containerH = $(container).height();
    var elementH = $(element).height();
    var elementP = (containerH) - (elementH)
    elementP = elementP / 2;
    $(element).css("padding-top", elementP);
    //console.log (elementP);
}

//sticky footer Height
function stickyFooter() {
    var footerH = $("#footer").height();
    $("body").css("margin-bottom", footerH+75);
    console.log (footerH+75);
}

$(document).ready(function(){
  /*centerV("#cont-banner-home", ".cont-banner-title");
  setTimeout(
    stickyFooter()
    , 150);*/
});


$( window ).resize(function() {
  /*setTimeout(
    stickyFooter()
    , 150);*/
});