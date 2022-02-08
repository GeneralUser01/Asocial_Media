import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Timestamps, WithId } from '../_shared/db-types';


export interface PostCommentContent {
  content: string,
}

export interface PostCommentParentId {
  post_id: number,
}

export type PostComment = PostCommentContent & WithId & Timestamps;

@Injectable({
  providedIn: 'root'
})
export class PostCommentService {
  httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

  constructor(private http: HttpClient) { }

  private getUrl(postId: string | number) {
    return '../api/posts/' + postId + '/comments/';
  }

  getComment(postId: number | string, commentId: number | string) {
    return this.http.get<PostComment>(this.getUrl(postId) + commentId, this.httpOptions);
  }
  getComments(postId: number | string) {
    return this.http.get<PostComment[]>(this.getUrl(postId), this.httpOptions);
  }

  createComment(postId: number | string, content: string) {
    return this.http.post(this.getUrl(postId), { content }, this.httpOptions);
  }
  updateComment(post: PostCommentContent & PostCommentParentId & WithId) {
    return this.http.put(this.getUrl(post.post_id) + post.id, post, this.httpOptions);
  }
  deleteComment(postId: number | string, commentId: number | string) {
    return this.http.delete(this.getUrl(postId) + commentId, this.httpOptions);
  }
}
