<?php

namespace Drupal\oauth2server\Resources;

use Drupal\restapi\AbstractResource;
use Drupal\oauth2server\Repository\AccessToken as AccessTokenRepository;


class Introspect extends AbstractResource {

  /**
   * @see https://tools.ietf.org/html/draft-ietf-oauth-introspection-04
   *
   */
  public function post() {

    $param = $this->getRequest()->get('access_token');
    $token = (new AccessTokenRepository())->get($param);

    if (!$token) {
      return $this->toJson([
        'error' => 'NO',
        'message' => 'NO TOKEN FOR ' . $param,
      ]);
    }

    $response = [
      'hi' => 'hi',
      'token' => $token->getId(),
    ];

    return $this->toJson($response);

  }

}