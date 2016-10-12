<?php

namespace Drupal\oauth2server\Repository;

use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;


class Session extends AbstractStorage implements SessionInterface {

  /**
   * Get a session from an access token
   *
   * @param \League\OAuth2\Server\Entity\AccessTokenEntity $accessToken The access token
   *
   * @return \League\OAuth2\Server\Entity\SessionEntity
   */
  public function getByAccessToken(AccessTokenEntity $accessToken) {

    $params = [':token_id' => $accessToken->getId()];
    $result = db_query("
      SELECT
        s.id, s.owner_type, s.owner_id
      FROM
        {oauth2_sessions} s
      INNER JOIN
        {oauth2_access_tokens} t ON t.session_id = s.id
      WHERE
        t.token = :token_id
    ", $params)->fetchObject();
    $session = NULL;

    if ($result) {
      $session = new SessionEntity($this->getServer());
      $session->setId($result->id);
      $session->setOwner($result->owner_type, $result->owner_id);
    }

    return $session;

  }


  /**
   * Get a session from an auth code
   *
   * @param \League\OAuth2\Server\Entity\AuthCodeEntity $authCode The auth code
   *
   * @return \League\OAuth2\Server\Entity\SessionEntity
   */
  public function getByAuthCode(AuthCodeEntity $authCode) {

    $params = [':auth_code' => $authCode->getId()];
    $result = db_query("
      SELECT
        s.id, s.owner_type, s.owner_id
      FROM
        {oauth2_sessinos} s
      INNER JOIN
        {oauth2_auth_codes} a ON a.session_id = s.id
      WHERE
        a.auth_code = :auth_code
    ", $params)->fetchObject();
    $session = NULL;

    if ($result) {
      $session = new SessionEntity($this->getServer());
      $session->setId($result->id);
      $session->setOwner($result->owner_type, $result->owner_id);
    }

    return $session;

  }


  /**
   * Get a session's scopes
   *
   * @param  \League\OAuth2\Server\Entity\SessionEntity
   *
   * @return array Array of \League\OAuth2\Server\Entity\ScopeEntity
   */
  public function getScopes(SessionEntity $session) {

    $params = [':session_id' => $session->getId()];
    $result = db_query("
      SELECT
        scope.id, scope.description
      FROM
        {oauth2_session_scopes} s
      INNER JOIN
        {oauth2_scopes} scope ON scope.id = s.scope_id
      WHERE
        s.session_id = :session_id
    ", $params);
    $scopes = [];

    foreach($result as $record) {
      $scopes[] = (new ScopeEntity($this->getServer()))->hydrate([
        'id'            =>  $record->id,
        'description'   =>  $record->description,
      ]);
    }

    return $scopes;

  }


  /**
   * Create a new session
   *
   * @param string $ownerType         Session owner's type (user, client)
   * @param string $ownerId           Session owner's ID
   * @param string $clientId          Client ID
   * @param string $clientRedirectUri Client redirect URI (default = null)
   *
   * @return integer The session's ID
   */
  public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null) {

    $fields = [
      'owner_id'   => $ownerId,
      'owner_type' => $ownerType,
      'client_id'  => $clientId,
    ];

    return db_insert('oauth2_sessions')
      ->fields($fields)
      ->execute();

  }


  /**
   * Associate a scope with a session
   *
   * @param \League\OAuth2\Server\Entity\SessionEntity $session The session
   * @param \League\OAuth2\Server\Entity\ScopeEntity   $scope   The scope
   *
   * @return void
   */
  public function associateScope(SessionEntity $session, ScopeEntity $scope) {

    $fields = [
      'session_id' => $session->getId(),
      'scope_id'   => $scope->getId(),
    ];

    db_insert('oauth2_session_scopes')
      ->fields($fields)
      ->execute();

  }

}