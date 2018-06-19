$(document).ready(function() {
    window.addEventListener('online', handleConnectionChange);
    window.addEventListener('offline', handleConnectionChange);

    $('.collapse-nav').click(function (e) {
        e.preventDefault();
        var collapse = !$('body').hasClass('collapsed');
        if (collapse) {
            $('body').addClass('collapsed');
            Cookies.set('sidebar_collapsed', 1);
        } else {
            $('body').removeClass('collapsed');
            Cookies.remove('sidebar_collapsed');
        }
    });

    $('.burger-menu, .close-nav').click(function (e) {
        e.preventDefault();
        var open = !$('body').hasClass('mobile-opened');
        if (open) {
            $('body').addClass('mobile-opened');
        } else {
            $('body').removeClass('mobile-opened');
        }
    });

    $('.mobile-overlay').click(function (e) {
        e.preventDefault();
        $('body').removeClass('mobile-opened');
    });

    //Init MaterializeCSS selects
    $('select').material_select();
    //Init MaterializeCSS modals
    $('.modal').modal();

    //Init tabs
    $('.tab').click(function () {
        $('.tab').removeClass('current');
        $(this).addClass('current');
        $('.panel').addClass('hidden');
        $('#' + $(this).attr('for')).removeClass('hidden');
    });

    //Init MaterializeCSS datepickers
    $('.datepicker').pickadate({
        monthsFull: [Translator.trans('global.months.january'), Translator.trans('global.months.february'),
                     Translator.trans('global.months.march'), Translator.trans('global.months.april'), Translator.trans('global.months.may'),
                     Translator.trans('global.months.june'), Translator.trans('global.months.july'), Translator.trans('global.months.august'),
                     Translator.trans('global.months.september'), Translator.trans('global.months.october'), Translator.trans('global.months.november'), Translator.trans('global.months.december')],
        monthsShort: [Translator.trans('global.months.january').substring(0, 3), Translator.trans('global.months.february').substring(0, 3),
                     Translator.trans('global.months.march').substring(0, 3), Translator.trans('global.months.april').substring(0, 3), Translator.trans('global.months.may').substring(0, 3),
                     Translator.trans('global.months.june').substring(0, 3), Translator.trans('global.months.july').substring(0, 3), Translator.trans('global.months.august').substring(0, 3),
                     Translator.trans('global.months.september').substring(0, 3), Translator.trans('global.months.october').substring(0, 3), Translator.trans('global.months.november').substring(0, 3),
                     Translator.trans('global.months.december').substring(0, 3)],
        weekdaysFull: [Translator.trans('global.days.sunday'), Translator.trans('global.days.monday'), Translator.trans('global.days.tuesday'), Translator.trans('global.days.wednesday'),
                       Translator.trans('global.days.thursday'), Translator.trans('global.days.friday'), Translator.trans('global.days.saturday')],
        weekdaysLetter: [Translator.trans('global.days.sunday').substring(0, 1), Translator.trans('global.days.monday').substring(0, 1), Translator.trans('global.days.tuesday').substring(0, 1),
                       Translator.trans('global.days.wednesday').substring(0, 1), Translator.trans('global.days.thursday').substring(0, 1), Translator.trans('global.days.friday').substring(0, 1),
                       Translator.trans('global.days.saturday').substring(0, 1)],
        today: Translator.trans('global.today').substring(0, 3)+'.',
        clear: Translator.trans('btn.clear'),
        close: Translator.trans('btn.close'),
        format: 'dd/mm/yyyy',
        formatSubmit: 'yyyy-mm-dd'

    });

    //Init lightboxes
    if ($('[name="data-lightbox"]').length > 0) {
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true
        });
    }

    //init image slider
    $('.slider-element').click(function (e) {
        e.preventDefault();
        var $imageFrame = $(this).closest('.slider-container').find('.slider-frame:first');
        $imageFrame.find('a:first').attr('href', $(this).attr('href'));
        $imageFrame.find('a:first').attr('data-title', $(this).attr('data-title'));
        $imageFrame.find('img:first').attr('src', $(this).attr('href'));
        $imageFrame.find('.image-label:first').html($(this).attr('data-title'));
        $(this).closest('.slider-elements').find('.slider-element').removeClass('active');
        $(this).addClass('active');
    });

    loadFilePreviews();


    //TEMPLATE FIELDS
    var $collectionHolder = $('#template-fields-holder');
    //Init sortable
    if ($('#template-fields-holder').find('.field').length > 0) {
        reloadSortableList($collectionHolder, '.field');
    }

    var indexFieldTemplate = $collectionHolder.find('.field').length;
    var $addFieldLink = $('<a href="#" class="add_field_link waves-effect waves-light btn">Add a new field</a>');
    var $newLinkDiv = $('<div></div>').append($addFieldLink);
    $collectionHolder.append($newLinkDiv);
    computePositions($collectionHolder);

    $addFieldLink.on('click', function(e) {
        e.preventDefault();
        addFieldForm($collectionHolder, $newLinkDiv);
    });

    function addFieldForm($collectionHolder, $newLinkDiv) {
        var prototype = $collectionHolder.attr('data-prototype');
        var $newForm = $(prototype.replace(/__name__/g, indexFieldTemplate));
        $newForm.find('.removeDatum').on('click', function(e) {
            e.preventDefault();
            $newForm.remove();
        });
        $newLinkDiv.before($newForm);
        indexFieldTemplate++;
        $('select').material_select();
        computePositions($collectionHolder);
        reloadSortableList($collectionHolder, '.field');
    }

    $('#template-fields-holder').on( "click", ".removeDatum", function() {
        $(this).closest('.field').remove();
        computePositions($collectionHolder);
    });
});


function loadFilePreviews() {
    $('.btn-image').unbind('click');
    $('.btn-image').click(function(){
        $(this).closest('.image-preview-wrapper').find('.has-preview').trigger('click');
    });

    $('.has-preview').unbind('change');
    $('.has-preview').on('change', function(e) {
        var reader = new FileReader();
        var self = $(this);
        reader.onload = function (e) {
            self.closest('.image-preview-wrapper').find('img').attr('src',e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    });
}

function handleConnectionChange(event){
    var element = document.getElementById("offline-message");
    if(event.type == "offline"){
        element.classList.remove("hidden");
    }

    if(event.type == "online"){
        element.classList.add("hidden");
    }
}
