Dropzone.autoDiscover = false;


var checkFormCanBeSubmit = function(){
    var incompleteListCount = $( '.media-form-list .invalid-media').length;
    $( '.media-form-list').parents('form').find('input[type=submit]').prop('disabled', incompleteListCount ? true : false);

    $('.media-form-list').each(function(){
        var invalidMedia = $(this).find('.invalid-media');

        if(invalidMedia.length == 0){
            $(this).find('.media-list-incomplete').removeClass('media-list-incomplete');
        } else {
            $(this).find('.media-list-incomplete').addClass('media-list-incomplete');
        }
    });
};

var getSelectMediaIds = function(){
    var selectedMedia = $('#selectedMediaListModal .list').find('[data-selected-media-id]');

    var checkedMedia = [];
    selectedMedia.each(function(){
        var id = $(this).attr('data-selected-media-id');
        checkedMedia.push(id);
    });

    if(selectedMedia.length){
        var selectMediaIds = checkedMedia.join();
        return selectMediaIds;
    } else {
        return '';
    }
};

var refreshFormList = function(formList){
    var inputName = formList.attr('data-input-name');
    var inputElement = $('[name='+inputName+']');
    var inputValues = inputElement.val();

    formList.find('.ajax-spinner').removeClass('d-none');
    formList.find('.sortable-list').remove();

    $.ajax({
        url: formlistUrl + "?ids=" + inputValues,
        cache: false,
        beforeSend : function(){
            var html = jQuery('.ajax-spinner-prototype').html();
            formList.html(html);
        }
    }).done(function( html ) {
        formList.html( html );

        formList.parents('.form-group').find('.col-sm-5').removeClass('col-sm-5').addClass('col-sm-9');

        checkFormCanBeSubmit();

        var freemium = formList.data('freemium');
        var canConnectLists = formList.data('can-connect-lists');

        $( '.media-form-list[data-input-name='+inputName+'] .media-list-sortable' ).sortable({
            connectWith: canConnectLists ? '.media-list-sortable' : '',
            receive: function( event, ui ) {
                var target = $(event.target);
                var maxReceiverLength = target.parent('.media-form-list').data('max-gallery-images');
                var finalReceiverLength = target.find('li[data-media-id]').length;

                if(maxReceiverLength && finalReceiverLength > maxReceiverLength){
                    $('#maxAlert').modal('show');
                    ui.sender.sortable("cancel");
                }
            },
            update: function( event, ui ) {
                var mediaElements = $(this).children('[data-media-id]');

                var newIds = [];
                mediaElements.each(function(){
                    newIds.push($(this).attr('data-media-id'));
                });

                inputElement.val(newIds.join());
            }
        });

        var max = formList.data('max-gallery-images');


        formList.find('.btn-edit-action').click(function(e){
            if(freemium == '1' && $('input[name=id]').length){
                $('#freemiumAlert').modal('show');

                e.preventDefault();
                return false;
            }
        });

        $('button[data-input-name="'+inputName+'"]').click(function(e){
            var currentSelectionLength = formList.find('li[data-media-id]').length;

            if(freemium == '1' && max && currentSelectionLength >= max){
                $('#freemiumAlert').modal('show');

                e.preventDefault();
                return false;
            } else if(max && currentSelectionLength >= max){
                $('#maxAlert').modal('show');

                e.preventDefault();
                return false;
            }
        });


        var mediaSeasonRecommended = formList.data('media-season-recommended');
        if(mediaSeasonRecommended){

            var selectedLiMedia = formList.find('li[data-media-id]');
            var seasons = [];

            selectedLiMedia.each(function(){
                var season = $(this).attr('data-media-season');
                seasons.push(season);
            });

            var uniqueSeasons = seasons.filter(function(elem, pos) {
                return seasons.indexOf(elem) == pos;
            });

            var info = formList.parents('.panel').find('.additional_info');


            if(uniqueSeasons.length < 4){
                info.removeClass('d-none');
            } else {
                info.addClass('d-none');
            }

        }
    });
};

