<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'register' => [
        'path' => './assets/js/register.js',
        'entrypoint' => true,
    ],
    'location' => [
        'path' => './assets/js/location.js',
        'entrypoint' => true,
    ],
    'resetpw' => [
        'path' => './assets/js/resetpw.js',
        'entrypoint' => true,
    ],
    'updatepw' => [
        'path' => './assets/js/updatepw.js',
        'entrypoint' => true,
    ],
    'deleteAccount' => [
        'path' => './assets/js/deleteAccount.js',
        'entrypoint' => true,
    ],
    'scrollToggle' => [
        'path' => './assets/js/scrollToggleDiv.js',
        'entrypoint' => true,
    ],
    'admin_custom' => [
        'path' => './assets/js/admin_custom.js',
        'entrypoint' => true,
    ],
    'admin_check_name' => [
        'path' => './assets/js/admin_check_name.js',
        'entrypoint' => true,
    ],
    'certificate_name' => [
        'path' => './assets/js/certificate_name_update.js',
        'entrypoint' => true,
    ],
    '@fortawesome/fontawesome-free/css/all.css' => [
        'version' => '6.6.0',
        'type' => 'css',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'video.js' => [
        'version' => '8.21.0',
    ],
    'global/window' => [
        'version' => '4.4.0',
    ],
    'global/document' => [
        'version' => '4.4.0',
    ],
    '@videojs/xhr' => [
        'version' => '2.7.0',
    ],
    'videojs-vtt.js' => [
        'version' => '0.15.5',
    ],
    '@babel/runtime/helpers/extends' => [
        'version' => '7.26.0',
    ],
    '@videojs/vhs-utils/es/resolve-url.js' => [
        'version' => '4.1.1',
    ],
    'm3u8-parser' => [
        'version' => '7.2.0',
    ],
    '@videojs/vhs-utils/es/codecs.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/media-types.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/byte-helpers' => [
        'version' => '4.1.1',
    ],
    'mpd-parser' => [
        'version' => '1.3.1',
    ],
    'mux.js/lib/tools/parse-sidx' => [
        'version' => '7.1.0',
    ],
    '@videojs/vhs-utils/es/id3-helpers' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/containers' => [
        'version' => '4.1.1',
    ],
    'mux.js/lib/utils/clock' => [
        'version' => '7.1.0',
    ],
    'video.js/dist/video-js.min.css' => [
        'version' => '8.21.0',
        'type' => 'css',
    ],
    'is-function' => [
        'version' => '1.0.2',
    ],
    '@videojs/vhs-utils/es/stream.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/decode-b64-to-uint8-array.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/resolve-url' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/media-groups' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/decode-b64-to-uint8-array' => [
        'version' => '4.1.1',
    ],
    '@xmldom/xmldom' => [
        'version' => '0.8.10',
    ],
    'purchase' => [
        'path' => './assets/js/purchase.js',
        'entrypoint' => true,
    ],
    'tom-select' => [
        'version' => '2.4.3',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'rating' => [
        'path' => './assets/js/rating.js',
        'entrypoint' => true,
    ],
    'countTestimonial' => [
        'path' => './assets/js/countTestimonial.js',
        'entrypoint' => true,
    ],
    'launchNotification' => [
        'path' => './assets/js/launchNotification.js',
        'entrypoint' => true,
    ],
];
