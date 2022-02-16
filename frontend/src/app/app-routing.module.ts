import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { UserDetailsComponent } from './user-details/user-details.component';
import { PostListComponent } from './post-list/post-list.component';
import { PostComponent } from './post/post.component';
import { PostCreationComponent } from './post-creation/post-creation.component';
import { ProfileComponent } from './profile/profile.component';

const routes: Routes = [
  { path: '', component: PostListComponent, },
  { path: 'register', component: RegisterComponent, },
  { path: 'login', component: LoginComponent, },
  { path: 'user', component: ProfileComponent, },
  { path: 'posts/:postId', component: PostComponent, },
  { path: 'post-creation', component: PostCreationComponent, },
  { path: 'users/:userId', component: UserDetailsComponent, },
  // This should be last:
  { path: '**', component: PageNotFoundComponent, },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
