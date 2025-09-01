import $ from 'jquery';
window.$ = $;
window.jQuery = $;

$(function() {

    'use strict';

    const OLX = {

        /**
         * Init
         */
        init: function () {

            this.install = this.install(this)


        },

        /**
         * Install
         */
        install: function () {

            $(document).on( 'click', '.checkLink', this.check_link)
            $(document).on( 'submit', '.subscribeStoreForm', this.store)

        },

        check_link: function(e)
        {

            e.preventDefault()

            let $this = $(this),
                $form = $this.closest('form'),
                url = $this.data('url'),
                val = $this.closest('.input-group').find('[name="url"]').val(),
                $preloader = $form.find('.preloader'),
                $content = $form.find('.content')

            $.ajax({
                beforeSend: function(xhr)
                {

                    $this.attr('disabled', true)
                    $preloader.removeClass('d-none')
                    $content.html('')
                    $form.find('.alert-success').addClass('visually-hidden')
                    $('.alert-email-success').remove()

                },
                data: {
                  url: val
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                complete: function()
                {

                    $this.attr('disabled', false)
                    $preloader.addClass('d-none')

                },
                error: function(response)
                {

                    const err = JSON.parse(response.responseText)

                    if (err.hasOwnProperty("errors"))
                    {

                        let errorList = '';

                        $.each(err.errors, function(key, value) {
                            $.each(value, function(i, errorMessage){
                                errorList += '<li class="txt-light">'+errorMessage+'</li>'
                            });
                        });

                        $form.find('.alert-danger').removeClass('visually-hidden').html('<ul class="mb-0">'+errorList+'</ul>')
                    }

                    if (err.hasOwnProperty("message"))
                    {
                        $form.find('.alert-danger').removeClass('visually-hidden').html(err.message)
                    }

                },
                success: function(response)
                {

                    $content.html(response.data.html)

                },
                url: url
            });

        },

        store: function(e)
        {

            e.preventDefault()

            let $this = $(this),
                url = $this.attr('action'),
                $content = $this.find('.content')

            $.ajax({
                beforeSend: function(xhr)
                {

                    $this.find('.alert-danger').addClass('visually-hidden')
                    $this.find('.alert-success').addClass('visually-hidden')
                    $this.addClass('preload')

                },
                data: $this.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                complete: function()
                {

                    $this.removeClass('preload')

                },
                error: function(response)
                {

                    const err = JSON.parse(response.responseText)

                    if (err.hasOwnProperty("errors"))
                    {

                        let errorList = '';

                        $.each(err.errors, function(key, value) {
                            $.each(value, function(i, errorMessage){
                                errorList += '<li class="txt-light">'+errorMessage+'</li>'
                            });
                        });

                        $this.find('.alert-danger').removeClass('visually-hidden').html('<ul class="mb-0">'+errorList+'</ul>')
                    }

                    if (err.hasOwnProperty("message"))
                    {
                        $this.find('.alert-danger').removeClass('visually-hidden').html(err.message)
                    }

                },
                success: function(response)
                {

                    $this.find('.alert-success').removeClass('visually-hidden').html(response.message)
                    $content.html('')
                    $this.trigger('reset')
                    $this.find('input[name="url"]').val('')

                },
                url: url
            });

        },




    }

    OLX.init()

});
