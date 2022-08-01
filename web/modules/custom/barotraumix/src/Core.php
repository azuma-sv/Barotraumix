<?php

/**
 * @file
 * Source code of the service "barotraumix.core".
 */

namespace Drupal\barotraumix;

use Drupal\barotraumix\BaroEntity\ContentPackage;
use Drupal\Core\File\FileSystemInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Exception;

/**
 * Class Core for main Barotraumix service.
 */
class Core {

  /**
   * @const BAROTRAUMA_APP_ID - Contains barotrauma app ID in steam.
   */
  const BAROTRAUMA_APP_ID = 1026340;

  /**
   * @var FileSystemInterface $fileSystem - File system service.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * Class constructor.
   *
   * @param FileSystemInterface $fileSystem - File system service.
   */
  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * Get scanner object which will allow us to scan folder of specific app by build id.
   *
   * @param int $appId - ID of the app in Steam.
   * @param int $buildId - Build id of the app in Steam.
   *
   * @return Scanner
   */
  public function scan(int $appId, int $buildId):Scanner {
    // Use static cache.
    static $scanners = [];
    // Get and return scanner.
    $scanners[$appId][$buildId] = $scanners[$appId][$buildId] ?? new Scanner($appId, $buildId, $this->fileSystem);
    return $scanners[$appId][$buildId];
  }

  /**
   * Creates node for application by using it's scanner.
   *
   * @param Scanner $scanner
   *  Scanner to use.
   *
   * @return NodeInterface
   */
  public function createApp(Scanner $scanner):NodeInterface {
    // Get primary content package.
    $contentPackage = new ContentPackage($scanner->contentPackage());
    $contentPackageEntity = $contentPackage->saveToDrupalEntity();
    $applicationType = $this->applicationType($scanner->type());

    // Create node object with attached file.
    $application = Node::create([
      'type'                      => 'application',
      'title'                     => $contentPackage->name() . ' - ' . $contentPackage->gameVersion(),
      'field_app_id'              => $scanner->appId(),
      'field_build_id'            => $scanner->buildId(),
      'field_application_type'    => [
        'target_id'          => $applicationType->id(),
        'target_revision_id' => $applicationType->getRevisionId(),
      ],
      'field_content_package'     => [
        'target_id'          => $contentPackageEntity->id(),
        'target_revision_id' => $contentPackageEntity->getRevisionId(),
      ],
      'created'                   => time(),
    ]);
    $application->save();
    return $application;
  }

  /**
   * Get taxonomy term of the application type by string.
   *
   * @param null|string $string
   *  String containing application type. We have only two: 'game' and 'mod'. Leave blank to use 'game'.
   *
   * @return Term
   */
  public function applicationType(string $string = NULL):Term {
    $string = $string ?? 'game';
    // 3 is for game, 4 is for mod.
    $terms = Term::loadMultiple([3, 4]);
    $term = NULL;
    $term = $string == 'game' ? $terms[3] : $term;
    $term = $string == 'mod' ? $terms[4] : $term;
    // Handle error.
    if (!isset($term)) {
      throw new Exception("Unable to find Taxonomy Term for application with type '$string'");
    }
    return $term;
  }

}
