import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map } from 'rxjs';
import { Likeable, Timestamps, WithId, WithUserId, Wrapped, WrappedCollection } from '../_shared/db-types';


export interface PostContent {
  title: string,
  body: string,
}

export type Post = PostContent & WithUserId & WithId & Timestamps & Likeable;

@Injectable({
  providedIn: 'root'
})
export class PostService {
  postUrl = '../api/posts/';
  httpOptions = {
  };

  constructor(private http: HttpClient) { }

  getPost(postId: number | string) {
    return this.http.get<Wrapped<Post>>(this.postUrl + postId, this.httpOptions)
      .pipe(map(result => result.data));
  }
  getPostImage(postId: number | string) {
    return this.http.get(this.postUrl + postId + '/image', this.httpOptions);
  }
  getPosts(page = 1) {
    return this.http.get<WrappedCollection<Post[]>>(this.postUrl + '?page=' + page, this.httpOptions);
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

  /** Like a post. */
  likePost(postId: number | string) {
    return this.http.post(this.postUrl + postId + '/like', {}, this.httpOptions);
  }
  /** Dislike a post. */
  dislikePost(postId: number | string) {
    return this.http.post(this.postUrl + postId + '/dislike', {}, this.httpOptions);
  }
  /** Remove any like or dislike that the user has made for the specified post. */
  unlikePost(postId: number | string) {
    return this.http.delete(this.postUrl + postId + '/unlike', this.httpOptions);
  }
}
