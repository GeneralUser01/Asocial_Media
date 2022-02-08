import { Component, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { NgForm } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { catchError, of } from 'rxjs';
import { PostComment, PostCommentService } from '../_services/post-comment.service';
import { Post, PostService } from '../_services/post.service';

@Component({
  selector: 'app-post',
  templateUrl: './post.component.html',
  styleUrls: ['./post.component.css', '../app.component.css'],
  encapsulation: ViewEncapsulation.None
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
  isSuccessful = false;
  isSubmitCommentFailed = false;
  errorMessage = '';
  serverErrors = { content: null };

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
      .pipe(catchError((error) => of([])))
      .subscribe((comments) => this.postComments = comments);
  }

  onSubmit(): void {
    // Forget previous server errors:
    this.serverErrors = { content: null };
    this.errorMessage = '';
    this.isSubmitCommentFailed = false;

    const { content } = this.form;
    if (!this.postId) {
      this.post = null;
    } else {
      this.commentService.createComment(this.postId, content).subscribe({
        next: (data) => {
          console.log('Post comment uploaded successfully: ', data);
          this.isSuccessful = true;
          this.isSubmitCommentFailed = false;

          // Clear comment textarea:
          this.textarea.reset();

          this.updateComments();
        },
        error: (err) => {
          console.log('Post comment upload failed: ', err);
          this.errorMessage = err.error.message;
          const errors = err.error.errors;
          if (errors) {
            this.serverErrors.content = errors.content || null;
          }

          this.isSubmitCommentFailed = true;
        }
      });
    }
  }
}
