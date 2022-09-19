/**
 * @file
 * Paste Filter CKEditor plugin.
 *
 * Plugin for filtering elements pasted into the CKEditor editing area.
 */

(function (Drupal) {

  'use strict';

  CKEDITOR.on('instanceReady', function( ev ) {
    var blockTags = ['div','h1','h2','h3','h4','h5','h6','p','pre','li','blockquote','ul','ol',
      'table','thead','tbody','tfoot','td','th',];

    for (var i = 0; i < blockTags.length; i++)
    {
      ev.editor.dataProcessor.writer.setRules( blockTags[i], {
        indent : false,
        breakBeforeOpen : true,
        breakAfterOpen : false,
        breakBeforeClose : false,
        breakAfterClose : true
      });
    }
  });

  CKEDITOR.plugins.add('ckeditor_paste_filter', {

    // Register the icons.
    icons: 'paste-filter',
    init : function( editor )
    {

      // v 4.1 filters
      // if (editor.addFeature)
      // {
      //   editor.addFeature( {
      //     allowedContent: 'img[!src,id];'
      //   } );
      // }

      // Paste from clipboard:
      editor.on( 'paste', function(e) {
        var data = e.data,
          html = (data.html || ( data.type && data.type=='html' && data.dataValue));
        if (!html)
          return;

        // absolute url to relative
        // html = html.replace( /href="http[s]*:\/\/(www\.|)example\.com\//gimsu, 'href="/');
        // remove styles from tags
        html = html.replace( /(<[^>]*) (style="[^"]*")/gimsu, '$1');
        // remove face from tags
        html = html.replace( /(<[^>]*) (face="[^"]*")/gimsu, '$1');
        // remove class from tags
        html = html.replace( /(<[^>]*) (class="[^"]*")/gimsu, '$1');
        // remove valign from tags
        html = html.replace( /(<[^>]*) (valign="[^"]*")/gimsu, '$1');
        // remove font
        html = html.replace( /<font[^>]*>/gimsu, '');
        html = html.replace( /<\/font>/gimsu, '');
        // remove spans
        html = html.replace( /<span[^>]*>/gimsu, '');
        html = html.replace( /<\/span>/gimsu, '');
        // remove empty paragraphs
        html = html.replace( /<p>&nbsp;<\/p>/gimsu, '');
        html = html.replace( /<p><\/p>/gimsu, '');
        // remove empty <b> tag
        html = html.replace( /<b><\/b>/gimsu, '');
        // remove empty <i> tag
        html = html.replace( /<i><\/i>/gimsu, '');

        if (e.data.html)
          e.data.html = html;
        else
          e.data.dataValue = html;
      });

    } //Init

  });

} (Drupal));
