(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.contentCalendarMain = {
    attach: function attach(context) {
      // Jump to current Calendar.
      if (!document.location.hash) {
        const currentDate = new Date();
        const calendarID = currentDate.getFullYear() + '-' + (currentDate.getMonth() + 1);
        window.location.href = '#' + calendarID;
      }

      // Go to a specific Calendar by year.
      $('#edit-calendar-year').on('change', function () {
        window.location.href = Drupal.url('admin/content-calendar/' + $(this).val());
      });

      window.onscroll = function () {
        const $calendarSidebar = $('#content-calendar-overview .sidebar');
        if (window.scrollY > 100) {
          $calendarSidebar.addClass('fixed');
        }
        else {
          $calendarSidebar.removeClass('fixed');
        }
      };
    }
  };

  Drupal.behaviors.contentCalendarDrag = {
    attach: function attach(context) {
      // Drag n Drop functionality inspired by:
      // https://neliosoftware.com/blog/native-drag-and-drop-with-html5/.
      $('.calendar-entry.draggable').draggable({
        helper: 'clone'
      });

      $('.content-calendar .droppable').droppable({
        accept: '.calendar-entry.draggable',
        hoverClass: 'hovering',

        // On drop event.
        drop: function (ev, ui) {
          ui.draggable.detach();
          $(this).append(ui.draggable);

          // Get the node id from the draggable object.
          const nid = $(ui.draggable[0]).data('nid');
          // Get the date from target cell.
          const date = $(this).data('date');

          if (date && nid) {
            // Generate the URL for AJAX call.
            const url = Drupal.url('admin/content-calendar/update-node-publish-date/' + nid + '/' + date);
            $.ajax({
              'url': url,
              'success': function (result) {
                if (!result.success) {
                  alert(Drupal.t('Something went wrong: @message', {
                    '@message': result.message
                  }));
                }
              },
              'error': function (xhr, status, error) {
                alert(Drupal.t('An error occurred during the update of the desired node. Please consult the watchdog.'));
              }
            });
          }
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
