<?php

namespace Drupal\oauth2server\Resources;

use Drupal\restapi\AbstractResource;
use Drupal\oauth2server\Server\DrupalAuthorizationServer;


class AccessToken extends AbstractResource {

  public function post() {

    $ttl      = variable_get('oauth2server_access_token_ttl', 3600);
    $callback = oauth2server_password_callback();
    $server   = DrupalAuthorizationServer::configureForPasswordGrant($callback, $ttl);

    try {
      $response = $server->issueAccessToken();
    }
    catch (\Exception $e) {
      $response = [
        'error'   => isset($e->errorType) ? $e->errorType : 'unknown',
        'message' => $e->getMessage(),
      ];
    }

    return $this->toJson($response);

  }

}