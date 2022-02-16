import { concat, defer, EMPTY, of, OperatorFunction } from "rxjs";
import { CacheQueue } from "./cache-queue";
import { CacheSingle } from "./cache-single";


export interface CacheRxjsSingleConfig {
  /** If there is an expired value in cache, then emit it anyway while we are
   * waiting for a new value to be determined. Defaults to `false`.
   *
   * This will not emit values that have been explicitly deleted. */
  emitOldValueWhileWaiting?: boolean,
}

export class CacheRxjsSingle<T> extends CacheSingle<T> {
  private inProgress: CacheQueue<T> | undefined = undefined;

  constructor(maxAge: number | undefined = undefined) {
    super(maxAge);
  }

  override delete(): void {
    super.delete();
    this.inProgress?.invalidate();
  }

  /** This handles all your caching needs. */
  rxjsOperator(config: CacheRxjsSingleConfig | null = null): OperatorFunction<T, T> {
    const operator = CacheQueue.rxjsOperator({
      getCached: () => {
        return this.get();
      },
      getCurrentQueue: () => {
        return this.inProgress;
      },
      completedCurrentQueue: () => {
        this.inProgress = undefined;
      },
      createCurrentQueue: (queue) => {
        this.inProgress = queue;
      },
      operationFinished: (value, invalidated) => {
        if (value === undefined || invalidated) return;
        this.put(value);
      },
    });

    if (config?.emitOldValueWhileWaiting) {
      return (observable) => {
        const olderCached = defer(() => {
          const old = this.getOlder();
          if (!old || this.hasValue()) return EMPTY;
          return of(old);
        });

        const newer = observable.pipe(operator);

        return concat(olderCached, newer);
      };
    } else {
      return operator;
    }
  }
}