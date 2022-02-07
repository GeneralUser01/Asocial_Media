import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { mergeMap, Observable } from 'rxjs';

const AUTH_API = '../api/';

const httpOptions = {
  headers: new HttpHeaders({ 'Content-Type': 'application/json' })
};

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient) { }

  /** Get a new token from the server that uniquely identifies this session.
   * This token also protects against cross-site request forgery. */
  getCsrfToken() {
    return this.http.get('/sanctum/csrf-cookie');
  }

  login(username: string, password: string, remember: boolean): Observable<any> {
    return this.getCsrfToken()
      .pipe(mergeMap(() => {
        return this.http.post(AUTH_API + 'login', {
          email: username,
          password,
          remember,
        }, httpOptions);
      }))
  }

  logout(): Observable<any> {
    return this.http.post(AUTH_API + 'logout', {}, httpOptions);
  }

  register(username: string, email: string, password: string): Observable<any> {
    return this.getCsrfToken()
      .pipe(mergeMap(() => {
        return this.http.post(AUTH_API + 'register', {
          name: username,
          email,
          password,
          password_confirmation: password,
        }, httpOptions);
      }))
  }
}