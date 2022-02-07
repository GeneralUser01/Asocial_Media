import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { catchError, of } from 'rxjs';
import { PostComment, PostCommentService } from '../_services/post-comment.service';
import { Post, PostService } from '../_services/post.service';

@Component({
  selector: 'app-post',
  templateUrl: './post.component.html',
  styleUrls: ['./post.component.css']
})
export class PostComponent implements OnInit {
  post: null | Post = null;
  comments: PostComment[] = [];
  isLoading = true;

  constructor(
    private postService: PostService,
    private commentService: PostCommentService,
    private activatedRoute: ActivatedRoute,
  ) { }

  ngOnInit(): void {
    this.activatedRoute.paramMap.subscribe(params => {
      const id = params.get('postId');
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
        this.commentService.getComments(id)
          // Use empty array in case of errors:
          .pipe(catchError((error) => of([])))
          .subscribe((comments) => this.comments = comments)
      }
    })
  }

}
