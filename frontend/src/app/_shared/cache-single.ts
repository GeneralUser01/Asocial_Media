

export class CacheSingle<T> {
  private value: T | undefined = undefined;
  private lastRead: number = 0;

  private _maxAge: number;

  constructor(maxAge: number | undefined = undefined) {
    this._maxAge = maxAge || 30000;
  }

  get maxAge() {
    return this._maxAge;
  }
  set maxAge(value) {
    this._maxAge = value;
  }

  /** Get the cached value, even if it is too old. */
  getOlder(): T | undefined {
    return this.value;
  }
  /** `true` if `get` would return a value. */
  hasValue(): boolean {
    return this.value !== undefined && !this.isExpired();
  }
  private isExpired() {
    return this.lastRead < (Date.now() - this._maxAge);
  }

  delete(): void {
    this.value = undefined;
    this.lastRead = 0;
  }
  get(): T | undefined {
    if (this.value === undefined) return undefined;

    const isExpired = this.isExpired();
    if (isExpired) return undefined;

    return this.value;
  }
  put(value: T): void {
    this.value = value;
    this.lastRead = Date.now();
  }
}