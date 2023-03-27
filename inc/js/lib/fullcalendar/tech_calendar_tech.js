$(document).ready(function(){

    /* ==========================================================================
        Fullcalendar
        ========================================================================== */

        var calendarEl = document.getElementById('calendar');

        var calendar = $('#calendar').fullCalendar({
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
                url : '/calendar/json_tech_calendar',
            },
            eventRender: function(event, element){
                if(event.icon){          
                    if(event.icon!=null){
                        element.find(".fc-title").prepend("<i style='margin-right:4px;font-size:18px;' class='fa fa-"+event.icon+"'></i>");
                    }
                }
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
            },
            eventClick: function(calEvent, jsEvent, view) {

                //alert(calEvent.cal_url);
               /* $.fancybox.open({
                    'transitionIn': 'none',
                    'transitionOut': 'none',
                    'type': 'ajax',
                    'src': calEvent.cal_url
                });*/

                var eventEl = $(this);
    
                // Add and remove event border class
                if (!$(this).hasClass('event-clicked')) {
                    $('.fc-event').removeClass('event-clicked');
    
                    $(this).addClass('event-clicked');
                }
                
                var area_text = (calEvent.ClassID==6) ? "Area: " : "Calendar Item: ";
                var acco = (calEvent.accomodation==1 || calEvent.accomodation==2) ? calEvent.accomodation_name : " " ;
                var cal_address = (calEvent.address!= null)?calEvent.address:'';
                var cal_phone = (calEvent.acco_phone!= null)?calEvent.acco_phone:'';

                var hidden_class = (calEvent.accomodation==0 || calEvent.accomodation==null) ? "hidden" : " " ;

                // Add popover
                $('body').append(

                    

                    '<div class="fc-popover click">' +
                        '<div class="fc-header">' +
                            moment(calEvent.start).format('dddd • D') +
                            '<button type="button" class="cl"><i class="font-icon-close-2"></i></button>' +
                        '</div>' +
    
                        '<div class="fc-body main-screen">' +
                        '<p>' +
                        moment(calEvent.start).format('DD/MM/YY') + "   - " +calEvent.start_time + " <br/> " + moment(calEvent.end).format('DD/MM/YY')+" - "+calEvent.end_time+ 
                    '</p>' +
                            
                            '<p><strong style="color:#000">'+
                            calEvent.title +
                            '</strong></p>' +

                            '<p class="'+hidden_class+'"><strong style="color:#000"><span style="color:#fa424a;" class="fa fa-home"></span> ' +
                            acco +
                            '</strong></p>' +

                            '<p class="'+hidden_class+'"><strong><span style="color:#fa424a;" class="fa fa-map-marker"></span> <span style="color:#000">' +
                            cal_address +
                            '</span></strong></p>' +

                            '<p class="'+hidden_class+'"><strong><span style="color:#fa424a;" class="fa fa-phone"></span> <span style="color:#000">' +
                            cal_phone +
                            '</span></strong></p>' +

                            '<p><strong><span style="color:#fa424a;" class="fa fa-info-circle"></span> <em style="color:#000">' +
                            calEvent.details +
                            '</em></strong></p>' +


                            

                           /* '<ul class="actions">' +
                                '<li><a target="_blank" href="/properties/property_detail/'+calEvent.id+'">More details</a></li>' +
                              
                               
                            '</ul>' +

                            */
                        '</div>' +
    
                        '<div class="fc-body remove-confirm">' +
                            '<p>Are you sure to remove event?</p>' +
                            '<div class="text-center">' +
                                '<button type="button" class="btn btn-rounded btn-sm">Yes</button>' +
                                '<button type="button" class="btn btn-rounded btn-sm btn-default remove-popover">No</button>' +
                            '</div>' +
                        '</div>' +
    
                        '<div class="fc-body edit-event">' +
                            '<p>Edit event</p>' +
                            '<div class="form-group">' +
                                '<div class="input-group date datetimepicker">' +
                                    '<input type="text" class="form-control" />' +
                                    '<span class="input-group-addon"><i class="font-icon font-icon-calend"></i></span>' +
                                '</div>' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<div class="input-group date datetimepicker-2">' +
                                    '<input type="text" class="form-control" />' +
                                    '<span class="input-group-addon"><i class="font-icon font-icon-clock"></i></span>' +
                                '</div>' +
                            '</div>' +
                            '<div class="form-group">' +
                                '<textarea class="form-control" rows="2">Name Surname Patient Surgey ACL left knee</textarea>' +
                            '</div>' +
                            '<div class="text-center">' +
                                '<button type="button" class="btn btn-rounded btn-sm">Save</button>' +
                                '<button type="button" class="btn btn-rounded btn-sm btn-default remove-popover">Cancel</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
    
                // Position popover
                function posPopover(){
                    $('.fc-popover.click').css({
                        left: eventEl.offset().left + eventEl.outerWidth()/2,
                        top: eventEl.offset().top + eventEl.outerHeight()
                    });
                }
    
                posPopover();
    
                $('.fc-scroller, .calendar-page-content, body').scroll(function(){
                    posPopover();
                });
    
                $(window).resize(function(){
                   posPopover();
                });
    
    
                // Remove old popover
                if ($('.fc-popover.click').length > 1) {
                    for (var i = 0; i < ($('.fc-popover.click').length - 1); i++) {
                        $('.fc-popover.click').eq(i).remove();
                    }
                }
    
                // Close buttons
                $('.fc-popover.click .cl, .fc-popover.click .remove-popover').click(function(){
                    $('.fc-popover.click').remove();
                    $('.fc-event').removeClass('event-clicked');
                });
    
                // Actions link
                $('.fc-event-action-edit').click(function(e){
                    e.preventDefault();
    
                    $('.fc-popover.click .main-screen').hide();
                    $('.fc-popover.click .edit-event').show();
                });
    
                $('.fc-event-action-remove').click(function(e){
                    e.preventDefault();
    
                    $('.fc-popover.click .main-screen').hide();
                    $('.fc-popover.click .remove-confirm').show();
                });

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


    



    
    
    