import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { CreatedAtTimestamp, Timestamps, UpdatedAtTimestamp, WithId, Wrapped, WrappedCollection } from '../_shared/db-types';
import { catchError, map, Observable, of, throwError } from 'rxjs';
import { SimpleRxjsCache } from '../_shared/simple-rxjs-cache';
import { CacheRxjsSingle } from '../_shared/cache-rxjs-single';
import { AuthService } from './auth.service';
import { Role } from './roles.service';

export interface WithRoles {
  roles: Role[] | number[],
}

export interface CurrentUserContent {
  name: string,
  email: string,
  email_verified_at: null | string,
}
export type UserContent =
  // Other users will definitively have these fields:
  Pick<CurrentUserContent, 'name'> &
  // And might have the rest depending on that user's preferences and the
  // current user's roles:
  Partial<CurrentUserContent & WithRoles>;

/** Info about the current user. */
export type CurrentUser = CurrentUserContent & WithRoles & WithId & Timestamps;
/** Info about another user. For the current user we are guaranteed more info so
 * use the `CurrentUser` type. */
export type User = UserContent & WithId & CreatedAtTimestamp & Partial<UpdatedAtTimestamp>;

@Injectable({
  providedIn: 'root'
})
export class UserService {
  currentUserUrl = '../api/user/';
  userUrl = '../api/users/';
  httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

  /** Cache the current user. */
  currentUserCache = new CacheRxjsSingle<null | CurrentUser>(/* Keep for 1min: */ 60 * 1000);

  /** Users shouldn't really change much so should be safe to keep them cached
   * for a long time. */
  userCache = new SimpleRxjsCache<string, User>({ maxAge: /* 1h: */ 60 * 60 * 1000, maxEntries: 200 });

  constructor(private http: HttpClient, private authService: AuthService) {
    authService.loginStateChanged$.subscribe(() => {
      this.currentUserCache.delete();
      this.userCache.clear();
    });
  }

  /** Gets the current user if we are logged in or `null` if we aren't logged
   * in. */
  getCurrentUser() {
    return this.http.get<Wrapped<CurrentUser>>(this.currentUserUrl, this.httpOptions)
      .pipe(
        map(result => result.data),
        catchError((err: HttpErrorResponse) => {
          // Handle 401 unauthorized error (not logged in) since we expect that to
          // happen for normal usage. For more info see:
          // https://angular.io/guide/http#handling-request-errors
          if (err.status === 401) return of(null);
          // Don't catch other errors:
          else return throwError(() => err);
        }),
        this.currentUserCache.rxjsOperator({ emitOldValueWhileWaiting: true }),
      );
  }

  getUser(userId: number | string): Observable<User> {
    // Check in current user cache:
    const current = this.currentUserCache.get();
    if (current && userId == current.id) {
      return of(current);
    }

    return this.http.get<Wrapped<User>>(this.userUrl + userId, this.httpOptions)
      .pipe(
        map(result => result.data),
        this.userCache.rxjsOperator(String(userId)),
      );
  }
  getUsers(page = 1) {
    return this.http.get<WrappedCollection<User[]>>(this.userUrl + '?page=' + page, this.httpOptions);
  }
  deleteUser(userId: number | string) {
    // Invalidate caches:
    this.userCache.delete(String(userId));
    if (this.currentUserCache.get()?.id == userId) {
      this.currentUserCache.delete();
    }

    return this.http.delete<Wrapped<User>>(this.userUrl + userId, this.httpOptions);
  }
}
