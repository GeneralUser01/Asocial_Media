import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map } from 'rxjs';
import { Likeable, Timestamps, WithId, WithUserId, Wrapped, WrappedCollection } from '../_shared/db-types';


export interface PostCommentContent {
  content: string,
}

export interface WithPostId {
  post_id: number,
}

export type PostComment = PostCommentContent & WithPostId & WithUserId & WithId & Timestamps & Likeable;

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
    return this.http.get<Wrapped<PostComment>>(this.getUrl(postId) + commentId, this.httpOptions)
      .pipe(map(result => result.data));
  }
  getComments(postId: number | string, page = 1) {
    return this.http.get<WrappedCollection<PostComment[]>>(this.getUrl(postId) + '?page=' + page, this.httpOptions);
  }

  createComment(postId: number | string, content: string) {
    return this.http.post(this.getUrl(postId), { content }, this.httpOptions);
  }
  updateComment(post: PostCommentContent & WithPostId & WithId) {
    return this.http.put(this.getUrl(post.post_id) + post.id, post, this.httpOptions);
  }
  deleteComment(postId: number | string, commentId: number | string) {
    return this.http.delete(this.getUrl(postId) + commentId, this.httpOptions);
  }

  /** Like a comment. */
  likeComment(postId: number | string, commentId: number | string) {
    return this.http.post(this.getUrl(postId) + commentId + '/like', {}, this.httpOptions);
  }
  /** Dislike a comment. */
  dislikeComment(postId: number | string, commentId: number | string) {
    return this.http.post(this.getUrl(postId) + commentId + '/dislike', {}, this.httpOptions);
  }
  /** Remove any like or dislike that the user has made for the specified comment. */
  unlikeComment(postId: number | string, commentId: number | string) {
    return this.http.delete(this.getUrl(postId) + commentId + '/unlike', this.httpOptions);
  }
}
