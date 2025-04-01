<?php

declare(strict_types=1);

namespace BrahmjotSingh0\Portals\Manager;

use BrahmjotSingh0\Portals\Portals;
use SOFe\AwaitGenerator\Await;
use Generator;

/**
 * Manages caching of portal data to reduce database queries.
 */
class CacheManager
{
    /** @var Portals The plugin instance. */
    private Portals $plugin;
    
    /** @var array<string, array> Cached portal data */
    private array $portalCache = [];
    
    /** @var int Last time the cache was refreshed */
    private int $lastCacheUpdate = 0;
    
    /** @var int Cache refresh interval in seconds */
    private const CACHE_REFRESH_INTERVAL = 60;
    
    /**
     * Initializes the CacheManager with the plugin instance.
     *
     * @param Portals $plugin The plugin instance.
     */
    public function __construct(Portals $plugin)
    {
        $this->plugin = $plugin;
        $this->refreshCache();
    }
    
    /**
     * Refreshes the portal cache from the database.
     * 
     * @param bool $force Whether to force refresh regardless of the time interval.
     * @return bool Whether the cache was refreshed.
     */
    public function refreshCache(bool $force = false): bool
    {
        $currentTime = time();
        
        // Only refresh if cache is empty, force is true, or the refresh interval has passed
        if ($force || empty($this->portalCache) || ($currentTime - $this->lastCacheUpdate) >= self::CACHE_REFRESH_INTERVAL) {
            Await::f2c(function (): Generator {
                $this->portalCache = yield from $this->plugin->getDatabaseManager()->fetchAllPortals();
                $this->lastCacheUpdate = time();
            });
            return true;
        }
        
        return false;
    }
    
    /**
     * Gets all cached portals.
     * 
     * @return array All portals from the cache.
     */
    public function getAllPortals(): array
    {
        return $this->portalCache;
    }
    
    /**
     * Gets portals for a specific world.
     * 
     * @param string $worldName The name of the world.
     * @return array Portals in the specified world.
     */
    public function getPortalsByWorld(string $worldName): array
    {
        return array_filter($this->portalCache, function($portal) use ($worldName) {
            return isset($portal['data']["worldName"]) && $portal['data']["worldName"] === $worldName;
        });
    }
    
    /**
     * Adds a portal to the cache.
     * 
     * @param array $portalData The portal data to add.
     */
    public function addPortal(array $portalData): void
    {
        $this->portalCache[] = $portalData;
    }
    
    /**
     * Gets the last cache update timestamp.
     * 
     * @return int The timestamp of the last cache update.
     */
    public function getLastUpdateTime(): int
    {
        return $this->lastCacheUpdate;
    }
    
    /**
     * Gets the cache refresh interval.
     * 
     * @return int The cache refresh interval in seconds.
     */
    public function getRefreshInterval(): int
    {
        return self::CACHE_REFRESH_INTERVAL;
    }
}