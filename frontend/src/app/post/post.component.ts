import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Post, PostService } from '../_services/post.service';

@Component({
  selector: 'app-post',
  templateUrl: './post.component.html',
  styleUrls: ['./post.component.css']
})
export class PostComponent implements OnInit {
  post: null | Post = null;
  isLoading = true;
  // isSuccessful = false;
  // isSubmitCommentFailed = false;
  // errorMessage = '';
  // serverErrors = { content: null };

  constructor(private postService: PostService, private activatedRoute: ActivatedRoute) { }

  ngOnInit(): void {
    this.activatedRoute.paramMap.subscribe(params => {
      const id = params.get('postId');
      if (!id) {
        this.post = null;
        this.isLoading = false;
      } else {
        this.isLoading = true;
        this.postService.getPost(id).subscribe(result => {
          this.post = result;
          this.isLoading = false;
        });
      }
    })
  }
}