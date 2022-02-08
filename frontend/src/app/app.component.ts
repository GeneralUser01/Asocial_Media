import { Component } from '@angular/core';
import { AuthService } from './_services/auth.service';
import { CurrentUser, UserService } from './_services/user.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'asocial-media';

  private roles: string[] = [];
  user: CurrentUser | null = null;
  isLoggedIn = false;
  showAdminBoard = false;
  showModeratorBoard = false;
  username?: string;

  constructor(private userService: UserService, private authService: AuthService) { }

  ngOnInit(): void {
    this.userService.getCurrentUser().subscribe(user => {
      this.user = user;
      this.userChanged();
    });
  }

  userChanged() {

    this.isLoggedIn = !!this.user;
/*
    if (this.isLoggedIn) {
      this.roles = this.user?.roles;

      this.showAdminBoard = this.roles.includes('ROLE_ADMIN');
      this.showModeratorBoard = this.roles.includes('ROLE_MODERATOR');

      this.username = this.user?.username;
    }*/
  }

  logout(): void {
    const done = () => {
      window.location.reload();
    };
    this.authService.logout().subscribe({
      next: done,
      error: (error) => {
        console.error('Logout failed: ', error);
        done();
      },
    });
  }
}
