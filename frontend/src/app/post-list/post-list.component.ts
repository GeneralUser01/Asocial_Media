import { Component, OnInit } from '@angular/core';
import { Post, PostService } from '../_services/post.service';

@Component({
  selector: 'app-post-list',
  templateUrl: './post-list.component.html',
  styleUrls: ['./post-list.component.css']
})
export class PostListComponent implements OnInit {
  posts: Post[] = [];

  constructor(private postService: PostService) { }

  ngOnInit(): void {
    this.postService.getPosts().subscribe(result => this.posts = result);
  }

  postThread(): void {
    console.log('test');
  }

}
