import { Component, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { NgForm } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { catchError, EMPTY, map, mergeMap, of, tap, throwError } from 'rxjs';
import { PostComment, PostCommentService } from '../_services/post-comment.service';
import { Post, PostService } from '../_services/post.service';
import { RolesService, WithRolesInfo } from '../_services/roles.service';
import { CurrentUser, User, UserService } from '../_services/user.service';

interface WithUser {
  user?: User | null,
}
interface WithIsLoadingUserError {
  userLoadingError?: null | string,
}

@Component({
  selector: 'app-post',
  templateUrl: './post.component.html',
  styleUrls: ['./post.component.css'],
  encapsulation: ViewEncapsulation.Emulated,
})
export class PostComponent implements OnInit {

  /** Info about the currently logged in account. */
  currentUser: (CurrentUser & Partial<WithRolesInfo>) | null = null;


  /** Post id stored in the current page's URL. */
  postId: null | string = null;


  /** Info loaded about the post. */
  post: null | (Post & WithUser & Partial<WithRolesInfo> & WithIsLoadingUserError) = null;
  /** `true` if we are still loading `post`. */
  isLoadingPost = true;

  /** The URL where the post's image would be found. */
  imageUrl: null | any = null;


  /** Info about the comments made about the post. */
  postComments: (PostComment & WithUser & Partial<WithRolesInfo> & WithIsLoadingUserError)[] = [];
  /** `true` if we are still loading `postComments`. */
  isLoadingPostComments = true;


  /** Bound to the comment input textarea. */
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
    private roleService: RolesService,
    private userService: UserService,
  ) { }

  ngOnInit(): void {
    this.activatedRoute.paramMap.subscribe(params => {
      const id = params.get('postId');
      this.postId = id;
      if (!id) {
        this.post = null;
        this.isLoadingPost = false;
        this.isLoadingPostComments = false;
      } else {
        this.isLoadingPost = true;
        this.isLoadingPostComments = true;

        this.userService.getCurrentUser().subscribe(user => {
          this.currentUser = user;
          if (!user) return;
          this.roleService.getRolesInfo(user).subscribe();
        });

        this.postService.getPost(id)
          // Use null in case of errors:
          .pipe(
            catchError((error) => of(null)),
            mergeMap((result) => {
              this.post = result;
              this.isLoadingPost = false;

              // Queue up another observable:
              if (this.post) {
                return this.userService.getUser(this.post.user_id).pipe(
                  tap(user => {
                    if (!this.post) return;
                    this.post.user = user;
                  }),
                  // Add roles info:
                  mergeMap(user => this.roleService.getRolesInfo(user)),
                  catchError(error => {
                    if (this.post) {
                      this.post.userLoadingError = error.error.message;
                    }
                    return throwError(() => error);
                  })
                );
              } else {
                return EMPTY;
              }
            }),
          )
          .subscribe();

        this.imageUrl = this.postService.postUrl + id + '/image';

        this.updateComments();
      }
    })
  }

  updateComments() {
    if (this.postId === null) {
      this.isLoadingPostComments = false;
      return;
    }
    this.commentService.getComments(this.postId)
      // Use empty array in case of errors:
      .pipe(
        catchError((error) => of(null)),
        // Handle comments array:
        mergeMap((comments) => {
          this.postComments = comments?.data ?? [];
          this.isLoadingPostComments = false;

          // Do one operation per comment:
          return this.postComments;
        }),
        // Subscribe to one observable per comment:
        mergeMap(comment => {
          // The server could provide us with a user already:
          if (comment.user) return EMPTY;

          return this.userService.getUser(comment.user_id).pipe(
            tap((user) => {
              comment.user = user;
            }),
            // Add roles info:
            mergeMap(user => this.roleService.getRolesInfo(user)),
            // Ignore errors (to not cancel getting users for other comments):
            catchError((error) => {
              comment.userLoadingError = error.error.message;
              return EMPTY;
            }),
          );
        }),
      )
      .subscribe();
  }
  updateComment(commentId: number | string) {
    if (this.postId === null) return;
    this.commentService.getComment(this.postId, commentId).subscribe((comment) => {
      for (let i = 0; i < this.postComments.length; i++) {
        if (this.postComments[i].id === commentId) {
          // Copy properties from new object over the old one (this keeps loaded
          // user data for the comment writer):
          Object.assign(this.postComments[i], comment);
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
  deleteComment(comment: PostComment | null) {
    if (!comment) return;
    this.commentService.deleteComment(comment.post_id, comment.id).subscribe(() => this.updateComments())
  }

  deletePost(): void {
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