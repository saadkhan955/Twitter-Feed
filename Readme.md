# Twitter Feed Module for Drupal

![Drupal](https://img.shields.io/badge/Drupal-9.x%20%7C%2010.x-blue)
![Twitter API](https://img.shields.io/badge/Twitter%20API-v2-blue)

A responsive Drupal block module that displays tweets from any Twitter account with customizable theming options.

## Features

- Display tweets from any public Twitter account
- Toggle between light and dark themes
- Fully responsive design for all devices
- Accessibility compliant (ARIA labels, keyboard navigation)
- Customizable display options
- Built-in caching system
- Development mode with dummy data
- No external library dependencies

## Requirements

- Drupal Core 9.x or 10.x
- Twitter Developer Account (for API access)
- PHP 7.4 or higher

## Installation

1. Download and place the module in your Drupal site:
- Location `web/module/custom`
2. Enable the module:
- Via Drush: `drush en twitter_feed`
- Or through Drupal admin: `/admin/modules`

3. Configure your Twitter API credentials:
`/admin/config/services/twitter-feed`

## Configuration

### Block Placement
1. Navigate to Block layout: `/admin/structure/block`
2. Click "Place block" in your desired region
3. Add "Twitter Feed Block"
4. Configure block settings:
- Twitter handle (override default if needed)
- Display theme (light/dark)
- Show/hide profile images
- Enable/disable theme toggle

### API Settings
Configure at: `/admin/config/services/twitter-feed`

#### Twitter API
- **Bearer Token**: Required authentication token
- Obtain from: [Twitter Developer Portal](https://developer.twitter.com/)

#### Default Settings
- **Default Handle**: Primary account to display
- **Tweet Count**: Number of tweets (1-10)

#### Cache
- **Cache Lifetime**: Duration in seconds
- Default: 3600 (1 hour)
- Minimum: 300 (5 minutes)

#### Development
- **Dev Mode**: Use dummy tweets (no API calls)

## Template Structure

`web/modules/custom/twitter_feed/templates/twitter-feed-block.html.twig`

Includes:
- Header with handle and theme toggle
- Tweet content section
- Responsive layout components
- Mobile-optimized markup

## Theming

CSS/SCSS located at:
`web/modules/custom/twitter_feed/css/twitter-feed.css`

Mobile features:
- Adaptive border radius
- Responsive profile images
- Reorganized tweet metrics

## JavaScript

Interactive features in:
`web/modules/custom/twitter_feed/js/twitter-feed.js`

Features:
- Theme switching
- Engagement interactions
- Dynamic loading

## Build System

Gulp configuration:
`web/modules/custom/twitter_feed/gulpfile.js`

Commands:
- `gulp`: Watch and compile SCSS
- `gulp build`: One-time compilation

## Architecture

### Implemented Hooks
- `hook_help()`: Module documentation
- `hook_theme()`: Theme registration
- `hook_preprocess_HOOK()`: Template variables

### Cache System
- Uses Drupal cache tags
- Automatic invalidation on config changes
- Configurable lifetime

## Security

- Bearer Token stored encrypted
- Read-only API access
- Input sanitization
- Rate limiting handled by Twitter API

## Accessibility

- WCAG 2.1 AA compliant
- ARIA labels for all interactive elements
- Semantic HTML structure
- Keyboard navigable

## Browser Support

- Chrome, Firefox, Safari, Edge (latest 2 versions)
- Mobile Safari, Chrome for Android
- IE11+ (with polyfills)

## Troubleshooting

**Tweets not displaying?**
1. Verify Bearer Token is valid
2. Check Twitter handle exists and is public
3. Clear cache: `drush cr`
4. Verify API rate limits not exceeded

**Styling issues?**
1. Check for CSS conflicts
2. Verify theme overrides
3. Test in development mode

## Extending

Override these components:
- Twig template for markup
- CSS for styling
- Implement `hook_twitter_feed_data_alter()` to modify tweet data