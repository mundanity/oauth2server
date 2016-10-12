<?php

namespace Drupal\oauth2server\Repository;

use League\OAuth2\Server\Storage\ClientInterface;
use League\OAuth2\Server\Entity\ClientEntity;


/**
 * Provides additional functionality for a client repository to associate
 * clients to Drupal entities.
 *
 */
interface DrupalClientInterface extends ClientInterface {

  /**
   * Returns an array of clients associated with an identifier and entity.
   *
   * @param mixed $id
   *   An ID for the entity associated with one or more clients.
   * @param string $type
   *   The entity type that the ID represents.
   *
   * @return ClientEntity[]
   *
   */
  public function getClientsByEntity($id, $type);


  /**
   * Associates a Drupal entity with a client.
   *
   * Unfortunately, Drupal entities don't have a formalized structure, as such,
   * we need to pass in both the entity ID we want to track, as well as the
   * entity type.
   *
   * @param ClientEntity $client
   *   The client entity to associate with a Drupal entity.
   * @param mixed $id
   *   The ID for the entity associated with this client.
   * @param string $type
   *   The entity type that the ID represents.
   *
   */
  public function associateEntity(ClientEntity $client, $id, $type);


  /**
   * Creates a new client.
   *
   * @param string $id
   *   The machine name of the client to create.
   * @param string $secret
   *   The secret key for the client.
   * @param string $name
   *   The human readable name or label for the client.
   *
   * @return ClientEntity
   *
   */
  public function create($id, $secret, $name);


  /**
   * Removes a client.
   *
   * @param ClientEntity $client
   *   The client to remove.
   *
   */
  public function delete(ClientEntity $client);

}