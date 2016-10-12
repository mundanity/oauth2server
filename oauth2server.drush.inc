<?php

/**
 * Implements hook_drush_command().
 *
 */
function oauth2server_drush_command() {

  $items['oauth2server-expire-tokens'] = [
    'description' => dt('Expires access tokens, refresh tokens, and authorization codes.'),
    'aliases'     => ['oauth2server-expire'],
  ];

  return $items;

}


/**
 * Expires access tokens, refresh tokens, and authorization codes.
 *
 */
function drush_oauth2server_expire_tokens() {

  $count = oauth2server_expire_tokens();

  drush_log(dt('Expired !count access tokens', ['!count' => $count->access_tokens]), 'ok');
  drush_log(dt('Expired !count refresh tokens', ['!count' => $count->refresh_tokens]), 'ok');
  drush_log(dt('Expired !count authorization codes', ['!count' => $count->auth_codes]), 'ok');

}