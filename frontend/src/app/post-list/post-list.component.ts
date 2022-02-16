import { Component, NgModule, OnInit, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, of } from 'rxjs';
import { Post, PostService } from '../_services/post.service';

// /**
//  * @title Paginator
//  */
// @Component({
//   selector: 'mat-paginator',
//   templateUrl: './post-list.component.html',
// })
// export class PaginatorOverview { }

@Component({
  selector: 'app-post-list',
  templateUrl: './post-list.component.html',
  styleUrls: ['./post-list.component.css']
})
export class PostListComponent implements OnInit {
  posts: Post[] = [];
  isLoadingPosts = true;

  constructor(private postService: PostService, private router: Router) { }

  ngOnInit(): void {
    this.postService.getPosts()
      .pipe(catchError((error) => of(null)))
      .subscribe(result => {
        this.isLoadingPosts = false;
        if (result) {
          this.posts = result.data;
        }
      });
  }
}
