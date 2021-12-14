(function ($) {
    'use strict';
    window.booklyCustomerCabinet = function (Options) {
        let $container = $('.' + Options.form_id);
        if (!$container.length) {
            return;
        }
        let $tabs = $('.bookly-js-tabs li a', $container),
            sections_ready = [];

        // Appointments section
        function initAppointments($container) {
            var $appointments_table = $('.bookly-appointments-list', $container),
                $reschedule_dialog = $('#bookly-customer-cabinet-reschedule-dialog', $container),
                $reschedule_date = $('#bookly-reschedule-date', $reschedule_dialog),
                $reschedule_time = $('#bookly-reschedule-time', $reschedule_dialog),
                $reschedule_error = $('#bookly-reschedule-error', $reschedule_dialog),
                $reschedule_save = $('#bookly-save', $reschedule_dialog),
                $cancel_dialog = $('#bookly-customer-cabinet-cancel-dialog', $container),
                $cancel_button = $('#bookly-yes', $cancel_dialog),
                $cancel_reason = $('#bookly-cancel-reason', $cancel_dialog),
                $cancel_reason_error = $('#bookly-cancel-reason-error', $cancel_dialog),
                appointments_columns = [],
                $appointmentDateFilter = $('#bookly-filter-date'),
                $staffFilter = $('#bookly-filter-staff'),
                $serviceFilter = $('#bookly-filter-service'),
                row;

            Object.keys(Options.appointment_columns).map(function(objectKey) {
                let column = Options.appointment_columns[objectKey];
                switch (column) {
                    case 'date':
                        appointments_columns.push({data: 'start_date', responsivePriority: 1});
                        break;
                    case 'location':
                        appointments_columns.push({data: 'location', responsivePriority: 4, render: $.fn.dataTable.render.text()});
                        break;
                    case 'service':
                        appointments_columns.push({
                            data: 'service_title', responsivePriority: 3, render: function (data, type, row, meta) {
                                return data.split('<br/>').map(function (item) {
                                    return $.fn.dataTable.render.text().display(item);
                                }).join('<br/>');
                            }
                        });
                        break;
                    case 'staff':
                        appointments_columns.push({data: 'staff_name', responsivePriority: 3, render: $.fn.dataTable.render.text()});
                        break;
                    case 'status':
                        appointments_columns.push({data: 'status', responsivePriority: 3, render: $.fn.dataTable.render.text()});
                        break;
                    case 'category':
                        appointments_columns.push({data: 'category', responsivePriority: 4, render: $.fn.dataTable.render.text()});
                        break;
                    case 'online_meeting':
                        appointments_columns.push({
                            data: 'online_meeting_provider',
                            render: function (data, type, row, meta) {
                                switch (data) {
                                    case 'zoom':
                                        return '<a class="badge badge-primary" href="' + $.fn.dataTable.render.text().display(row.online_meeting_url) + '" target="_blank"><i class="fas fa-video fa-fw"></i> Zoom <i class="fas fa-external-link-alt fa-fw"></i></a>';
                                    case 'google_meet':
                                        return '<a class="badge badge-primary" href="' + $.fn.dataTable.render.text().display(row.online_meeting_id) + '" target="_blank"><i class="fas fa-video fa-fw"></i> Google Meet <i class="fas fa-external-link-alt fa-fw"></i></a>';
                                    default:
                                        return '';
                                }
                            }
                        });
                        break;
                    case 'join_online_meeting':
                        appointments_columns.push({
                            data: 'online_meeting_provider',
                            render: function (data, type, row, meta) {
                                switch (data) {
                                    case 'zoom':
                                        return '<a class="badge badge-primary" href="' + $.fn.dataTable.render.text().display(row.join_online_meeting_url) + '" target="_blank"><i class="fas fa-video fa-fw"></i> Zoom <i class="fas fa-external-link-alt fa-fw"></i></a>';
                                    default:
                                        return '';
                                }
                            }
                        });
                        break;
                    case 'price':
                        appointments_columns.push({
                            data: 'price',
                            responsivePriority: 3,
                            render: function ( data, type, row, meta ) {
                                if (row.payment_id !== null) {
                                    return '<button type="button" class="btn btn-sm btn-default" data-action="show-payment" data-payment_id="' + row.payment_id + '">' + BooklyCustomerCabinetL10n.payment + '</button>';
                                }

                                return data;
                            }
                        });
                        break;
                    case 'cancel':
                        appointments_columns.push({
                            data              : 'ca_id',
                            render            : function (data, type, row, meta) {
                                switch (row.allow_cancel) {
                                    case 'expired' :
                                        return BooklyCustomerCabinetL10n.expired_appointment;
                                    case 'blank' :
                                        return '';
                                    case 'allow' :
                                        return '<button class="btn btn-sm btn-default" data-type="open-modal" data-target=".' + Options.form_id + ' #bookly-customer-cabinet-cancel-dialog">' + BooklyCustomerCabinetL10n.cancel + '</button>';
                                    case 'deny':
                                        return BooklyCustomerCabinetL10n.deny_cancel_appointment;
                                }
                            },
                            responsivePriority: 2,
                            orderable         : false
                        });
                        break;
                    case 'reschedule':
                        appointments_columns.push({
                            data              : 'ca_id',
                            render            : function (data, type, row, meta) {
                                switch (row.allow_reschedule) {
                                    case 'expired' :
                                        return BooklyCustomerCabinetL10n.expired_appointment;
                                    case 'blank' :
                                        return '';
                                    case 'allow' :
                                        return '<button class="btn btn-sm btn-default" data-type="open-modal" data-target=".' + Options.form_id + ' #bookly-customer-cabinet-reschedule-dialog">' + BooklyCustomerCabinetL10n.reschedule + '</button>';
                                    case 'deny':
                                        return BooklyCustomerCabinetL10n.deny_cancel_appointment;
                                }
                            },
                            responsivePriority: 2,
                            orderable         : false
                        });
                        break;
                    default:
                        if (column.match("^custom_field")) {
                            appointments_columns.push({data: 'custom_fields.' + column.substring(13), render: $.fn.dataTable.render.text(), responsivePriority: 3, orderable: false});
                        }
                        break;
                }
            });
            // Date range filter
            let pickerRanges = {};

            pickerRanges[BooklyCustomerCabinetL10n.dateRange.anyTime]   = [moment(), moment().add(100, 'years')];
            pickerRanges[BooklyCustomerCabinetL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            pickerRanges[BooklyCustomerCabinetL10n.dateRange.today]     = [moment(), moment()];
            pickerRanges[BooklyCustomerCabinetL10n.dateRange.tomorrow]  = [moment().add(1, 'days'), moment().add(1, 'days')];
            pickerRanges[BooklyCustomerCabinetL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
            pickerRanges[BooklyCustomerCabinetL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
            pickerRanges[BooklyCustomerCabinetL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
            pickerRanges[BooklyCustomerCabinetL10n.dateRange.nextMonth] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];
            if (BooklyCustomerCabinetL10n.tasks.enabled) {
                pickerRanges[BooklyCustomerCabinetL10n.tasks.title] = [moment(), moment().add(1, 'days')];
            }
            $appointmentDateFilter.daterangepicker(
                {
                    parentEl : $appointmentDateFilter.closest('div'),
                    startDate: moment(),
                    endDate  : moment().add(100, 'years'),
                    ranges   : pickerRanges,
                    showDropdowns  : true,
                    linkedCalendars: false,
                    autoUpdateInput: false,
                    locale: $.extend({},BooklyCustomerCabinetL10n.dateRange, BooklyCustomerCabinetL10n.datePicker)
                },
                function(start, end, label) {
                    switch (label) {
                        case BooklyCustomerCabinetL10n.tasks.title:
                            $appointmentDateFilter
                            .data('date', 'null')
                            .find('span')
                            .html(BooklyCustomerCabinetL10n.tasks.title);
                            break;
                        case BooklyCustomerCabinetL10n.dateRange.anyTime:
                            $appointmentDateFilter
                            .data('date', 'any')
                            .find('span')
                            .html(BooklyCustomerCabinetL10n.dateRange.anyTime);
                            break;
                        default:
                            $appointmentDateFilter
                            .data('date', start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'))
                            .find('span')
                            .html(start.format(BooklyCustomerCabinetL10n.dateRange.format) + ' - ' + end.format(BooklyCustomerCabinetL10n.dateRange.format));
                    }
                }
            ).data('date', 'any').find('span')
            .html(BooklyCustomerCabinetL10n.dateRange.anyTime);

            $appointmentDateFilter.on('apply.daterangepicker', function () {
                appointments_datatable.ajax.reload();
            });

            $staffFilter.on('change', function () {
                appointments_datatable.ajax.reload();
            })
            $serviceFilter.on('change', function () {
                appointments_datatable.ajax.reload();
            })

            $('.bookly-js-select')
            .val(null)
            .select2({
                width: '100%',
                theme: 'bootstrap4',
                dropdownParent: '#bookly-tbs',
                allowClear: true,
                placeholder: '',
                language: {
                    noResults: function() { return BooklyCustomerCabinetL10n.no_result_found; }
                },
            });

            /**
             * Init DataTables.
             */
            var appointments_datatable = $appointments_table.DataTable({
                order: [[0, 'desc']],
                info: false,
                lengthChange: false,
                pageLength: 10,
                pagingType: 'numbers',
                searching: false,
                processing: true,
                responsive: true,
                serverSide: true,
                ajax: {
                    url: Options.ajaxurl,
                    type: 'POST',
                    data: function (d) {
                        return $.extend({
                            action: 'bookly_customer_cabinet_get_appointments',
                            appointment_columns: Options.appointment_columns,
                            csrf_token: BooklyL10nGlobal.csrf_token,
                            date: $appointmentDateFilter.data('date'),
                            staff: $staffFilter.val(),
                            service: $serviceFilter.val(),
                        }, {
                            filter: {}
                        }, d);
                    }
                },
                columns: appointments_columns,
                dom: "<'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12'p>>",
                language: {
                    zeroRecords: BooklyCustomerCabinetL10n.zeroRecords,
                    processing: BooklyCustomerCabinetL10n.processing
                }
            });

            $appointments_table.on('click', 'button', function () {
                if ($(this).closest('tr').hasClass('child')) {
                    row = appointments_datatable.row($(this).closest('tr').prev().find('td:first-child'));
                } else {
                    row = appointments_datatable.row($(this).closest('td'));
                }
            });

            // Cancel appointment dialog
            $cancel_button.on('click', function () {
                if ($cancel_reason.length && $cancel_reason.val() === '') {
                    $cancel_reason_error.show();
                } else {
                    var ladda = Ladda.create(this);
                    ladda.start();
                    $.ajax({
                        url: Options.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'bookly_customer_cabinet_cancel_appointment',
                            csrf_token: BooklyL10nGlobal.csrf_token,
                            ca_id: row.data().ca_id,
                            reason: $cancel_reason.val()
                        },
                        dataType: 'json',
                        success: function (response) {
                            ladda.stop();
                            if (response.success) {
                                $cancel_dialog.booklyModal('hide');
                                appointments_datatable.ajax.reload();
                            } else {
                                booklyAlert({error: [BooklyCustomerCabinetL10n.errors.cancel]});
                            }
                        }
                    });
                }
            });

            // Reschedule appointment dialog
            $reschedule_date.daterangepicker({
                parentEl        : '#bookly-customer-cabinet-reschedule-dialog',
                singleDatePicker: true,
                showDropdowns   : true,
                autoUpdateInput : true,
                minDate         : moment().add(BooklyCustomerCabinetL10n.minDate, 'days'),
                maxDate         : moment().add(BooklyCustomerCabinetL10n.maxDate, 'days'),
                locale          : BooklyCustomerCabinetL10n.datePicker
            }).on('change', function () {
                $reschedule_save.prop('disabled', true);
                $reschedule_time.html('');
                $reschedule_error.hide();
                $.ajax({
                    url     : Options.ajaxurl,
                    type    : 'POST',
                    data    : {
                        action    : 'bookly_customer_cabinet_get_day_schedule',
                        csrf_token: BooklyL10nGlobal.csrf_token,
                        ca_id     : row.data().ca_id,
                        date      : moment($(this).val(), BooklyCustomerCabinetL10n.datePicker.format).format('DD-MM-YYYY')
                    },
                    dataType: 'json',
                    success : function (response) {
                        if (response.data.length) {
                            var time_options = response.data[0].options;
                            $.each(time_options, function (index, option) {
                                var $option = $('<option/>');
                                $option.text(option.title).val(option.value);
                                if (option.disabled) {
                                    $option.attr('disabled', 'disabled');
                                }
                                $reschedule_time.append($option);
                            });
                            $reschedule_save.prop('disabled', false);
                        } else {
                            $reschedule_error.text(BooklyCustomerCabinetL10n.noTimeslots).show();
                        }
                    }
                });
            });
            $reschedule_dialog.on('show.bs.modal', function (e) {
                let previous = $reschedule_date.data('daterangepicker').startDate.format('YYYY-MM-DD');
                $reschedule_date.data('daterangepicker').setStartDate(row.data().start_date);
                $reschedule_date.data('daterangepicker').setEndDate(row.data().start_date);
                if (previous === $reschedule_date.data('daterangepicker').startDate.format('YYYY-MM-DD')) {
                    // Even if the date hasn't changed, forcibly inform the object that it has been changed
                    $reschedule_date.trigger('change');
                }
            });
            $reschedule_save.on('click', function (e) {
                e.preventDefault();
                var ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    url     : Options.ajaxurl,
                    type    : 'POST',
                    data    : {
                        action    : 'bookly_customer_cabinet_save_reschedule',
                        csrf_token: BooklyL10nGlobal.csrf_token,
                        ca_id     : row.data().ca_id,
                        slot      : $reschedule_time.val(),
                    },
                    dataType: 'json',
                    success : function (response) {
                        ladda.stop();
                        if (response.success) {
                            $reschedule_dialog.booklyModal('hide');
                            appointments_datatable.ajax.reload();
                        } else {
                            booklyAlert({error: [BooklyCustomerCabinetL10n.errors.reschedule]});
                        }
                    }
                });
            });

            $appointments_table.on('click', '[data-action=show-payment]', function () {
                BooklyPaymentDetailsDialog.showDialog({
                    payment_id: row.data().payment_id
                });
            });
        }

        // Profile section
        function initProfile($container) {
            var $profile_content = $('.bookly-js-customer-cabinet-content-profile', $container),
                $form = $('form', $profile_content),
                $delete_btn = $('button.bookly-js-delete-profile', $profile_content),
                $delete_modal = $('.bookly-js-customer-cabinet-delete-dialog', $container),
                $delete_loading = $('.bookly-loading', $delete_modal),
                $approve_deleting = $('.bookly-js-approve-deleting', $delete_modal),
                $denied_deleting = $('.bookly-js-denied-deleting', $delete_modal),
                $confirm_delete_btn = $('.bookly-js-confirm-delete', $delete_modal),
                $phone_field = $('.bookly-js-user-phone-input', $profile_content),
                $save_btn = $('button.bookly-js-save-profile', $profile_content);
            if (Options.intlTelInput.enabled) {
                $phone_field.intlTelInput({
                    preferredCountries: [Options.intlTelInput.country],
                    initialCountry: Options.intlTelInput.country,
                    geoIpLookup: function (callback) {
                        $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                            var countryCode = (resp && resp.country) ? resp.country : '';
                            callback(countryCode);
                        });
                    },
                    utilsScript: Options.intlTelInput.utils
                });
            }
            $save_btn.on('click', function (e) {
                e.preventDefault();
                var ladda = Ladda.create(this);
                ladda.start();
                $('.is-invalid', $profile_content).removeClass('is-invalid');
                $('.form-group .bookly-js-error').remove();
                var phone_number = $phone_field.val();
                try {
                    phone_number = Options.intlTelInput.enabled ? $phone_field.intlTelInput('getNumber') : $phone_field.val();
                    if (phone_number == '') {
                        phone_number = $phone_field.val();
                    }
                } catch (error) {}
                $phone_field.val(phone_number);

                var data = $form.serializeArray();
                data.push({name: 'action', value: 'bookly_customer_cabinet_save_profile'});
                data.push({name: 'csrf_token', value: BooklyL10nGlobal.csrf_token});
                data.push({name: 'columns', value: Options.profile_parameters});
                $.ajax({
                    url     : Options.ajaxurl,
                    type    : 'POST',
                    data    : data,
                    dataType: 'json',
                    success : function (response) {
                        if (response.success) {
                            booklyAlert({success: [BooklyCustomerCabinetL10n.profile_update_success]});
                            if ($('[name="current_password"]', $profile_content).val()) {
                                window.location.reload();
                            }
                        } else {
                            $.each(response.errors, function (name, value) {
                                if (name == 'info_fields') {
                                    $.each(value, function (id, text) {
                                        var $form_group = $('.form-group[data-id="customer_information_' + id + '"]', $profile_content);
                                        $form_group.find('.bookly-js-control-input').addClass('is-invalid');
                                        $form_group.append('<div class="bookly-js-error text-danger">' + text + '</div>');
                                    });
                                } else {
                                    var $form_group = $('.form-group [id="bookly_' + name + '"]', $profile_content).closest('.form-group');
                                    $form_group.find('.bookly-js-control-input').addClass('is-invalid');
                                    $form_group.append('<div class="bookly-js-error text-danger">' + value + '</div>');
                                }
                            });
                            $('html, body').animate({
                                scrollTop: $profile_content.find('.is-invalid').first().offset().top - 100
                            }, 1000);
                        }
                        ladda.stop();
                    }
                });
            });
            $delete_btn.on('click', function (e) {
                e.preventDefault();
                $approve_deleting.hide();
                $denied_deleting.hide();
                $delete_loading.show();
                $delete_modal.booklyModal('show');
                $.ajax({
                    url     : Options.ajaxurl,
                    type    : 'POST',
                    data    : {
                        action    : 'bookly_customer_cabinet_check_future_appointments',
                        csrf_token: BooklyL10nGlobal.csrf_token,
                    },
                    dataType: 'json',
                    success : function (response) {
                        $delete_loading.hide();
                        if (response.success) {
                            $approve_deleting.show();
                        } else {
                            $denied_deleting.show()
                        }
                    }
                });
            });
            $confirm_delete_btn.on('click', function (e) {
                e.preventDefault();
                var ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    url     : Options.ajaxurl,
                    type    : 'POST',
                    data    : {
                        action    : 'bookly_customer_cabinet_delete_profile',
                        csrf_token: BooklyL10nGlobal.csrf_token,
                    },
                    dataType: 'json',
                    success : function (response) {
                        ladda.stop();
                        if (response.success) {
                            $delete_modal.booklyModal('hide');
                            window.location.reload();
                        }
                    }
                });
            });
        }

        function initSection(section) {
            if ($.inArray(section, sections_ready) === -1) {
                switch (section) {
                    case 'appointments':
                        initAppointments($container);
                        sections_ready.push(section);
                        break;
                    case 'profile':
                        initProfile($container);
                        sections_ready.push(section);
                        break;
                }
            }
        }

        if (Options.tabs.length > 1) {
            // Tabs
            $tabs.on('click', function () {
                var section = $(this).attr('href').substring(1);
                $('.bookly-js-customer-cabinet-content', $container).hide();
                $('.bookly-js-customer-cabinet-content-' + section, $container).show();
                initSection(section);
            });
            $tabs.first().trigger('click');
        } else {
            var section = Options.tabs[0];
            $('.bookly-js-customer-cabinet-content', $container).show();
            initSection(section);
        }

        $container
            .on('click', '[data-type="open-modal"]', function () {
                $($(this).attr('data-target')).booklyModal('show');
            });

    }
})(jQuery);