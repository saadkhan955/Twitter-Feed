/**
 * @file
 * Core behavior for the Twitter Feed module.
 */
(function (Drupal, drupalSettings, once) {
  'use strict';

  Drupal.behaviors.twitterFeed = {
    attach: function (context, settings) {

      // Shorten long URLs
      once('twitter-feed-links', '.twitter-feed__tweet-body a', context).forEach(function (linkEl) {
        const url = linkEl.textContent;
        if (url.length > 30 && url.startsWith('http')) {
          linkEl.textContent = url.substring(0, 30) + '...';
        }
      });
    }
  };

})(Drupal, drupalSettings, once);
