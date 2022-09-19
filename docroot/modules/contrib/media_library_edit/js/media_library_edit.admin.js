(function ($, Drupal, window) {

  Drupal.behaviors.MediaLibraryWidgetWarn = {
    attach: function attach(context) {
      // Override the existing warning from media_library/js/media_library.ui.js
      // to disable for the edit link.
      $('.js-media-library-item a[href]:not(.media-library-edit__link)', context).once('media-library-warn-link').on('click', function (e) {
        var message = Drupal.t('Unsaved changes to the form will be lost. Are you sure you want to leave?');
        var confirmation = window.confirm(message);
        if (!confirmation) {
          e.preventDefault();
        }
      });
    }
  };

  Drupal.behaviors.MediaLibraryWidgetEditLink = {
    attach: function attach() {
      $('.media-library-widget .media-library-edit__link')
          .once('media-library-edit-link')
          .on('click', function () {
            // Remove any "selected-media" classes.
            $(this).parent().parent().find('selected-media').removeClass('selected-media');
            // Mark the media item as selected to render it properly when submitting an ajax media edit request.
            $(this).parent().find('article').addClass('selected-media');
          })
    },
  };

})(jQuery, Drupal, window);
