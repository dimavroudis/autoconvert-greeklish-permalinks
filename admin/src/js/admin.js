
(function ($) {

    /**
     * Initialize Select2
     */
    $('.select2').select2();

    /**
     * Run js based on url
     */
    var urlParams = new URLSearchParams(window.location.search);
    var tab = urlParams.get('tab');

    if (tab === 'generate_permalinks') {
        converterTab();
    } else if (tab === 'permalink_settings') {
        settingsTab();
    }

    /**
    * Initialize Converter tab
    */
    function converterTab() {
        var selectPosts = $("#selectPosts");
        var selectTaxonomies = $("#selectTaxonomies");
        var messageOutput = $("#messageOutput");

        //Select All functionality
        $("#selectAllPosts").click(function (e) {
            e.preventDefault();
            $("#selectPosts > option").prop("selected", "selected");
            selectPosts.trigger("change");
        });
        $("#selectAllTaxonomies").click(function (e) {
            e.preventDefault();
            $("#selectTaxonomies > option").prop("selected", "selected");
            selectTaxonomies.trigger("change");
        });

        //Conversion Form
        $('#converterForm #submit').click(function (e) {
            var post_types = selectPosts.val() ? selectPosts.val() : [];
            var taxonomies = selectTaxonomies.val() ? selectTaxonomies.val() : [];
            var limit = 100;
            var total_posts_converted = 0, total_terms_converted = 0;

            //Run initial check
            $.ajax({
                url: AgpSettings.endpoints.check,
                method: 'POST',
                dataType: 'json',
                beforeSend: function (xhr) {
                    messageOutput.hide();
                    $('#converterForm input, #converterForm select, #converterForm button').prop('disabled', true);
                    xhr.setRequestHeader('X-WP-Nonce', AgpSettings.nonce);
                },
                data: {
                    post_types,
                    taxonomies
                },
            }).done(function (checkResponse) {
                //If pots or terms found, run conversion
                if (checkResponse.data.posts + checkResponse.data.terms > 0) {
                    var convertPermalinks = () => {
                        $.ajax({
                            url: AgpSettings.endpoints.convert,
                            method: 'POST',
                            dataType: 'json',
                            beforeSend: function (xhr) {
                                showMessage(AgpSettings.messages.converting, '', total_posts_converted, checkResponse.data.posts, total_terms_converted, checkResponse.data.terms)
                                xhr.setRequestHeader('X-WP-Nonce', AgpSettings.nonce);
                            },
                            data: {
                                post_types,
                                taxonomies,
                                limit
                            },
                        }).done(function (response) {
                            //If posts or terms converted, run again
                            if (response.data.posts + response.data.terms > 0) {
                                total_posts_converted += response.data.posts;
                                total_terms_converted += response.data.terms;
                                convertPermalinks();
                            } else {
                                showMessage(AgpSettings.messages.success, 'success', total_posts_converted, total_terms_converted)
                                $('#converterForm input, #converterForm select, #converterForm button').prop('disabled', false);
                            }
                        }).fail(function (err) {
                            showMessage(err.responseJSON.message, 'error');
                            $('#converterForm input, #converterForm select, #converterForm button').prop('disabled', false);
                        })
                    }
                    convertPermalinks()
                } else {
                    showMessage(checkResponse.message, 'success');
                    $('#converterForm input, #converterForm select, #converterForm button').prop('disabled', false);
                }
            }).fail(function (err) {
                showMessage(err.responseJSON.message, 'error')
                $('#converterForm input, #converterForm select, #converterForm button').prop('disabled', false);
            })
        })


        function showMessage(message, type, ...args) {
            if (type === 'error') {
                messageOutput.attr("class", "error inline")
            } else if (type === 'success') {
                messageOutput.attr("class", "updated inline")
            } else {
                messageOutput.attr("class", "notice")
            }
            const messageElement = document.createElement('p');
            $(messageElement).text(formatString(message, ...args));
            messageOutput.html(messageElement);
            messageOutput.show();
        }

        function formatString(format) {
            var args = Array.prototype.slice.call(arguments, 1);
            return format.replace(/{(\d+)}/g, function (match, number) {
                return typeof args[number] !== 'undefined' ? args[number] : match;
            });
        };
    }

    /**
     * Initialize Settings tab
     */
    function settingsTab() {
        var automaticSwitch = document.getElementById("agpAutomatic");
        var automaticOptions = [document.getElementById("selectPosts"), document.getElementById("selectTaxonomies")];

        toggleOptions();
        automaticSwitch.addEventListener('click', function () {
            toggleOptions();
        });


        function toggleOptions() {
            var customAutoSection = document.getElementById('agp_custom_automatic_options');
            if (!automaticSwitch.checked) {
                customAutoSection.style.display = 'none';
                automaticOptions.forEach(function (element) {
                    element.disabled = true;
                });
            } else {
                customAutoSection.style.display = 'block';
                automaticOptions.forEach(function (element) {
                    element.disabled = false;
                });
            }
        }
        automaticOptions.forEach(function (element) {
            $(element).on('select2:select', function (e) {
                var data = e.params.data;
                if (data.id === 'all_options' || data.id === 'no_options') {
                    $(element).val(data.id).trigger('change');
                } else {
                    $(element).find('option[value="all_options"]').prop('selected', false);
                    $(element).find('option[value="no_options"]').prop('selected', false);
                    $(element).trigger('change');
                }
            });
        });
    }

})(jQuery);