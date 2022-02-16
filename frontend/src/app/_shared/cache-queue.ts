import { concat, defer, EMPTY, filter, finalize, first, map, mergeMap, Observable, of, OperatorFunction, pipe, Subject, takeWhile, tap } from "rxjs";


export interface CacheQueueConfig<T> {
  /** Try to get an existing queue. */
  getCurrentQueue: () => (CacheQueue<T> | undefined),
  /** Called when a no queue existed and we started an operation. */
  createCurrentQueue: (queue: CacheQueue<T>) => void,
  /** Called when there are no operations in the current queue. This means the
   * queue is finished and can't be reused again. */
  completedCurrentQueue: () => void,

  /** An operation finished. */
  operationFinished: (value: T | undefined, invalidated: boolean) => void,

  /** Try to get a value from cache. */
  getCached: () => (T | undefined),
}

interface OperationFinished<T> {
  generation: number,
  value: T | undefined,
}

/** A helper that prevents multiple operations from being preformed at the same
 * time. */
export class CacheQueue<T> {
  /** The number of ongoing operations for the current generation. */
  operations: number = 0;
  /** The number of ongoing operations for older generations. */
  olderOperations: number = 0;

  /** The current generation. This is incremented every time `invalidate` is
   * called. So values from operations that were started with a lower generation
   * than this should not be used for new operations. */
  generation: number = 0;

  /** Be notified when one operation finish. */
  event: Subject<OperationFinished<T>> = new Subject();
  /** All ongoing operations have finished and `event` has been completed. */
  finished: boolean = false;


  /** Invalidate all ongoing operations. Older operations can still use the
   * results from newer operations. */
  invalidate() {
    if (this.finished) return;

    if (this.generation === Number.MAX_SAFE_INTEGER) {
      this.generation = 0;
    } else {
      this.generation++;
    }
    this.olderOperations += this.operations;
    this.operations = 0;
  }

  get isFinished() {
    return this.finished;
  }

  /** This handles all your caching needs. */
  static rxjsOperator<T>(config: CacheQueueConfig<T>): OperatorFunction<T, T> {
    // For more info about creating rxjs "operators" see:
    // https://rxjs.dev/guide/operators#creating-new-operators-from-scratch
    return (observable: Observable<T>): Observable<T> => {
      /** A subscriber that emits a single event. */
      const SINGLE = of(null);

      // Track ongoing operations (these will send an event whenever an
      // operation finishes):
      const events = defer(() => {
        const inProgress = config.getCurrentQueue();
        if (!inProgress) return EMPTY;
        else return of(inProgress);
      }).pipe(
        // TODO: could keep calling getCurrentQueue until we don't get a
        // different queue anymore.

        // Wait for ongoing operations in the queue:
        mergeMap(queue => {
          // Events must be for this generation or newer:
          const gen = queue.generation;
          return queue.event.pipe(
            takeWhile(
              // Stop waiting for events when the operations count reach 0,
              // this allows us to start a new operation while the queue
              // isn't finished yet. Doing this ensures that if a previous
              // request fails then all waiting operations won't start at
              // the same time.
              () => queue.operations > 0 ||
                // If our operation was started in an older generation then
                // wait for older operations as well:
                (gen !== queue.generation && queue.olderOperations > 0),
              // Check cache even when operations count is 0:
              true
            ),
            // Ignore events from older invalidated operations:
            filter(event => event.generation >= gen || event.generation === queue.generation),
          );
        }),
      );

      // First check cache, then check cache after ongoing operations finish:
      const waitForCached = concat(SINGLE, events).pipe(
        mergeMap((event) => {
          // Received event so one operation completed
          // (errored/canceled/succeeded).

          // Check if anyone stored a value in the cache:
          const cached = config.getCached();
          if (cached !== undefined) {
            return of(cached);
          } else if (event && event.value !== undefined) {
            // Our operation might be part of an older generation that has
            // been invalidated, in that case using this older value should
            // still be fine:
            return of(event.value);
          } else {
            return EMPTY;
          }
        }),
      );

      // Just do the operation directly:
      const doItYourself = observable.pipe(CacheQueue.trackOperation(config));

      // Try to get value from cache, otherwise do the operation:
      return concat(waitForCached, doItYourself).pipe(first());
    };
  }

  /** An Rxjs operator that tracks the progress of an operation. This will not
   * pause or prevent the original operation. Instead it will only call
   * callbacks in the provided config at different points of the operation's
   * lifecycle. */
  static trackOperation<T>(config: CacheQueueConfig<T>): OperatorFunction<T, T> {
    // Use pipe to make custom rxjs operator if we need to combine multiple
    // operators:
    // https://rxjs.dev/guide/operators#use-the-pipe-function-to-make-new-operators

    // Use `defer` to create a new "pipe" for each subscriber since we have some
    // local state that we don't want to share between multiple observers.
    return (observable) => defer(() => {
      /** The queue that existed when this operation was started. */
      let registeredInfo: CacheQueue<T> | null = null;
      let operationGeneration = 0;
      const started = () => {
        if (registeredInfo) return;
        let info = config.getCurrentQueue();
        if (info && !info.finished) {
          info.operations++;
        } else {
          info = new CacheQueue<T>();
          info.operations = 1;
          config.createCurrentQueue(info);
        }

        operationGeneration = info.generation;
        registeredInfo = info;
      };
      const cleanup = (success: boolean, value: T | undefined) => {
        if (!registeredInfo) return;
        const info = registeredInfo;
        const generation = operationGeneration;
        registeredInfo = null;
        operationGeneration = 0;

        const isTooOld = info.generation !== generation;

        // Update cache:
        config.operationFinished(value, info.finished || isTooOld);


        // Modify info:
        if (isTooOld) {
          info.olderOperations--;
        } else {
          info.operations--;
        }

        // Notify waiting operations to check the cache (if they can't find
        // anything because we canceled or errored out then they will start a new
        // operation):
        info.event.next({ value, generation, });


        // If there are no more operations in progress then do some cleanup:
        const lastOp = (info.operations <= 0) && (info.olderOperations <= 0);
        const wasFinished = info.finished;
        if (lastOp) {
          info.event.complete();
          info.finished = true;

          if (!wasFinished) {
            config.completedCurrentQueue();
          }
        }
      }
      const finished = (value: T) => {
        cleanup(true, value);
      };
      const exited = () => {
        cleanup(false, undefined);
      };

      return observable.pipe(
        tap({
          // Started the operation that will get us the value eventually:
          subscribe: started,

          // We get to see the value:
          next: finished,

          // Exit conditions:
          unsubscribe: exited,
          error: exited,
          complete: exited,
          finalize: exited,
        }),
        // Make really sure we cleanup when the operation completes
        // (success/error/unsubscribe):
        finalize(exited),
      );
    });
  }
}