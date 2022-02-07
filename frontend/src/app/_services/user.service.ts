import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Timestamps, WithId } from '../_shared/db-types';

export interface UserContent {
  name: string,
  email: string,
  email_verified_at: null | string,
  roles: string[],
}

export type User = UserContent & WithId & Timestamps;

@Injectable({
  providedIn: 'root'
})
export class UserService {
  userUrl = '../api/user/';
  httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

  constructor(private http: HttpClient) { }

  getCurrentUser() {
    return this.http.get<User>(this.userUrl, this.httpOptions);
  }
}
