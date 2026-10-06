# AVILLAS child theme

Install this directory as `wp-content/themes/realhomes-child` alongside the
RealHomes parent theme. This repository contains only the child theme, not
WordPress, plugins, uploads, configuration, or the database.

## Page styling

- Global header and language selector: `assets/css/avillas-header-global.css` and `child-custom.css`.
- Homepage and property styling: `assets/css/avillas-shared.css`.
- Telephone consultation (page 14510) and visits (page 7951) share
  `assets/css/rendez-vous-telephonique.css`. The visits page adds
  `assets/css/visites-personnalisees.css` and `assets/images/screen.png`.
- The existing eForm forms and their validation remain managed by the plugin.

## Random businesses slider

`inc/random-properties.php` renders three randomly selected published properties
before the classic footer partners section. It excludes the current property
and the `services` property-status term, including its descendants. Rental and
other published property listings are eligible. With fewer than three eligible
listings, it shows the available number; with none, it renders no section.

The slider uses native horizontal scrolling, previous/next controls, keyboard
arrows when the track has focus, and no automatic playback. French, English,
Spanish, and Catalan labels follow Polylang/WordPress; GTranslate can translate
the rendered text. Full-page caching retains the random selection until that
page is regenerated.

## Local validation

Run `php -l functions.php`, `php -l inc/random-properties.php`, and
`node --check assets/js/random-properties.js`. Clear LiteSpeed cache after
changing page styles or server-rendered markup.

reCAPTCHA is not disabled for local testing: the existing domain error remains
visible when the configured key does not authorize localhost.
