import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { from, Observable } from 'rxjs';

const AUTH_API = '/api/auth/';

const httpOptions = {
  headers: new HttpHeaders({ 'Content-Type': 'application/json' })
};

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient) { }

  login(username: string, password: string): Observable<any> {
   // return from(["not done yet"]);
    return this.http.post(AUTH_API + 'signin', {
      username,
      password
    }, httpOptions);
  }

  register(username: string, email: string, password: string): Observable<any> {
    //return from(["not done yet"]);
    return this.http.post(AUTH_API + 'signup', {
      username,
      email,
      password
    }, httpOptions);
  }
}