var addMediaToModal = function(media){
    var container = $('#selectedMediaListModal .list');
    var html = container.html();
    var mediaTmpl = '<li data-id="'+media.id+'" data-media-season="'+media.season+'">' +
        '<button class="btn btn-secondary btn-sm unlink-media-btn">' +
        '<i class="fas fa-unlink"></i>' +
        '</button>' +
        '<img data-selected-media-id="'+media.id+'" class="thumbnail" src="' + media.img + '" />' +
        '</li>';
    container.html(html + mediaTmpl);

    container.find('.unlink-media-btn').on('click', function(){
        var img = $(this).parents('li').find('img[data-selected-media-id]');
        var mediaId = img.attr('data-selected-media-id');
        $(this).parents('li').remove();
        $('[data-media-id='+mediaId+']').removeClass('checked');
    });
};

var removeMediaToModal = function(media){
    var container = $('#selectedMediaListModal .list');
    container.find('[data-selected-media-id='+media.id+']').parents('li').remove();
};

var initDataTable = function(){
    var table = $('#media_gallery .table');
    var selectedMedia = $('#selectedMediaListModal .list').find('[data-selected-media-id]');

    finalListUrl = listUrl + '?show_uid_media=' + (showUIDMedia ? showUIDMedia : 0) + '&show_checked_item_first=' + (showCheckedItemsFirst ? getSelectMediaIds() : 0);



    table.DataTable({
        "lengthMenu": [[3, 5, 10, 20], [3, 5, 10, 20]],
        "bDestroy": true,
        "ordering": false,
        "processing": true,
        "serverSide": true,
        "autoWidth": false,
        "ajax": finalListUrl,
        "columns": [
            {
                "width": "10px",
                "data": "checked",
                "className": "checked-column"
            },
            {
                "width": "10px",
                "data": "img"
            },
            { "data": "title" },
            { "width": "80px", "data": "date" },
            { "width": "310px", "data": "season" },
            { "width": "10px", "data": "removeBtn" }
        ],
        "language" : {
            "sProcessing":     table.data('sprocessing'),
            "sSearch":         table.data('ssearch'),
            "sLengthMenu":     table.data('slengthmenu'), //"Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo":           table.data('sinfo'), //"Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty":      table.data('sinfoempty'), //"Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            "sInfoFiltered":   table.data('sinfofiltered'), //"(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix":    "",
            "sLoadingRecords": table.data('sloadingrecords'), //"Chargement en cours...",
            "sZeroRecords":    table.data('szerorecords'), //"Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable":     table.data('semptytable'), //"Aucune donn&eacute;e disponible dans le tableau",
            "oPaginate": {
                "sFirst":      table.data('sfirst'), //"Premier",
                "sPrevious":   table.data('sprevious'), //"Pr&eacute;c&eacute;dent",
                "sNext":       table.data('snext'), //"Suivant",
                "sLast":       table.data('slast') //"Dernier"
            }
        },
        "initComplete": function(settings, json) {

        }
    }).on( 'draw.dt', function () {

        var container = $('#selectedMediaListModal .list');
        var selectedMedia = container.find('[data-selected-media-id]');

        selectedMedia.each(function(){
            var id = $(this).attr('data-selected-media-id');
            $('[data-media-id="'+id+'"]').addClass('checked');
        });

        $('[data-toggle="tooltip"]').tooltip();
    } );
};

var loadList  = function(){
    var inputName = $('#addMediaModal').attr('data-input-name');
    var inputCurrent = $('[name="'+ inputName +'"]').val();
    if(inputCurrent && inputCurrent != 0){
        $.ajax({
            url: currentSelectedMediaUrl + "?ids=" + inputCurrent,
            cache: false,
            dataType: 'json'
        }).done(function( data ) {
            data.forEach(function(media){
                addMediaToModal(media);
            });
            initDataTable();
        });
    } else {
        initDataTable();
    }
};

var myDropzone;
var showUIDMedia = 0;
var showCheckedItemsFirst = 0;
var showUIDBtnInitialized = false;

