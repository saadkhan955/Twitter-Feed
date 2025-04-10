/**
 * @file
 * Theme toggle for Twitter Feed block.
 */
(function (Drupal, drupalSettings, once) {
  'use strict';

  const THEME_STORAGE_KEY = 'twitter_feed_theme';
  const THEMES = ['light', 'dark', 'auto'];
  const CLASS_PREFIX = 'twitter-feed--';

  Drupal.behaviors.twitterFeedThemeToggle = {
    attach: function (context, settings) {
      once('twitter-feed-theme', '.twitter-feed__theme-toggle', context).forEach(function (button) {
        if (!button) {
          return;
        }

        const container = button.closest('.twitter-feed');
        if (!container) {
          return;
        }

        let currentTheme = THEMES[0];
        THEMES.forEach(theme => {
          if (container.classList.contains(CLASS_PREFIX + theme)) {
            currentTheme = theme;
          }
        });

        if (!container.classList.contains(CLASS_PREFIX + currentTheme)) {
          container.classList.add(CLASS_PREFIX + currentTheme);
        }

        button.addEventListener('click', function (e) {
          e.preventDefault();

          const currentIndex = THEMES.indexOf(currentTheme);
          const nextTheme = THEMES[(currentIndex + 1) % THEMES.length];

          THEMES.forEach(theme => {
            container.classList.remove(CLASS_PREFIX + theme);
          });
          container.classList.add(CLASS_PREFIX + nextTheme);

          button.setAttribute('data-theme', nextTheme);
          currentTheme = nextTheme;

          try {
            localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
          } catch (e) { }

          Drupal.announce(
            Drupal.t('Theme changed to @theme', { '@theme': nextTheme }),
            'polite'
          );
        });

        try {
          const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
          if (savedTheme && THEMES.includes(savedTheme)) {
            THEMES.forEach(theme => {
              container.classList.remove(CLASS_PREFIX + theme);
            });
            container.classList.add(CLASS_PREFIX + savedTheme);
            currentTheme = savedTheme;
            button.setAttribute('data-theme', savedTheme);
          }
        } catch (e) { }
      });
    }
  };
})(Drupal, drupalSettings, once);