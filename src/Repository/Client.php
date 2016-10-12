<?php

namespace Drupal\oauth2server\Repository;


use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Storage\AbstractStorage;


class Client extends AbstractStorage implements DrupalClientInterface {

  /**
   * Validate a client
   *
   * @param string $clientId     The client's ID
   * @param string $clientSecret The client's secret (default = "null")
   * @param string $redirectUri  The client's redirect URI (default = "null")
   * @param string $grantType    The grant type used (default = "null")
   *
   * @return \League\OAuth2\Server\Entity\ClientEntity
   */
  public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null) {

    $query = db_select('oauth2_clients', 'c');
    $query->fields('c');
    $query->condition('c.id', $clientId);

    if ($clientSecret) {
      $query->condition('c.secret', $clientSecret);
    }

    if ($redirectUri) {
      $query->join('oauth2_client_redirect_uris', 'u', 'u.client_id = c.id');
      $query->condition('u.redirect_uri', $redirectUri);
    }

    $result = $query->execute()->fetchObject();
    $client = NULL;

    if ($result) {
      $client = (new ClientEntity($this->getServer()))->hydrate([
        'id'   => $result->id,
        'name' => $result->name,
      ]);
    }

    return $client;

  }

  /**
   * Get the client associated with a session
   *
   * @param \League\OAuth2\Server\Entity\SessionEntity $session The session
   *
   * @return \League\OAuth2\Server\Entity\ClientEntity
   */
  public function getBySession(SessionEntity $session) {

    $params = [':id' => $session->getId()];
    $result = db_query("
      SELECT
        c.id, c.name
      FROM
        {oauth2_clients} c
      INNER JOIN
        {oauth2_sessions} s ON s.client_id = c.id
      WHERE
        s.id = :id
    ", $params)->fetchObject();
    $client = NULL;

    if ($result) {
      $client = (new ClientEntity($this->getServer()))->hydrate([
        'id'   => $result->id,
        'name' => $result->name,
      ]);
    }

    return $client;

  }


  /**
   * {@inheritdoc}
   *
   */
  public function getClientsByEntity($id, $type) {

    $params = [
      ':id'   => $id,
      ':type' => $type,
    ];
    $result = db_query("
      SELECT
        c.id, c.name
      FROM
        {oauth2_clients} c
      INNER JOIN
        {oauth2_entity_clients} e ON e.client_id = c.id
      WHERE
        e.entity_type = :type
      AND
        e.entity_id = :id
    ", $params);
    $clients = [];

    foreach($result as $record) {
      $clients[] = (new ClientEntity($this->getServer()))->hydrate([
        'id'   => $record->id,
        'name' => $record->name,
      ]);
    }

    return $clients;

  }


  /**
   * {@inheritdoc}
   *
   */
  public function associateEntity(ClientEntity $client, $id, $type) {

    $fields = [
      'client_id'   => $client->getId(),
      'entity_id'   => $id,
      'entity_type' => $type,
    ];

    db_insert('oauth2_entity_clients')
      ->fields($fields)
      ->execute();

  }


  /**
   * {@inheritdoc}
   *
   */
  public function create($id, $secret, $name) {

    $id = preg_replace('/[^a-zA-Z0-9_]/', '_', $id);
    $fields = [
      'id'     => $id,
      'secret' => $secret,
      'name'   => $name,
    ];

    db_insert('oauth2_clients')
      ->fields($fields)
      ->execute();

    return $this->get($id);

  }


  /**
   * {@inheritdoc}
   *
   */
  public function delete(ClientEntity $client) {

    db_delete('oauth2_entity_clients')
      ->condition('client_id', $client->getId())
      ->execute();

    db_delete('oauth2_sessions')
      ->condition('client_id', $client->getId())
      ->execute();

    db_delete('oauth2_client_redirect_uris')
      ->condition('client_id', $client->getId())
      ->execute();

    db_delete('oauth2_clients')
      ->condition('id', $client->getId())
      ->execute();

  }

}