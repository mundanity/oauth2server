<?php

namespace Drupal\oauth2server\Repository;

use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\RefreshTokenInterface;


class RefreshToken extends AbstractStorage implements RefreshTokenInterface {

  /**
   * Return a new instance of \League\OAuth2\Server\Entity\RefreshTokenEntity
   *
   * @param string $token
   *
   * @return \League\OAuth2\Server\Entity\RefreshTokenEntity
   */
  public function get($token) {

    $params = [':token' => $token];
    $result = db_query("
      SELECT
        t.token, t.access_token, t.expire_time
      FROM
        {oauth2_refresh_tokens} t
      WHERE
        t.token = :token
    ", $params)->fetchObject();
    $token = NULL;

    if ($result) {
      $token = new RefreshToken($this->getServer());
      $token->setId($result->token);
      $token->setExpireTime($result->expire_time);
      $token->setAccessTokenId($result->access_token);
    }

    return $token;

  }

  /**
   * Create a new refresh token_name
   *
   * @param string  $token
   * @param integer $expireTime
   * @param string  $accessToken
   *
   * @return \League\OAuth2\Server\Entity\RefreshTokenEntity
   */
  public function create($token, $expireTime, $accessToken) {

    $fields = [
      'token'        => $token,
      'access_token' => $accessToken,
      'expire_time'  => $expireTime,
    ];

    db_insert('oauth2_refresh_tokens')
      ->fields($fields)
      ->execute();

  }

  /**
   * Delete the refresh token
   *
   * @param \League\OAuth2\Server\Entity\RefreshTokenEntity $token
   *
   * @return void
   */
  public function delete(RefreshTokenEntity $token) {

    db_delete('oauth2_refresh_tokens')
      ->condition('token', $token->getId())
      ->execute();

  }

}