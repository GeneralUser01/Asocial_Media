// This cache was inspired by the example code provided by
// https://angular.io/guide/http
//
// More specifically the `request-cache.service.ts` file that is part of that
// example project. You can easily view it as part of their live example at
// https://stackblitz.com/run?file=src%2Fapp%2Frequest-cache.service.ts

interface CacheEntry<T> {
  value: T;
  lastRead: number;
}
export interface SimpleCacheConfig {
  /** Max age of cached items in milliseconds. */
  maxAge?: null | number,
  /** Max number of entries to cache. */
  maxEntries?: null | number,
}

export class SimpleCache<K, V> {
  private cache = new Map<K, CacheEntry<V>>();

  /** Max age of cached items in milliseconds. */
  private _maxAge = 30000;

  /** Max number of entries to cache. */
  private _maxEntries = 200;

  constructor(config: SimpleCacheConfig | null = null) {
    if (!config) return;

    if (config.maxAge !== undefined && config.maxAge !== null) {
      this._maxAge = config.maxAge;
    }
    if (config.maxEntries !== undefined && config.maxEntries !== null) {
      this._maxEntries = config.maxEntries;
    }
  }

  /** Max age of cached items in milliseconds. */
  get maxAge(): number {
    return this._maxAge;
  }
  set maxAge(value: number) {
    this._maxAge = value;

    if (this.size === 0) return;

    this.cleanOnce();
  }

  /** Max number of entries to cache. */
  get maxEntries(): number {
    return this._maxEntries;
  }
  set maxEntries(value: number) {
    this._maxEntries = value;

    if (this.size === 0) return;

    if (this.hasTooMany()) {
      // Remove all entries that are too old:
      this.cleanOnce();
    }

    if (this.hasTooMany()) {
      // Remove other entries until we have less than the maximum size:
      this.cleanMany();
    }
  }

  /** The number of values that are stored in this cache. */
  get size(): number {
    return this.cache.size;
  }

  private hasTooMany() {
    return this.cache.size > this._maxEntries;
  }

  /** Preform cleanup of the cache. Will delete entries until we have less than
   * the maximum size. */
  private cleanMany() {
    // Take the number we have then remove the ones we get to keep, the
    // remaining number will be what should be removed.
    const toDelete = this.cache.size - this._maxEntries;

    if (toDelete <= 0) return;

    const entries = Array.from(this.cache.entries());
    // Sort so oldest entries are first:
    entries.sort((a, b) => {
      return a[1].lastRead - b[1].lastRead;
    })

    // Remove the first entries in the array until we have deleted enough
    // entries:
    for (let i = 0; i < toDelete; i++) {
      const entry = entries.shift();
      if (entry) {
        this.removedInternally(entry[0]);
      }
    }

    // Recreate the map without the removed entries:
    this.cache = new Map(entries);
  }
  /** Called when an item is removed because of internal cleanup. The entry
   * might still be present in the cache for a little while after this method
   * has been called. */
  protected removedInternally(key: K) { }

  /** Preform some cleanup. This will delete all entries that are too old.
   * Additionally it can delete the oldest remaining entry if there are too many
   * entries.
   */
  private cleanOnce() {
    const expired = Date.now() - this._maxAge;

    let trackOldest = this.hasTooMany();
    let oldest: null | CacheEntry<V> = null;
    let oldestKey: null | K = null;

    this.cache.forEach((entry, key) => {
      if (entry.lastRead < expired) {
        this.cache.delete(key);
        this.removedInternally(key);
      } else if (trackOldest) {
        if (!oldest || (entry.lastRead < oldest.lastRead)) {
          oldest = entry;
          oldestKey = key;
        }
      }
    });
    if (trackOldest && this.hasTooMany() && oldestKey !== null) {
      this.cache.delete(oldestKey);
      this.removedInternally(oldestKey);
    }
  }

  clear() {
    this.cache.clear();
  }
  delete(key: K): void {
    this.cache.delete(key);
  }
  get(key: K): V | undefined {
    const cached = this.cache.get(key);
    if (!cached) {
      return undefined;
    }

    const isExpired = cached.lastRead < (Date.now() - this._maxAge);
    return isExpired ? undefined : cached.value;
  }
  put(key: K, value: V): void {
    const newEntry: CacheEntry<V> = { value, lastRead: Date.now() };
    this.cache.set(key, newEntry);

    // remove expired cache entries
    this.cleanOnce();
  }
}