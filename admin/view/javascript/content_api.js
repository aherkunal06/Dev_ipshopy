
$(document).ready(function () {
    $('#login_auth2').on('click', function (e) {
        console.log(this);

        url = this.dataset.url;
        console.log(url);
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: '1',

            beforeSend: function () { },
            success: function (data) {
                console.log(data);

                if (data['href']) {
                    location.href = data['href'];
                }

                if (data['success']) {

                    $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + data['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }

                if (data['error']) {


                    $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + data['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }


            },
            complete: function () { },

            error: function (xhr, ajaxOptions, thrownError) {

                console.warn(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });

    });
    $('#delete_products').on('click', function (e) {
        console.log(this);
        element = $(this)

        url = this.dataset.url;
        console.log(url);
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: '1',

            beforeSend: function () {
                $(element).addClass("loading");
            },

            complete: function () {
                $(element).removeClass('loading');
            },
            success: function (data) {
                console.log(data);

                if (data['href']) {
                    location.href = data['href'];
                }
                if (data['success']) {

                    element.parent().prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + data['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }

                if (data['error']) {

                    element.parent().prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i>' + data['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {

                console.warn(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });

    $('#load_products').on('click', function (e) {
        console.log(this);
        element = $(this)

        url = this.dataset.url;
        console.log(url);
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: '1',

            beforeSend: function () {
                $(element).addClass("loading");
            },

            complete: function () {
                $(element).removeClass('loading');
            },
            success: function (data) {
                console.log(data);

                if (data['href']) {
                    location.href = data['href'];
                }


                if (data['success']) {

                    element.parent().prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + data['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }

                if (data['error']) {

                    element.parent().prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i>' + data['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }

            },


            error: function (xhr, ajaxOptions, thrownError) {

                console.warn(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });

    });
    $('#clear_logs').on('click', function (e) {
        console.log(this);
        element = $(this)

        url = this.dataset.url;
        console.log(url);
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data: '1',

            beforeSend: function () {
                $(element).addClass("loading");
            },

            complete: function () {
                $(element).removeClass('loading');
            },
            success: function (data) {
                console.log(data);

                if (data['href']) {
                    location.href = data['href'];
                }


                if (data['success']) {

                    $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + data['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }

                if (data['error']) {

                    $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i>' + data['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                }
                location.reload();
            },


            error: function (xhr, ajaxOptions, thrownError) {

                console.warn(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });

    });


    $('#button-upload').on('click', function () {
        var element = this;
        url = this.dataset.url;
        console.log(url);
        if (!$('button-upload').prop('disabled')) {
            $('#form-upload').remove();
            $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" accept=".json"/></form>');
            $('#form-upload input[name=\'file\']').trigger('click');
            $('#form-upload input[name=\'file\']').on('change', function () {
                console.log(this.files);
                if ((this.files[0].size / 1024) > 209715200) {
                    $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i>' + 'Warning: The uploaded file exceeds the max file size: 200 megabytes!' + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                    $(this).val('');
                }
            });


            if (typeof timer != 'undefined') {
                clearInterval(timer);
            }

            timer = setInterval(function () {
                if ($('#form-upload input[name=\'file\']').val() != '') {
                    clearInterval(timer);
                    $.ajax({
                        url: url,
                        type: 'post', data: new FormData($('#form-upload')[0]),
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {

                        },
                        complete: function () {

                        },
                        success: function (data) {


                            if (data['success']) {

                                $('#alert').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + data['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                            }

                            if (data['error']) {

                                $('#alert').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i>' + data['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                            }
                            if (data['data']) {

                                element.nextElementSibling.textContent = data['data'];
                                
                                $(element.nextElementSibling).removeClass('text-danger')
                                $(element.nextElementSibling).addClass('text-success')

                                //     < span class="text-success" >
                                //         client_secret.json is  uploaded!
								// </span >

                              //  $(element).parent().find('span.text-success').text() = data['data'];
                            }


                        },
                        error: function (xhr, ajaxOptions, thrownError) {

                            console.warn(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });
                }
            }, 500);
        }
    });

    $('input[name="merchant_id"]').on('keyup', function () {
        text = ""
        for (i = 0; i < this.value.length; i++) {
            val = Number((this.value[i]))
            if (!isNaN(val)) {
                text += this.value[i];
            }

        }
        text=text.slice(0,20);
        this.value = text
    });

    $('input[name="prefixOfferId"]').on('keyup', function () {
        text = ""
        for (i = 0; i < this.value.length; i++) {
            var username=/^[a-zA-Z0-9_]+$/.test(this.value[i]);

            if (username){
                text += this.value[i];
            }
        }
        text=text.slice(0,15);

        this.value = text

    });

    $('#accordion-button').on('click', function () {

        setTimeout(() => {
            var el = document.getElementById('accordion-body');
            if (el.scrollHeight > 0) {
                el.scrollTop = el.scrollHeight;
            } 
        }, 200);

  
    });

});

