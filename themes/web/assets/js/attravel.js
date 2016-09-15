var App = function () {

    var handleTourCreate = function () {
        $("a.button-previous").hide();
        $("a.button-submit").hide();
        $("a.step.active").parent().addClass('active');
        var currentTab =1;
        var serviceId;
        jQuery(document).on('click', 'a.button-next', function () {
            if (currentTab == 1) {
                $.ajax({
                    cache: false,
                    type: "POST",
                    url: serviceCreateStep1Url,
                    data: $("#service-form").serializeArray(),
                    success: function (data) {
                        data = jQuery.parseJSON(data);
                        $('.loading').fadeOut();
                        if (data.return == 1) {
                            serviceId = data.service_id;
                            currentTab++;
                            onNext(currentTab);
                            $(".service_id").val(data.service_id);
                        } else {
                            alert('Bạn điền thiếu hoặc sai thông tin service!');
                            $("#" + data.error).focus();
                        }
                    }
                });
            }
            if (currentTab == 2) {
                console.log($("#promotion-form").serializeArray());
                $.ajax({
                    cache: false,
                    type: "POST",
                    url: serviceCreateStep2Url,
                    data: $("#promotion-form").serializeArray(),
                    success: function (data) {
                        data = jQuery.parseJSON(data);
                        $('.loading').fadeOut();
                        if (data.return == 1) {
                            currentTab++;
                            onNext(currentTab);
                            showConfirm(serviceId);
                        }
                        if (data.return == 0) {
                            alert('Bạn điền thiếu hoặc sai thông tin khách hàng!');
                            $("#" + data.error).focus();
                        }
                        if (data.return == 2) {
                            alert('Ngày kết thúc phải lớn hơn ngày bắt đầu');
                            $("#" + data.error).focus();
                        }
                    }
                });
            }
        });
        jQuery(document).on('click', 'a.button-previous', function () {
            currentTab--;
            if (currentTab == 2) {
                if ($("#customer-form input.check").val() == 1) {
                    $("#customer-form input.check").val(2);
                }
	            if ($("#customer-form input.update")) {
		            if ($("#customer-form input.update").val() != 1) {
			            $("#customer-form input.update").val(1);
		            }
	            }

            }
            if (currentTab == 3) {
                if ($("#service-form input.check").val() == 1) {
                    $("#service-form input.check").val(2);
                }
	            if ($("#customer-form input.update")) {
		            if ($("#customer-form input.update").val() != 1) {
			            $("#customer-form input.update").val(1);
		            }
	            }

            }
            onPrev(currentTab);
        });
        var onNext = function (tab) {
                console.log('next: ' + tab);
                if (tab <= totalTab) {
                    $("a.step").each(function () {
                        $(this).parent().removeClass('active');
                        if ($(this).attr('id') == 'tab' + tab) {
                            $(this).parent().addClass('active');
                        }
                    });
                    $("div.tab-pane").each(function () {
                        $(this).removeClass('active');
                        if ($(this).attr('id') == 'tab' + tab) {
                            $(this).addClass('active');
                        }
                    });
                    $("a.button-previous").show();
                    $("a.button-submit").hide();
                    if (tab == totalTab) {
                        $("a.button-next").hide();
                        $("a.button-submit").show();
                    }
                    changeTitle(tab);
                }
            },
            onPrev = function (tab) {
                console.log('prev: ' + tab);
                if (tab >= 1) {
                    $("a.step").each(function () {
                        $(this).parent().removeClass('active');
                        if ($(this).attr('id') == 'tab' + tab) {
                            $(this).parent().addClass('active');
                        }
                    });
                    $("div.tab-pane").each(function () {
                        $(this).removeClass('active');
                        if ($(this).attr('id') == 'tab' + tab) {
                            $(this).addClass('active');
                        }
                    });
                    $("a.button-next").show();
                    $("a.button-submit").hide();
                    if (tab == 1) {
                        $("a.button-previous").hide();
                    }
                    changeTitle(tab);
                }
            },
            changeTitle = function (currentTab) {
                $("span.step-title").html('Bước ' + currentTab + ' trên ' + totalTab);
                App.scrollTo($('.breadcrumb'));
            };

    };

    var handleLoginForm = function () {

        jQuery('#forget-password').click(function () {
            jQuery('.login-form').hide();
            jQuery('.forget-form').show();
        });

        jQuery('#back-btn').click(function () {
            jQuery('.login-form').show();
            jQuery('.forget-form').hide();
        });
    };

    var handleGoTop = function () {
        /* set variables locally for increased performance */
        jQuery('.footer .go-top').click(function () {
            App.scrollTo();
        });
    };

    var handleSidebarToggler = function () {

        var container = $(".page-container");

        if ($.cookie('sidebar-closed') == 1) {
            container.addClass("sidebar-closed");
        }

        // handle sidebar show/hide
        $('.page-sidebar .sidebar-toggler').click(function () {
            $(".sidebar-search").removeClass("open");
            var container = $(".page-container");
            if (container.hasClass("sidebar-closed") === true) {
                container.removeClass("sidebar-closed");
                $.cookie('sidebar-closed', null);
            } else {
                container.addClass("sidebar-closed");
                $.cookie('sidebar-closed', 1);
            }
        });

        // handle the search bar close
        $('.sidebar-search .remove').click(function () {
            $('.sidebar-search').removeClass("open");
        });

        // handle the search query submit on enter press
        $('.sidebar-search input').keypress(function (e) {
            if (e.which == 13) {
                window.location.href = "extra_search.html";
                return false; //<---- Add this line
            }
        });

        // handle the search submit
        $('.sidebar-search .submit').click(function () {
            if ($('.page-container').hasClass("sidebar-closed")) {
                if ($('.sidebar-search').hasClass('open') == false) {
                    $('.sidebar-search').addClass("open");
                } else {
                    window.location.href = "extra_search.html";
                }
            } else {
                window.location.href = "extra_search.html";
            }
        });
    };

    var handlePortletTools = function () {
        jQuery('.portlet .tools a.remove').click(function () {
            var removable = jQuery(this).parents(".portlet");
            if (removable.next().hasClass('portlet') || removable.prev().hasClass('portlet')) {
                jQuery(this).parents(".portlet").remove();
            } else {
                jQuery(this).parents(".portlet").parent().remove();
            }
        });

        jQuery('.portlet .tools a.reload').click(function () {
            var el = jQuery(this).parents(".portlet");
            App.blockUI(el);
            var th = $(this).parent().parent().parent().find('div.grid-view').attr('id');
            if (th != 'undefined') {
                $("#" + th).yiiGridView('update');
            }
            window.setTimeout(function () {
                App.unblockUI(el);
            }, 1000);
        });

        jQuery('.portlet .tools .collapse, .portlet .tools .expand').click(function () {
            var el = jQuery(this).parents(".portlet").children(".portlet-body");
            if (jQuery(this).hasClass("collapse")) {
                jQuery(this).removeClass("collapse").addClass("expand");
                el.slideUp(200);
            } else {
                jQuery(this).removeClass("expand").addClass("collapse");
                el.slideDown(200);
            }
        });

        /*
         sample code to handle portlet config popup on close
         $('#portlet-config').on('hide', function (e) {
         //alert(1);
         //if (!data) return e.preventDefault() // stops modal from being shown
         });
         */
    };

    var handleMainMenu = function () {
        jQuery('.page-sidebar .has-sub > a').click(function () {

            var handleContentHeight = function () {
                var content = $('.page-content');
                var sidebar = $('.page-sidebar');

                if (!content.attr("data-height")) {
                    content.attr("data-height", content.height());
                }


                if (sidebar.height() > content.height()) {
                    content.css("min-height", sidebar.height() + 20);
                } else {
                    content.css("min-height", content.attr("data-height"));
                }
            };

            var last = jQuery('.has-sub.open', $('.page-sidebar'));
            if (last.size() == 0) {
                //last = jQuery('.has-sub.active', $('.page-sidebar'));
            }
            last.removeClass("open");
            jQuery('.arrow', last).removeClass("open");
            jQuery('.sub', last).slideUp(200);

            var sub = jQuery(this).next();
            if (sub.is(":visible")) {
                jQuery('.arrow', jQuery(this)).removeClass("open");
                jQuery(this).parent().removeClass("open");
                sub.slideUp(200, function () {
                    handleContentHeight();
                });
            } else {
                jQuery('.arrow', jQuery(this)).addClass("open");
                jQuery(this).parent().addClass("open");
                sub.slideDown(200, function () {
                    handleContentHeight();
                });
            }
        });
    };

    var handleResponsive = function () {
        if (jQuery.browser.msie && jQuery.browser.version.substr(0, 1) == 8) {
            isIE8 = true; // checkes for IE8 browser version
            $('.visible-ie8').show(); //
        }
        if (jQuery.browser.msie && jQuery.browser.version.substr(0, 1) == 9) {
            isIE9 = true;
        }

        var isIE10 = !!navigator.userAgent.match(/MSIE 10/);

        if (isIE10) {
            jQuery('html').addClass('ie10'); // set ie10 class on html element.
        }

        // loops all page elements with "responsive" class and applied classes for tablet mode
        // For metornic  1280px or less set as tablet mode to display the content properly
        var handleTabletElements = function () {
            if ($(window).width() <= 1280) {
                $(".responsive").each(function () {
                    var forTablet = $(this).attr('data-tablet');
                    var forDesktop = $(this).attr('data-desktop');
                    if (forTablet) {
                        $(this).removeClass(forDesktop);
                        $(this).addClass(forTablet);
                    }
                });
                handleTooltip();
            }
        };

        // loops all page elements with "responsive" class and applied classes for desktop mode
        // For metornic  higher 1280px set as desktop mode to display the content properly
        var handleDesktopElements = function () {
            if ($(window).width() > 1280) {
                $(".responsive").each(function () {
                    var forTablet = $(this).attr('data-tablet');
                    var forDesktop = $(this).attr('data-desktop');
                    if (forTablet) {
                        $(this).removeClass(forTablet);
                        $(this).addClass(forDesktop);
                    }
                });
                handleTooltip();
            }
        };

        // handle all elements which require to re-initialize on screen width change(on resize or on rotate mobile device)
        var handleElements = function () {
            handleTabletElements();
            handleDesktopElements();
        }

        // handles responsive breakpoints.
        $(window).setBreakpoints({
            breakpoints: [320, 480, 768, 900, 1024, 1280]
        });

        $(window).bind('exitBreakpoint320', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint320', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint480', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint480', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint768', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint768', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint900', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint900', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint1024', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint1024', function () {
            handleElements();
        });

        $(window).bind('exitBreakpoint1280', function () {
            handleElements();
        });
        $(window).bind('enterBreakpoint1280', function () {
            handleElements();
        });
    };

    var handleTooltip = function () {
        if (App.isTouchDevice()) { // if touch device, some tooltips can be skipped in order to not conflict with click events
            jQuery('.tooltips:not(.no-tooltip-on-touch-device)').tooltip();
        } else {
            jQuery('.tooltips').tooltip();
        }
    };

    var handlePopover = function () {
        jQuery('.popovers').popover({html:true});
    };

    var handleUniform = function () {
        if (!jQuery().uniform) {
            return;
        }
        var test = $("input[type=checkbox]:not(.toggle), input[type=radio]:not(.toggle, .star)");
        if (test) {
            test.uniform();
        }
    };

    var handleDateTimePickers = function () {

        if (jQuery().datepicker) {
            $('.date-picker').datepicker({
                format: 'dd/mm/yyyy',
                'autoclose': true,
                orientation: "top left",
                'language': "vi"
            });
        }

        if
        (jQuery().timepicker) {
            $('.timepicker-default').timepicker();
            $('.timepicker-24').timepicker({
                minuteStep: 1,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
                //value:true
            });
        }

        if (!jQuery().daterangepicker) {
            return;
        }

        $('.date-range').daterangepicker();

        $('#dashboard-report-range').daterangepicker({
                ranges: {
                    'Today': ['today', 'today'],
                    'Yesterday': ['yesterday', 'yesterday'],
                    'Last 7 Days': [Date.today().add({
                        days: -6
                    }), 'today'],
                    'Last 30 Days': [Date.today().add({
                        days: -29
                    }), 'today'],
                    'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                    'Last Month': [Date.today().moveToFirstDayOfMonth().add({
                        months: -1
                    }), Date.today().moveToFirstDayOfMonth().add({
                        days: -1
                    })]
                },
                opens: 'left',
                format: 'MM/dd/yyyy',
                separator: ' to ',
                startDate: Date.today().add({
                    days: -29
                }),
                endDate: Date.today(),
                minDate: '01/01/2012',
                maxDate: '12/31/2014',
                locale: {
                    applyLabel: 'Submit',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom Range',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    firstDay: 1
                },
                showWeekNumbers: true,
                buttonClasses: ['btn-danger']
            },

            function (start, end) {
                App.blockUI(jQuery("#dashboard"));
                setTimeout(function () {
                    App.unblockUI(jQuery("#dashboard"));
                    $.gritter.add({
                        title: 'Dashboard',
                        text: 'Dashboard date range updated.'
                    });
                    App.scrollTo();
                }, 1000);
                $('#dashboard-report-range span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy'));

            });

        $('#dashboard-report-range').show();

        $('#dashboard-report-range span').html(Date.today().add({
            days: -29
        }).toString('MMMM d, yyyy') + ' - ' + Date.today().toString('MMMM d, yyyy'));

        $('#form-date-range').daterangepicker({
                ranges: {
                    'Hôm nay': ['today', 'today'],
                    'Hôm qua': ['yesterday', 'yesterday'],
                    '7 ngày gần đây': [Date.today().add({
                        days: -6
                    }), 'today'],
                    '30 ngày gần đây': [Date.today().add({
                        days: -29
                    }), 'today'],
                    'Tháng này': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                    'Tháng trước': [Date.today().moveToFirstDayOfMonth().add({
                        months: -1
                    }), Date.today().moveToFirstDayOfMonth().add({
                        days: -1
                    })]
                },
                opens: 'right',
                format: 'dd/MM/yyyy',
                separator: ' to ',
                startDate: Date.today().add({
                    days: -29
                }),
                endDate: Date.today(),
                minDate: '01/01/2014',
                maxDate: '12/31/2015',
                locale: {
                    applyLabel: 'Xem',
                    fromLabel: 'Từ',
                    toLabel: 'Tới',
                    customRangeLabel: 'Khoảng ngày',
                    daysOfWeek: ['CN', 'H', 'B', 'T', 'N', 'S', 'B'],
                    monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                    firstDay: 1
                },
                showWeekNumbers: false,
                buttonClasses: ['btn-danger']
            },

            function (start, end) {
                $('#form-date-range span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy'));
            });

        $('#form-date-range span').html(Date.today().add({
            days: -29
        }).toString('MMMM d, yyyy') + ' - ' + Date.today().toString('MMMM d, yyyy'));


        if (!jQuery().datepicker || !jQuery().timepicker) {
            return;
        }
    };

    var handleTables = function () {
        if (!jQuery().dataTable) {
            return;
        }

        // begin first table
        $('#sample_1').dataTable({
            "aLengthMenu": [
                [10, 25, 50, -1],
                [5, 15, 20, "All"]
            ],
            // set the initial value
            "iDisplayLength": 5,
            "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                'bSortable': false,
                'aTargets': [0]
            }]
        });

        jQuery('#sample_1 .group-checkable').change(function () {
            var set = jQuery(this).attr("data-set");
            var checked = jQuery(this).is(":checked");
            jQuery(set).each(function () {
                if (checked) {
                    $(this).attr("checked", true);
                } else {
                    $(this).attr("checked", false);
                }
            });
            jQuery.uniform.update(set);
        });

        jQuery('#sample_1_wrapper .dataTables_filter input').addClass("m-wrap medium"); // modify table search input
        jQuery('#sample_1_wrapper .dataTables_length select').addClass("m-wrap xsmall"); // modify table per page dropdown

        // begin second table
        $('#sample_2').dataTable({
            "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                'bSortable': false,
                'aTargets': [0]
            }]
        });

        jQuery('#sample_2 .group-checkable').change(function () {
            var set = jQuery(this).attr("data-set");
            var checked = jQuery(this).is(":checked");
            jQuery(set).each(function () {
                if (checked) {
                    $(this).attr("checked", true);
                } else {
                    $(this).attr("checked", false);
                }
            });
            jQuery.uniform.update(set);
        });

        jQuery('#sample_2_wrapper .dataTables_filter input').addClass("m-wrap small"); // modify table search input
        jQuery('#sample_2_wrapper .dataTables_length select').addClass("m-wrap xsmall"); // modify table per page dropdown

        // begin: third table
        $('#sample_3').dataTable({
            "sDom": "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span6'i><'span6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                'bSortable': false,
                'aTargets': [0]
            }]
        });

        jQuery('#sample_3 .group-checkable').change(function () {
            var set = jQuery(this).attr("data-set");
            var checked = jQuery(this).is(":checked");
            jQuery(set).each(function () {
                if (checked) {
                    $(this).attr("checked", true);
                } else {
                    $(this).attr("checked", false);
                }
            });
            jQuery.uniform.update(set);
        });

        jQuery('#sample_3_wrapper .dataTables_filter input').addClass("m-wrap small"); // modify table search input
        jQuery('#sample_3_wrapper .dataTables_length select').addClass("m-wrap xsmall"); // modify table per page dropdown
    }

    return {
        scrollTo: function (el, offeset) {
            pos = el ? el.offset().top : 0;
            jQuery('html,body').animate({
                scrollTop: pos + (offeset ? offeset : 0)
            }, 'slow');
        },
        isTouchDevice: function () {
            try {
                document.createEvent("TouchEvent");
                return true;
            } catch (e) {
                return false;
            }
        },
        blockUI: function (el, loaderOnTop) {
            lastBlockedUI = el;
            jQuery(el).block({
                message: '<img src="' + themeUrl + '/assets/img/loading.gif" align="absmiddle">',
                css: {
                    border: 'none',
                    padding: '2px',
                    backgroundColor: 'none'
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.05,
                    cursor: 'wait'
                }
            });
        },
        unblockUI: function (el) {
            jQuery(el).unblock({
                onUnblock: function () {
                    jQuery(el).removeAttr("style");
                }
            });
        },
        init: function () {
            handleResponsive();
            handleUniform();
            handleTourCreate();
            handleLoginForm();
            handleTables();
            handleGoTop();
            handlePortletTools();
            handleMainMenu();
            handleSidebarToggler();
            handleTooltip();
            handlePopover();
            handleDateTimePickers();
        }
    };
}();
App.init();
