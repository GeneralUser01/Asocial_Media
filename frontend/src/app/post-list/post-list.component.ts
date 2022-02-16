import { Component, NgModule, OnInit, ViewEncapsulation } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, EMPTY, mergeMap, of, tap, throwError } from 'rxjs';
import { Post, PostService } from '../_services/post.service';
import { RolesService } from '../_services/roles.service';
import { UserService } from '../_services/user.service';

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

  constructor(private postService: PostService, private userService: UserService, private roleService: RolesService) { }

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

//   updatePost() {
//     if (this.postId === null) return;
//     this.postService.getPost(this.postId)
//       // Use null in case of errors:
//       .pipe(
//         catchError((error) => of(null)),
//         mergeMap((result) => {
//           this.post = result;
//           this.isLoadingPost = false;

//           // Queue up another observable:
//           if (this.post) {
//             return this.userService.getUser(this.post.user_id).pipe(
//               tap(user => {
//                 if (!this.post) return;
//                 this.post.user = user;
//               }),
//               // Add roles info:
//               mergeMap(user => this.roleService.getRolesInfo(user)),
//               catchError(error => {
//                 if (this.post) {
//                   this.post.userLoadingError = error.error.message;
//                 }
//                 return throwError(() => error);
//               })
//             );
//           } else {
//             return EMPTY;
//           }
//         }),
//       )
//       .subscribe();
//   }
}
