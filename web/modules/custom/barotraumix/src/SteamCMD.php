<?php

/**
 * @file
 * Source code of the service "barotraumix.steam".
 *
 * Helps to establish connections with SteamCMD and to perform downloads/updates.
 */

namespace Drupal\barotraumix;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class SteamCMD.
 */
class SteamCMD {

  /**
   * @var FileSystemInterface $fileSystem - File system service.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * @var EntityTypeManagerInterface $entityTypeManager - File system service.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var LoggerChannelFactoryInterface $logger - File system service.
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * @var LoggerChannelInterface $channel - File system service.
   */
  protected LoggerChannelInterface $channel;

  /**
   * @var null|string - Storage for last executed command.
   */
  protected null|string $lastCommand = NULL;

  /**
   * @var mixed - Storage for output of last executed command.
   */
  protected mixed $lastCommandOutput = NULL;

  /**
   * Inject dependencies.
   *
   * @param FileSystemInterface $fileSystem - File system service.
   * @param EntityTypeManagerInterface $entityTypeManager - File system service.
   * @param LoggerChannelFactoryInterface $logger - File system service.
   */
  public function __construct(FileSystemInterface $fileSystem, EntityTypeManagerInterface $entityTypeManager, LoggerChannelFactoryInterface $logger) {
    $this->fileSystem = $fileSystem;
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
    $this->channel = $this->logger->get(static::class);
  }

  /**
   * Method to get current Build ID of specific app from Steam.
   *
   * @param int $appId
   *  ID of the application. Barotrauma App ID will be used as default value.
   *
   * @return null|string
   */
  public function buildId(int $appId = Core::BAROTRAUMA_APP_ID):null|string {
    // Attempt to get build id.
    $command = "+app_info_update 1 +app_info_print '$appId'";
    $buildId = $this->parseBuildId($this->run($command));
    // Log errors.
    if (empty($buildId)) {
      $appName = ($appId == Core::BAROTRAUMA_APP_ID) ? 'Barotrauma' : "the app: $appId";
      $message = $this->prepareSteamLog("Failed to get Build ID of $appName");
      $this->channel->critical($message);
    }
    return $buildId;
  }

  /**
   * Method to install specific app.
   *
   * @param int|NULL $appId
   *  ID of the application. Barotrauma App ID will be used as default value.
   *
   * @param bool $overwrite
   *  Indicates that we allow SteamCMD to overwrite existing files. FALSE by default.
   *
   * @return int|bool|NULL
   *  In case of integer - new build id has been provided.
   *  FALSE - Application is already installed.
   *  NULL - Error occurred.
   */
  public function appInstall(int $appId = NULL, bool $overwrite = FALSE):int|bool|NULL {
    // Default value for appId.
    $appId = $appId ?? Core::BAROTRAUMA_APP_ID;

    // Get app build id from Steam.
    // @todo: Uncomment.
//    $buildId = $this->buildId($appId);
    // @todo: Remove.
    $buildId = 9118874;

    // String which will help to log errors.
    $appName = ($appId == Core::BAROTRAUMA_APP_ID) ? 'Barotrauma' : "the app: $appId";

    // Log errors if build id is not available.
    if (empty($buildId)) {
      $message = "Failed to install $appName, can't get Build ID from Steam.";
      $this->channel->critical($message);
      return NULL;
    }
    // Append build id for logs.
    $appName .= " (build id: $buildId)";

    // Check if we already have this app with this build id.
    if (!$overwrite && $this->isAvailableOnTheWebsite($appId, $buildId)) {
      $message = "Failed to install $appName, because it's already present.";
      $this->channel->info($message);
      return FALSE;
    }

    // Prepare directory.
    if (!$this->prepareDirectory($appId, $buildId)) {
      $message = "Failed to install $appName, because of wrong file permissions.";
      $this->channel->alert($message);
      return NULL;
    }

    // In case if we don't want to overwrite existing app files.
    if (!$overwrite) {
      // Scan directory.
      $scan = $this->fileSystem->scanDirectory($this->prepareUri($appId, $buildId), '/.*/', ['recurse' => FALSE]);
      if (!empty($scan)) {
        $message = "Failed to install $appName, because it's folder is occupied.";
        $this->channel->critical($message);
        return NULL;
      }
    }

    // Finally run SteamCMD.
    // @todo: Uncomment.
//    $status = $this->runUpdate($appId, $buildId);
    // @todo: Remove.
    $status = TRUE;
    if ($status) {
      return $buildId;
    }
    return NULL;
  }

  /**
   * Last executed command
   *
   * @return null|string
   */
  public function lastCommand(): null|string {
    return $this->lastCommand;
  }

  /**
   * Last executed command output.
   *
   * @return mixed
   */
  public function lastCommandOutput(): mixed {
    return $this->lastCommandOutput;
  }

