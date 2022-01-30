import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';


export interface Post {
  id: number,
  title: string,
  body: string,
  created_at: string,
  updated_at: string,
}

@Injectable({
  providedIn: 'root'
})
export class PostService {
  postUrl = 'api/posts/';
  httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

  constructor(private http: HttpClient) { }

  getPost(postId: number | string) {
    return this.http.get<Post>(this.postUrl + postId, this.httpOptions);
  }
  getPosts() {
    return this.http.get<Post[]>(this.postUrl, this.httpOptions);
  }
}
