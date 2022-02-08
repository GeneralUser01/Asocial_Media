import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { CreatedAtTimestamp, Timestamps, UpdatedAtTimestamp, WithId, Wrapped, WrappedCollection } from '../_shared/db-types';
import { map } from 'rxjs';

export interface CurrentUserContent {
  name: string,
  email: string,
  email_verified_at: null | string,
  roles: number[],
}
export type UserContent =
  // Other users will definitively have these fields:
  Pick<CurrentUserContent, 'name'> &
  // And might have the rest depending on that user's preferences and the
  // current user's roles:
  Partial<CurrentUserContent>;

/** Info about the current user. */
export type CurrentUser = CurrentUserContent & WithId & Timestamps;
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

  constructor(private http: HttpClient) { }

  getCurrentUser() {
    return this.http.get<Wrapped<CurrentUser>>(this.currentUserUrl, this.httpOptions)
      .pipe(map(result => result.data));
  }

  getUser(userId: number | string) {
    return this.http.get<Wrapped<User>>(this.userUrl + userId, this.httpOptions)
      .pipe(map(result => result.data));
  }
  getUsers(page = 1) {
    return this.http.get<WrappedCollection<User[]>>(this.userUrl + '?page=' + page, this.httpOptions);
  }
}
