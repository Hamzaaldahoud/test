(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.contentKanbanInstantSearch = {
    attach: function attach(context) {
      // Instant search for Kanban.
      $(".content-kanban-filter-form #edit-search").on('keyup', function () {
        let $kanbanEntries = $(".content-kanban-entry");
        if ($(this).val().length >= 2) {
          const val = jQuery.trim(this.value).toLowerCase();

          let $matches = $kanbanEntries.filter(function () {
            return jQuery(this).text().toLowerCase().match(val);
          });
          $kanbanEntries.not($matches).hide();
          $matches.show();
        }
        else {
          $kanbanEntries.show();
        }
      })
    }
  };

  Drupal.behaviors.contentKanbanBoard = {
    attach: function attach(context) {
      // Drag n Drop functionality inspired by:
      // https://neliosoftware.com/blog/native-drag-and-drop-with-html5/.
      $('.kanban-entry').draggable({
        helper: 'clone'
      });

      $('.kanban-column').droppable({
        accept: '.kanban-entry',
        hoverClass: 'hovering',

        // On drop event.
        drop: function (ev, ui) {
          ui.draggable.detach();
          $(this).append(ui.draggable);

          // Get the EntityId and type from draggable object.
          const entityId = $(ui.draggable[0]).data('id');
          const type = $(ui.draggable[0]).data('type');
          // Get the state_id from target column.
          const stateID = $(this).data('state_id');

          if (stateID && entityId && type) {
            // Generate URL for AJAX call.
            const url = Drupal.url('admin/content-kanban/update-entity-workflow-state/' + type + '/' + entityId + '/' + stateID);
            $.ajax({
              'url': url,
              'success': function (result) {
                if (!result.success) {
                  alert(Drupal.t('Something went wrong: @message', {
                    '@message': result.message
                  }));
                }
                else {
                  alert(Drupal.t('Updated.'));
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
