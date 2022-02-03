import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { mergeMap, Observable } from 'rxjs';


export interface Post {
  id: number,
  title: string,
  body: string,
  image: File,
  created_at: string,
  updated_at: string,
}

@Injectable({
  providedIn: 'root'
})
export class PostService {
  postUrl = '../api/posts/';
  httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

  constructor(private http: HttpClient) { }

  /** Get a new token from the server that uniquely identifies this session.
 * This token also protects against cross-site request forgery. */
    getCsrfToken() {
    return this.http.get('/sanctum/csrf-cookie');
  }

  getPost(postId: number | string) {
    return this.http.get<Post>(this.postUrl + postId, this.httpOptions);
  }
  getPosts() {
    return this.http.get<Post[]>(this.postUrl, this.httpOptions);
  }
  addPost(title: string, body: string, image?: File | null) {
    return this.getCsrfToken()
      .pipe(mergeMap(() => {
      return this.http.post<Post>(this.postUrl, {
          title,
          body,
          image,
        }, this.httpOptions);
      }))
    }
  }