detectVerticalSquash = function(img) {
    var alpha, canvas, ctx, data, ey, ih, iw, py, ratio, sy;
    iw = img.naturalWidth;
    ih = img.naturalHeight;
    canvas = document.createElement("canvas");
    canvas.width = 1;
    canvas.height = ih;
    ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);
    data = ctx.getImageData(0, 0, 1, ih).data;
    sy = 0;
    ey = ih;
    py = ih;
    while (py > sy) {
        alpha = data[(py - 1) * 4 + 3];
        if (alpha === 0) {
            ey = py;
        } else {
            sy = py;
        }
        py = (ey + sy) >> 1;
    }
    ratio = py / ih;
    if (ratio === 0) {
        return 1;
    } else {
        return ratio;
    }
};

drawImageIOSFix = function(o, ctx, img, sx, sy, sw, sh, dx, dy, dw, dh) {

    // console.log(o);
    var vertSquashRatio;
    vertSquashRatio = detectVerticalSquash(img);

    dh = dh / vertSquashRatio;
    ctx.translate( dx+dw/2, dy+dh/2 );
    ctx.rotate(-o*Math.PI/180);
    dx = -dw/2;
    dy = -dh/2;

    return ctx.drawImage(img, sx, sy, sw, sh, dx, dy, dw, dh / vertSquashRatio);
};

