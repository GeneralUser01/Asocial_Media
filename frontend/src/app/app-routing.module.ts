import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { ShowThreadComponent } from './show-thread/show-thread.component';
import { ThreadListComponent } from './thread-list/thread-list.component';
import { UserDetailsComponent } from './user-details/user-details.component';

const routes: Routes = [
  { path: '', component: ThreadListComponent, },
  { path: 'register', component: RegisterComponent, },
  { path: 'login', component: LoginComponent, },
  { path: 'thread/:threadId', component: ShowThreadComponent, },
  { path: 'user/:username', component: UserDetailsComponent, },
  // This should be last:
  { path: '**', component: PageNotFoundComponent, },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
