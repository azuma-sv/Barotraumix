<?php

/**
 * @file
 * Implements inerface for MBP Core service.
 */

namespace Drupal\bmp_core\BMP;

/**
 * Interface BMPCoreInterface.
 */
interface BMPCoreInterface {

  const BAROTRAUMA_APP_ID = 1026340;

  /**
   * Method to get current BuildId of specific app from Steam.
   *
   * @param int $appId
   *  ID of the application. Barotrauma App ID will be used as default value.
   *
   * @return null|string
   */
  public function steamGetBuildId(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID): null|string;

  /**
   * Method to update specific app from Steam.
   *
   * @param int $appId
   *  ID of the application. Barotrauma App ID will be used as default value.
   *
   * @return bool
   */
  public function steamAppUpdate(int $appId = BMPCoreInterface::BAROTRAUMA_APP_ID): bool;

  /**
   * Method which will return last executed command.
   *
   * @return null|string
   */
  public function getRawSteamCommand(): null|string;

  /**
   * Method which will return raw output from last executed command.
   *
   * @return null|string
   */
  public function getRawSteamCommandOutput(): null|string;
}