$(function() {

    $(document).on('keyup', 'input[type=search]', function(){
        if(showCheckedItemsFirst){
            $('#show_checked_items_first').click();
        }
    });

    $('#show_uid_media').click(function(){

        if($(this).prop('checked')){
            showUIDMedia = $(this).val();
        } else {
            showUIDMedia = 0;
        }

        /**
         * Reload list with uid media option
         */
        if(showUIDBtnInitialized){
            var dataTable = $('#media_gallery .table').DataTable();
            var selectedMedia = $('#selectedMediaListModal .list').find('[data-selected-media-id]');

            finalListUrl = listUrl + '?show_uid_media=' + (showUIDMedia ? showUIDMedia : 0) + '&show_checked_items_first=' + (showCheckedItemsFirst ? getSelectMediaIds() : 0);

            dataTable.ajax.url(finalListUrl);
            dataTable.ajax.reload();
        }
    }).click().click();

    $('#show_checked_items_first').click(function(){

        if($(this).prop('checked')){
            $('input[type=search]').val('').keyup();
            showCheckedItemsFirst = 1;
        } else {
            showCheckedItemsFirst = 0;
        }

        /**
         * Reload list with uid media option
         */
        if(showUIDBtnInitialized){
            var dataTable = $('#media_gallery .table').DataTable();
            var selectedMedia = $('#selectedMediaListModal .list').find('[data-selected-media-id]');

            finalListUrl = listUrl + '?show_uid_media=' + (showUIDMedia ? showUIDMedia : 0) + '&show_checked_items_first=' + (showCheckedItemsFirst ? getSelectMediaIds() : 0);

            dataTable.ajax.url(finalListUrl);
            dataTable.ajax.reload();
        }
    }).click().click();

    showUIDBtnInitialized = true;

    $(document).on('click', '.btn-unlink-action', function(){
        var mediaId = $(this).data('media-id');

        var input = $(this).parents('.form-group').find('.media-input');

        var currentInputValue = input.val();
        var currentInputValueArray = currentInputValue.split(',');

        var newInputValueArray = currentInputValueArray.filter(function(item) {
            return item != mediaId
        });

        input.val(newInputValueArray.join()).change();
    });

    Dropzone.prototype.accept = function(file, done) {
        if (file.size > this.options.maxFilesize * 1024 * 1024) {
            return done(this.options.dictFileTooBig.replace("{{filesize}}", Math.round(file.size / 10.24 / 100) ).replace("{{maxFilesize}}", Math.round(this.options.maxFilesize * 1024)));
        } else if (!Dropzone.isValidFile(file, this.options.acceptedFiles)) {
            return done(this.options.dictInvalidFileType);
        } else if ((this.options.maxFiles != null) && this.getAcceptedFiles().length >= this.options.maxFiles) {
            done(this.options.dictMaxFilesExceeded.replace("{{maxFiles}}", this.options.maxFiles));
            return this.emit("maxfilesexceeded", file);
        } else {
            return this.options.accept.call(this, file, done);
        }
    };

    Dropzone.prototype.createThumbnailFromUrl = function(file, imageUrl, callback, crossOrigin) {
        var img;
        img = document.createElement("img");
        if (crossOrigin) {
            img.crossOrigin = crossOrigin;
        }
        img.onload = (function(_this) {

            return function() {
                var orientation = 0;
                EXIF.getData(img, function() {
                    switch(parseInt(EXIF.getTag(this, "Orientation"))){
                        case 3:
                            orientation = 180;
                            break;
                        case 6:
                            orientation = -90;
                            break;
                        case 8:
                            orientation = 90;
                            break;
                    }
                });

                var canvas, ctx, resizeInfo, thumbnail, _ref, _ref1, _ref2, _ref3;
                file.width = img.width;
                file.height = img.height;
                resizeInfo = _this.options.resize.call(_this, file);
                if (resizeInfo.trgWidth == null) {
                    resizeInfo.trgWidth = resizeInfo.optWidth;
                }
                if (resizeInfo.trgHeight == null) {
                    resizeInfo.trgHeight = resizeInfo.optHeight;
                }
                canvas = document.createElement("canvas");
                ctx = canvas.getContext("2d");
                canvas.width = resizeInfo.trgWidth;
                canvas.height = resizeInfo.trgHeight;
                drawImageIOSFix(orientation, ctx, img, (_ref = resizeInfo.srcX) != null ? _ref : 0, (_ref1 = resizeInfo.srcY) != null ? _ref1 : 0, resizeInfo.srcWidth, resizeInfo.srcHeight, (_ref2 = resizeInfo.trgX) != null ? _ref2 : 0, (_ref3 = resizeInfo.trgY) != null ? _ref3 : 0, resizeInfo.trgWidth, resizeInfo.trgHeight);
                thumbnail = canvas.toDataURL("image/png");
                _this.emit("thumbnail", file, thumbnail);
                if (callback != null) {
                    return callback();
                }
            };
        })(this);
        if (callback != null) {
            img.onerror = callback;
        }
        return img.src = imageUrl;
    };

    document.processedFiles = 0;

    // Dropzone class:
    if(typeof myDropzone == 'undefined') myDropzone = new Dropzone("#dropzone-media-form", {
        url: uploadUrl,
        maxFilesize: uploadMaxSizeMb,
        // autoQueue: false,
        // uploadMultiple: true,detectVerticalSquash
        // createImageThumbnails: false,
        dictDefaultMessage: uploadMsg,
        dictFileTooBig: dictFileTooBig,
        init: function(){
            this.on('resetFiles', function() {
                if(this.files.length != 0){
                    for(i=0; i<this.files.length; i++){
                        this.files[i].previewElement.remove();
                    }
                    this.files.length = 0;
                }

                $('#dropzone-media-form').removeClass('dz-started');
            });

            var dropzone = this;

            this.on("addedfile", function(file) {
                var max = $("#addMediaModal").attr('data-max-gallery-images');
                var currentSelectionLength = $('#selectedMediaListModal .list > li').length;

                if (max && currentSelectionLength >= max) {
                    dropzone.removeFile(file);
                    $('#maxAlert').modal('show');
                }
            })

            this.on('error', function(file, response) {
                $(file.previewElement).find('.dz-error-message').html(response);

                $('#errorAlertContent').html(response);
                $('#errorAlert').modal('show');
            });
        }
    });

    // myDropzone.on('addedfile', function(file) {
    //     var self = this;
    //     window.loadImage.parseMetaData(file, function (data) {
    //         // use embedded thumbnail if exists.
    //         if (data.exif) {
    //             var thumbnail = data.exif.get('Thumbnail');
    //             var orientation = data.exif.get('Orientation');
    //             if (thumbnail && orientation) {
    //                 window.loadImage(thumbnail, function (img) {
    //                     self.emit('thumbnail', file, img.toDataURL());
    //                 }, { orientation: orientation });
    //                 return;
    //             }
    //         }
    //         // use default implementation for PNG, etc.
    //         self.createThumbnail(file);
    //     });
    // });

    myDropzone.on("drop", function() {
        return false;
    });

    myDropzone.on("processing", function(file) {
        $('.ajax-spinner').show();
    });

    myDropzone.on("success", function(file) {
        document.processedFiles++;
    });

    myDropzone.on("queuecomplete", function(file) {


        if(showCheckedItemsFirst){
            jQuery('#show_checked_items_first').click().hide();
            jQuery('[for=show_checked_items_first]').hide();
        }

        var dataTable = $('#media_gallery .table').DataTable();
        var inputName = $('#addMediaModal').attr('data-input-name');
        var selectedMedia = $('#selectedMediaListModal .list').find('[data-selected-media-id]');

        /**
         * Set upload count to refresh list ajax url
         */

        finalListUrl = listUrl + '?show_uid_media=' + (showUIDMedia ? showUIDMedia : 0) + '&show_checked_items_first=' + (showCheckedItemsFirst ? getSelectMediaIds() : 0);

        dataTable.ajax.url(finalListUrl + '&uploadCount=' + document.processedFiles);
        dataTable.ajax.reload(function(data){

            var uploadedMedia = data['uploadedMedia'].slice(0,document.processedFiles);


            uploadedMedia.forEach(function(media){
                var rowElement = $('#media_gallery .table tbody tr[data-media-id='+media.id+']');

                if(rowElement.length){
                    /**
                     * Item on first page
                     */
                    rowElement.click();
                } else {
                    /**
                     * Item should be on next pages... but inserted in page 1 / hidden
                     */
                    $('#media_gallery .table tbody tr:last-child').after('<tr style="display:none;" data-media-id="'+media.id+'" data-media-img="'+media.img+'"><td>TOTO</td></tr>');
                    $('#media_gallery .table tbody tr[data-media-id='+media.id+']').click();
                }
            });

            document.processedFiles = 0;
        });
    });

    $('#mediaModalSaveBtn').click(function(e){

        var inputName = $('#addMediaModal').attr('data-input-name');
        var mediaSeason = $('#addMediaModal').attr('data-media-season');
        var checkedMedia = [];
        var seasons = [];
        var container = $('#selectedMediaListModal .list');
        var selectedMedia = container.find('[data-selected-media-id]');
        var selectedLiMedia = container.find('li[data-id]');

        selectedLiMedia.each(function(){
            var season = $(this).attr('data-media-season');
            seasons.push(season);
        });

        var uniqueSeasons = seasons.filter(function(elem, pos) {
            return seasons.indexOf(elem) == pos;
        });

        if(mediaSeason && mediaSeason == "1" && seasons.length != uniqueSeasons.length){
            e.stopImmediatePropagation();
            $('#seasonAlert').modal('show');
        } else {
            selectedMedia.each(function(){
                var id = $(this).attr('data-selected-media-id');
                checkedMedia.push(id);
            });

            $('[name="'+ inputName +'"]').val(checkedMedia.join()).change();
        }
    });

    $( ".media-form-list" ).each(function(){
        refreshFormList($(this));
    });

    $('.media-input').change(function(){
        var formListElement = $('.media-form-list[data-input-name='+$(this).attr('name')+']');
        refreshFormList(formListElement);
    });

    $('#addMediaModal').on('show.bs.modal', function (event) {

        var button = $(event.relatedTarget);
        var inputName = button.attr('data-input-name');
        var mediaGallery = button.attr('data-media-gallery');
        var mediaSeason = button.attr('data-media-season');
        var maxGalleryImages = button.attr('data-max-gallery-images');
        var maxMsg = button.attr('data-max-msg');

        $(this).attr('data-input-name', inputName);
        $(this).attr('data-media-gallery', mediaGallery);
        $(this).attr('data-media-season', mediaSeason);
        $(this).attr('data-max-gallery-images', maxGalleryImages);

        $(this).find('.modal-title .max').html('&nbsp;&nbsp;&nbsp;<strong>' + maxMsg + '</strong>');

        $('#selectedMediaListModal .list').html('');

        myDropzone.emit("resetFiles");

        loadList();
    });

    $( document ).on('click', 'tr[data-media-id]', function(e){

        var max = $("#addMediaModal").attr('data-max-gallery-images');
        var currentSelectionLength = $('#selectedMediaListModal .list > li').length;

        if(max != '' && currentSelectionLength >= max){
            $('#maxAlert').modal('show');

            e.preventDefault();
            return false;
        }

        if($("#addMediaModal").attr('data-media-gallery') == 0){
            $('[data-media-id]').removeClass('checked');
            $('#selectedMediaListModal .list').html('');
        }

        $(this).toggleClass('checked');

        var media = {
            id: $(this).attr('data-media-id'),
            img: $(this).attr('data-media-img'),
            season: $(this).attr('data-media-season')
        };

        if($(this).hasClass('checked')){
            addMediaToModal(media);
        } else {
            removeMediaToModal(media);
        }

        // max gallery
        if(max > 0){
            var currentSelectionLength = $('#selectedMediaListModal .list > li').length;

            if(currentSelectionLength > max){
                selectionCollection.first().find(".glyphicon-remove").click();
            }
        }
    });

    $( document ).on('click', '#media_gallery a.remove-media-ajax', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        if(!$(this).is('[disabled]')){

            $('#removeMediaModal').modal();

            var mediaTr = $(this).parents('tr[data-media-id]');
            var mediaId = mediaTr.attr('data-media-id');

            $('#removeMediaModalBtn').attr('href', $(this).attr('data-href')).attr('data-media-id-to-remove', mediaId);
        } else {
            return false;
        }
    });

    $( document ).on('click', '#removeMediaModalBtn', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        $('#removeMediaModal').modal('hide');

        var mediaId = $(this).attr('data-media-id-to-remove');
        var media = {
            id: mediaId
        };

        $.ajax({
            url: $(this).attr('href'),
            cache: false
        }).done(function( html ) {
            $('#media_gallery .table').DataTable().ajax.reload(null, false);
            removeMediaToModal(media);
        });
    });

    $(document).on('submit', '.editMediaModalContent form',  function (event) {
        event.preventDefault();

        var form = $(this);

        // Create an FormData object
        var data = new FormData(form[0]);

        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            enctype: 'multipart/form-data',
            dataType: "json",
            data: data,
            processData: false,
            contentType: false,
            success: function(data) {
                if(data.status == 0){
                    $('.editMediaModalContent').html(data.content);
                    parseCrop();
                } else {
                    $('#editMediaModal').modal('hide');

                    var mediaId = form.find('[name=id]').val();
                    var d = new Date();

                    $('[data-media-id='+ mediaId +'] img').attr('src', data.url + '?' + d.getTime());
                    $('.invalid-media[data-media-id='+ mediaId +']').removeClass('invalid-media');

                    $('[data-media-id='+ mediaId +']').attr('data-media-season', data.season);

                    checkFormCanBeSubmit();
                }
            }
        });
    });

    $('#editMediaModalSaveBtn').click(function(){
        var form = $('.editMediaModalContent form');

        form.find(':submit').click();
    });

    $(document).on('show.bs.modal', '#editMediaModal',  function (event) {
        $( ".editMediaModalContent" ).html('');
    }).on('hidden.bs.modal', '#editMediaModal',  function (event) {
        $( ".editMediaModalContent" ).html('');
    }).on('shown.bs.modal', '#editMediaModal', function (event) {

        var button = $(event.relatedTarget);
        var mediaId = button.attr('data-media-id');

        $.ajax({
            url: mediaFormAction + '?id=' + mediaId,
            cache: false,
            dataType: "json",
        }).done(function( data ) {
            $( ".editMediaModalContent" ).html( data.content );
            parseCrop();
        });
    });

    $( document ).on('click', '.season-switch button', function(e){
        e.stopImmediatePropagation();
        var switchElement = $(this).parent();
        switchElement.find('button').removeClass('btn-primary').addClass('btn-secondary');
        $(this).removeClass('btn-secondary').addClass('btn-primary');

        var url = switchElement.data('url');
        var id = switchElement.data('id');
        var season = $(this).data('id');

        $('#selectedMediaListModal .list > li[data-id='+id+']').attr('data-media-season', season);

        $.post(url, {id: id, season: season });
    });
});