import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule } from '@angular/forms';
// import { MatPaginatorModule } from '@angular/material/paginator';
import { HttpClientModule } from '@angular/common/http';

import { AppRoutingModule } from './app-routing.module';
import { APP_BASE_HREF } from '@angular/common';
import { AppComponent } from './app.component';
import { PageNotFoundComponent } from './page-not-found/page-not-found.component';
import { LoginComponent } from './login/login.component';
import { UserDetailsComponent } from './user-details/user-details.component';
import { RegisterComponent } from './register/register.component';
import { ProfileComponent } from './profile/profile.component';
import { PostListComponent } from './post-list/post-list.component';
import { PostComponent } from './post/post.component';
import { PostCreationComponent } from './post-creation/post-creation.component';
import { RequireConfirmationValidatorDirective } from './_shared/require-confirmation.directive';

@NgModule({
  declarations: [
    AppComponent,
    PageNotFoundComponent,
    LoginComponent,
    UserDetailsComponent,
    RegisterComponent,
    ProfileComponent,
    PostListComponent,
    PostComponent,
    PostCreationComponent,
    RequireConfirmationValidatorDirective
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    // MatPaginatorModule,
    HttpClientModule
  ],
  providers: [
    // Specify the "root" path as understood by our routing (without this the
    // URL would always be prefixed with the value of `baseHref` inside the
    // `angular.json` file):
    { provide: APP_BASE_HREF, useValue: '/' }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
