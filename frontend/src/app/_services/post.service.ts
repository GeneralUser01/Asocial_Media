import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Timestamps, WithId } from '../_shared/db-types';


export interface PostContent {
  title: string,
  body: string,
}

export type Post = PostContent & WithId & Timestamps;

@Injectable({
  providedIn: 'root'
})
export class PostService {
  postUrl = '../api/posts/';
  httpOptions = {
  };

  constructor(private http: HttpClient) { }

  getPost(postId: number | string) {
    return this.http.get<Post>(this.postUrl + postId, this.httpOptions);
  }
  getPostImage(postId: number | string) {
    return this.http.get(this.postUrl + postId + '/image', this.httpOptions);
  }
  getPosts() {
    return this.http.get<Post[]>(this.postUrl, this.httpOptions);
  }

  createPost(title: string, body: string, image: Blob | null = null) {
    const formData = new FormData();
    formData.append('title', title);
    formData.append('body', body);
    if (image !== null) {
      formData.append('image', image);
    }
    return this.http.post<Post>(this.postUrl, formData, this.httpOptions);
  }
  updatePost(post: PostContent & WithId) {
    return this.http.put(this.postUrl, post, this.httpOptions);
  }
  deletePost(postId: number | string) {
    return this.http.delete(this.postUrl + postId, this.httpOptions);
  }
}
