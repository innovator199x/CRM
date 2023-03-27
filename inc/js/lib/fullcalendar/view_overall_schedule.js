$(document).ready(function(){

    /* ==========================================================================
        Fullcalendar
        ========================================================================== */

        var calendarEl = document.getElementById('calendar');

        var calendar = $('#calendar').fullCalendar({
            navLinks: true,
            navLinkDayClick: function(date, jsEvent) {
                window.location.href = "/tech/view_overall_schedule_day?date="+date.toISOString();
              },
            header: {
                left: '',
                center: 'prev, title, next',
                right: 'today agendaDay,agendaTwoDay,agendaWeek,month'
            },
            buttonIcons: {
                prev: 'font-icon font-icon-arrow-left',
                next: 'font-icon font-icon-arrow-right',
                prevYear: 'font-icon font-icon-arrow-left',
                nextYear: 'font-icon font-icon-arrow-right'
            },
            firstDay: 1,
            editable: false,
            selectable: true,   
            eventLimit: true, // allow "more" link when too many events
            events: {
                url : '/tech/json_view_overall_schedule_calendar',
            },
            displayEventTime: false,
            displayEventEND: false,

           
           
            viewRender: function(view, element) {
                
                if (!("ontouchstart" in document.documentElement)) {
                    $('.fc-scroller').jScrollPane({
                        autoReinitialise: true,
                        autoReinitialiseDelay: 100
                    });
                }
    
                $('.fc-popover.click').remove();
            }
        });
    
    
    
    
    /* ==========================================================================
        Side datepicker
        ========================================================================== */
    
        $('#side-datetimepicker').flatpickr({
            inline: true,
            format: 'DD/MM/YYYY'
        });


    });
    
    
    /* ==========================================================================
        Calendar page grid
        ========================================================================== */
    
    (function($, viewport){
        $(document).ready(function() {
    
            if(viewport.is('>=lg')) {
                $('.calendar-page-content, .calendar-page-side').matchHeight();
            }
    
            // Execute code each time window size changes
            $(window).resize(
                viewport.changed(function() {
                    if(viewport.is('<lg')) {
                        $('.calendar-page-content, .calendar-page-side').matchHeight({ remove: true });
                    }
                })
            );
        });
    })(jQuery, ResponsiveBootstrapToolkit);


    



    
    
    