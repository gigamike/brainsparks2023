
$(document).ready(function () {

    //carousel
    $('#carousel1').carousel({
        interval: 4000,
        pause: "hover"
    });

    $('#loginCarousel').slick({
        dots: true,
        autoplay: true,
        autoplaySpeed: 1500,
        arrows: false,
    });


    $('#loginCarousels').slick({
        infinite: false,
        slidesToShow: 3,
        slidesToScroll: 1,
        //centerMode: true,
        dots: true,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 724,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });

});
