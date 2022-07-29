<?php

namespace Drupal\bmp_core\Commands;

use Drupal\bmp_core\BMP\BMPCoreInterface;
use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\bmp_core\Commands
 *
 * @todo: Set proper name for class and file later.
 */
class BaroTraumaAppInfo extends DrushCommands {

  /**
   * @var BMPCoreInterface $bmpCore - BPMCore service.
   */
  public BMPCoreInterface $bmpCore;
  /**
   * Constructs an AliasManager.
   *
   * @param BMPCoreInterface $BMPCore
   *   The BMP Core service.
   */
  public function __construct(BMPCoreInterface $BMPCore) {
    parent::__construct();
    $this->bmpCore = $BMPCore;
  }

  /**
   * Drush command that displays buildid of steam app.
   *
   * @param int $appId
   *  ID of Steam application to check.
   *
   * @command bmp:app-buildid
   * @aliases bmp-app-buildid
   * @usage bmp:app-buildid
   */
  public function buildid(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID) {
    $buildId = $this->bmpCore->steamGetBuildId();
    $this->output()->writeln($buildId);
  }

}
