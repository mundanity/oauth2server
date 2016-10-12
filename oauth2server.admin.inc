<?php

/**
 * Administrative configuration form.
 *
 */
function oauth2server_admin_form($form, &$form_state) {

  $form['oauth2server_access_token_ttl'] = [
    '#type'          => 'textfield',
    '#size'          => 10,
    '#title'         => t('Access token TTL'),
    '#description'   => t('The time (in seconds) a provisioned access token will be valid for (default 3600s).'),
    '#default_value' => variable_get('oauth2server_access_token_ttl', 3600),
  ];
  $form['oauth2server_is_authorization_server'] = [
    '#type'          => 'checkbox',
    '#title'         => t('Enable authorization server.'),
    '#description'   => t('If enabled, this Drupal site will act as an authorization server, and provide an "access_token" endpoint.'),
    '#default_value' => variable_get('oauth2server_is_authorization_server', TRUE),
  ];

  return system_settings_form($form);

}