  /**
   * Method to run SteamCMD commands.
   *
   * @param string $command
   *  Command to run. Can't work with double quote sign.
   *
   * @param bool $login
   *  Will log in as anonymous user.
   *
   * @param string $preLoginCommand
   *  Set of commands which need to be executed before logging in.
   *
   * @return mixed
   */
  protected function run(string $command, bool $login = TRUE, string $preLoginCommand = ''):mixed {
    // Prepare command, it's prefix and suffix.
    $prefix = 'steamcmd +@ShutdownOnFailedCommand 1 +@NoPromptForPassword 1 ';
    $login = $login ? ' +login anonymous ' : '';
    $suffix = ' +quit';
    $command = $prefix . $preLoginCommand . $login . $command . $suffix;

    // Check for cache.
    $cached = $this->cacheGet($command);
    if (isset($cached)) {
      return $cached;
    }

    // Run command and save data about that for debugging purpose.
    $this->lastCommand = $command;
    $output = shell_exec($command);
    $this->lastCommandOutput = $output;
    $message = $this->prepareSteamLog('SteamCMD has been executed.');
    $this->channel->info($message);

    // Use cache.
    $this->cacheSet($command, $this->lastCommandOutput);
    return $this->cacheGet($command);
  }

  /**
   * Direct method to update application.
   *
   * @param int $appId - ID of the app in Steam.
   * @param int $buildId - Build id of the app in Steam.
   *
   * @return bool
   *  Update status.
   */
  protected function runUpdate(int $appId, int $buildId):bool {
    // Get the real path to the folder and run the command.
    $path = $this->fileSystem->realpath($this->prepareUri($appId, $buildId));
    $command = "+app_update $appId validate";
    $preLogin = "+force_install_dir $path";
    $output = $this->run($command, TRUE, $preLogin);

    // Parse output with regular expression.
    $regexp = "/Success! App '$appId' fully installed./";
    preg_match($regexp, $output, $matches, PREG_OFFSET_CAPTURE);
    $status = !empty($matches[0][0]);

    // Log error.
    if (!$status) {
      $message = $this->prepareSteamLog('SteamCMD has executed an update, but we haven\'t received "success" message.');
      $this->channel->alert($message);
    }

    // Return status.
    return $status;
  }

  /**
   * Static cache storage.
   *
   * This function will help us to avoid cases when two similar requests were made during single PHP run.
   *
   * @return array
   *  Array with cached data.
   */
  protected function &staticCache():array {
    // Define static cache.
    static $cache = [];
    return $cache;
  }

  /**
   * Set cache for specific command.
   *
   * @param string $command
   *  Exact cached command.
   * @param mixed $value
   *  Will set into cache if value is provisioned. Also, it might return cached data otherwise.
   */
  protected function cacheSet(string $command, mixed $value):void {
    $cache = &$this->staticCache();
    // We can cache NULL.
    $value = !isset($value) ? FALSE : $value;
    $cache[$command] = $value;
  }

  /**
   * Get cache for specific command.
   *
   * @param string $command
   *  Exact cached command.
   *
   * @return mixed
   *   Cached data.
   */
  protected function cacheGet(string $command):mixed {
    $cache = &$this->staticCache();
    return $cache[$command] ?? NULL;
  }

  /**
   * Method to prepare debugging information for log.
   *
   * @param string $message - Main message for reviewer.
   *
   * @return string - Prepared message.
   */
  protected function prepareSteamLog(string $message):string {
    $lastCommand = "Last command: $this->lastCommand ";
    $lastCommandOutput = "Output: \r\n$this->lastCommandOutput";
    return "$message \r\n$lastCommand\r\n$lastCommandOutput";
  }

  /**
   * This method will parse response from SteamCMD to provide Build ID of the app.
   *
   * @param mixed $response
   *  Response which came to us form SteamCMD.
   *
   * @return null|int
   *   Numeric build id or nothing.
   */
  protected function parseBuildId(mixed $response):null|int {
    // Reject unknown responses.
    if (!is_string($response)) {
      return NULL;
    }
    // Parse with regular expression.
    $regexp = '/"branches"\s*{\s*"public"\s*{\s*"buildid"\s*"(?<buildid>\d*)"/ms';
    preg_match_all($regexp, $response, $matches, PREG_SET_ORDER);
    // Return numeric build id value or NULL.
    $data = isset($matches[0]['buildid']) ? intval($matches[0]['buildid']) : NULL;
    return empty($data) ? NULL : $data;
  }

  /**
   * Method to check if application with this build id is already available on the website.
   *
   * @param int $appId - ID of the app in Steam.
   * @param int $buildId - Build id of the app in Steam.
   *
   * @return bool
   */
  protected function isAvailableOnTheWebsite(int $appId, int $buildId):bool {
    // @todo: Check below needs testing. Application update might work not in the way I expect.
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', 'application');
    $query->condition('field_app_id', $appId);
    $query->condition('field_build_id', $buildId);
    $query->count();
    return !empty($query->execute());
  }

  /**
   * Prepare URI for application folder.
   *
   * @param int $appId - ID of the app in Steam.
   * @param int $buildId - Build id of the app in Steam.
   *
   * @return string
   */
  protected function prepareUri(int $appId, int $buildId):string {
    return 'baro://' . $appId . '/' . $buildId;
  }

  /**
   * Method to prepare directory by URI.
   *
   * @param int $appId - ID of the app in Steam.
   * @param int $buildId - Build id of the app in Steam.
   * @param int $options
   *   A bitmask to indicate if the directory should be created if it does
   *   not exist (FileSystemInterface::CREATE_DIRECTORY) or made writable if it
   *   is read-only (FileSystemInterface::MODIFY_PERMISSIONS).
   *
   * @return bool
   */
  protected function prepareDirectory(int $appId, int $buildId, int $options = FileSystemInterface::CREATE_DIRECTORY):bool {
    $uri = $this->prepareUri($appId, $buildId);
    return $this->fileSystem->prepareDirectory($uri, $options);
  }

}
