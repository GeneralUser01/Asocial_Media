import { Component, NgModule, OnInit, ViewEncapsulation } from '@angular/core';
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

  constructor(private postService: PostService) { }

  ngOnInit(): void {
    this.postService.getPosts().subscribe(result => this.posts = result.data);
  }
}
