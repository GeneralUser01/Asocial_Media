import { Component, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { NgForm } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { catchError, of } from 'rxjs';
import { PostComment, PostCommentService } from '../_services/post-comment.service';
import { Post, PostService } from '../_services/post.service';

@Component({
  selector: 'app-post',
  templateUrl: './post.component.html',
  styleUrls: ['./post.component.css'],
  encapsulation: ViewEncapsulation.Emulated,
})
export class PostComponent implements OnInit {
  post: null | Post = null;
  postId: null | string = null;
  image: null | any = null;
  isLoading = true;

  postComments: PostComment[] = [];

  @ViewChild('content') textarea!: NgForm;

  createdPostComment: null | any = null;
  form: any = {
    content: null,
  };
  postCommentUploadIsSuccessful = false;
  isSubmitCommentFailed = false;
  postCommentErrorMessage = '';
  postCommentServerErrors = { content: null };

  postDeletionIsSuccessful = false;
  isPostDeletionFailed = false;
  postDeletionErrorMessage = '';

  constructor(
    private postService: PostService,
    private commentService: PostCommentService,
    private activatedRoute: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.activatedRoute.paramMap.subscribe(params => {
      const id = params.get('postId');
      this.postId = id;
      if (!id) {
        this.post = null;
        this.isLoading = false;
      } else {
        this.isLoading = true;

        this.postService.getPost(id)
          // Use null in case of errors:
          .pipe(catchError((error) => of(null)))
          .subscribe(result => {
            this.post = result;
            this.isLoading = false;
          });

        this.image = 'api/posts/' + id + '/image';

        this.updateComments();
      }
    })
  }

  updateComments() {
    if (this.postId === null) return;

    this.commentService.getComments(this.postId)
      // Use empty array in case of errors:
      .pipe(catchError((error) => of(null)))
      .subscribe((comments) => this.postComments = comments?.data ?? []);
  }

  updateComment(commentId: number | string) {
    if (this.postId === null) return;
    this.commentService.getComment(this.postId, commentId).subscribe((comment) => {
      for (let i = 0; i < this.postComments.length; i++) {
        if (this.postComments[i].id === commentId) {
          this.postComments[i] = comment;
        }
      }
    });
  }
  likeComment(comment: PostComment | null) {
    if (!comment) return;
    if (comment.opinion === 'liked') {
      this.commentService.unlikeComment(comment.post_id, comment.id).subscribe(() => this.updateComment(comment.id));
    } else {
      this.commentService.likeComment(comment.post_id, comment.id).subscribe(() => this.updateComment(comment.id));
    }
  }
  dislikeComment(comment: PostComment | null) {
    if (!comment) return;
    if (comment.opinion === 'disliked') {
      this.commentService.unlikeComment(comment.post_id, comment.id).subscribe(() => this.updateComment(comment.id));
    } else {
      this.commentService.dislikeComment(comment.post_id, comment.id).subscribe(() => this.updateComment(comment.id));
    }
  }

  onDelete(): void {
    // Forget previous errors:
    this.postDeletionErrorMessage = '';
    this.isPostDeletionFailed = false;
    if (this.postId === null) return;

    this.postService.deletePost(this.postId).subscribe({
      next: (data) => {
        console.log('Post deleted successfully: ', data);
        this.postDeletionIsSuccessful = true;
        this.isPostDeletionFailed = false;
      },
      error: (err) => {
        console.log('Post deletion failed: ', err);
        this.postDeletionErrorMessage = err.error.message;

        this.isPostDeletionFailed = true;
      }
    });
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.postCommentServerErrors = { content: null };
    this.postCommentErrorMessage = '';
    this.isSubmitCommentFailed = false;

    const { content } = this.form;
    if (!this.postId) {
      this.post = null;
    } else {
      this.commentService.createComment(this.postId, content).subscribe({
        next: (data) => {
          console.log('Post comment uploaded successfully: ', data);
          this.postCommentUploadIsSuccessful = true;
          this.isSubmitCommentFailed = false;

          // Clear comment textarea:
          this.textarea.reset();

          this.updateComments();
        },
        error: (err) => {
          console.log('Post comment upload failed: ', err);
          this.postCommentErrorMessage = err.error.message;
          const errors = err.error.errors;
          if (errors) {
            this.postCommentServerErrors.content = errors.content || null;
          }

          this.isSubmitCommentFailed = true;
        }
      });
    }
  }
}
