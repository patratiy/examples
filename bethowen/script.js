var hover_item;
//var items_act = [];

$.when($.ready).then(function() {
    //console.log('1');
    $('.outer-container').hover( function() {
        var item = $(this).find('.container-item');
        //console.log( $(item).data('act-ico'));
        $(item).css('background', 'url(' + $(item).data('act-ico') + ')');
    },
    function() {
        var item = $(this).find('.container-item');
        if($(this).hasClass('active') == false) {
            $(item).css('background', 'url(' + $(item).data('nact-ico') + ')');
        }
    });

    $('body').on('click', '.outer-container', function() {
        $('.outer-container.active').find('.container-item').css('background', 'url(' + $('.outer-container.active').find('.container-item').data('nact-ico') + ')');
        $('.outer-container').removeClass('active');
        $(this).addClass('active');
        $(this).find('.container-item').css('background', 'url(' + $(this).find('.container-item').data('act-ico') + ')');
        $.ajax({
            url: '//' + location.hostname + '/ajax/ajax_load_data.php',
            data: {'section':$(this).data('filter')},
            dataType: 'json',
            type: 'POST',
            success: function(data) {
                $('.container-filter-action').html(data.html);
                applyShine();
            }
        });
    });

    if(device.mobile()) {
        $('.container-filter-top').slick({slidesToShow: 3, slidesToScroll: 3, arrows:true});
    }

    applyShine();
});

function applyShine() {
    $('.items-block ').hover(function() {
            hover_item = $(this);
            //items_act.push($(hover_item).find(".sun-shine").data('item'));
            $(hover_item).find(".sun-shine").animate({
                /*opacity: 0.25,*/
                left: "320px",
                /*height: "toggle"*/
            }, 600, function() {
                // Animation complete.
                $(hover_item).find(".sun-shine").css('left', '-60px');
            });
        },
        function () {
            //console.log(items_act);
            $(".sun-shine").css('left', '-60px');
        });
}