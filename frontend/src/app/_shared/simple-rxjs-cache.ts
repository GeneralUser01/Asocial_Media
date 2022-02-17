// This cache was inspired by the example code provided by
// https://angular.io/guide/http
//
// More specifically the `request-cache.service.ts` file that is part of that
// example project. You can easily view it as part of their live example at
// https://stackblitz.com/run?file=src%2Fapp%2Frequest-cache.service.ts

import { OperatorFunction } from "rxjs";
import { CacheQueue } from "./cache-queue";
import { SimpleCache, SimpleCacheConfig } from "./simple-cache";

export class SimpleRxjsCache<K, V> extends SimpleCache<K, V>  {
  /** Cache keys where there are operations underway to determine their values. */
  private inProgress = new Map<K, CacheQueue<V>>();


  constructor(config: SimpleCacheConfig | null = null) {
    super(config);
  }

  override delete(key: K): void {
    super.delete(key);
    this.inProgress.get(key)?.invalidate();
  }
  override clear(): void {
    super.clear();
    // Invalidate all ongoing operations and don't cache their results:
    this.inProgress.forEach(queue => queue.invalidate());
  }

  override removedInternally(key: K): void {
    super.removedInternally(key);
    // The key hasn't been invalidated, just evicted from the cache. So we can
    // still cache the results from ongoing operations.
  }

  /** This handles all your caching needs. */
  rxjsOperator(key: K): OperatorFunction<V, V> {
    return CacheQueue.rxjsOperator({
      getCached: () => {
        return this.get(key);
      },
      getCurrentQueue: () => {
        return this.inProgress.get(key);
      },
      completedCurrentQueue: () => {
        this.inProgress.delete(key);
      },
      createCurrentQueue: (queue) => {
        this.inProgress.set(key, queue);
      },
      operationFinished: (value, invalidated) => {
        if (value === undefined || invalidated) return;
        this.put(key, value);
      },
    });
  }
}