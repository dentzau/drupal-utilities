<?php

declare(strict_types = 1);

namespace Drupal\utilities\ParamConverter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides a route parameter converter for entities by their UUID.
 *
 * @code
 * my_module.route_name:
 *   path: /some/path/to/{node}
 *   defaults:
 *     _controller: Drupal\my_module\Controller\MyController::show
 *   options:
 *     parameters:
 *       node:
 *         type: entity_uuid:node
 * @endcode
 */
class EntityUuidConverter implements ParamConverterInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor for EntityUuidConverter.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults): ?EntityInterface {
    [, $entity_type] = explode(':', $definition['type']);
    $storage = $this->entityTypeManager->getStorage($entity_type);

    $query = $storage
      ->getQuery()
      ->accessCheck()
      ->condition('uuid', $value);

    $entities = $storage->loadMultiple($query->execute());
    return reset($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route): bool {
    [$converter] = explode(':', $definition['type'] ?? '');
    return $converter === 'entity_uuid';
  }

}
